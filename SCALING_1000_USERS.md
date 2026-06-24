# Plan montée en charge — 1 000 utilisateurs simultanés

> Audit réalisé le 2026-06-24 sur la base du code source PraxiQuest.
> Stack : Laravel 11 + Inertia/Vue 3 + MySQL · Hébergement actuel : OVH Pro partagé (cluster121)

---

## Diagnostic : les 4 goulots d'étranglement

### 🔴 Critique #1 — Queue synchrone (bloque les workers PHP)

```
QUEUE_CONNECTION=sync   ← jobs IA exécutés dans le process HTTP
SESSION_DRIVER=file     ← verrous fichiers sous concurrence
CACHE_STORE=file        ← ShouldBeUnique peu fiable
```

`GenerateAttemptInsights`, `GenerateGlobalGrimoire`, `ExtractCvDataJob` utilisent `->afterResponse()` sur une queue sync. Chaque appel LLM (30-240 s, max_tokens=4000) **monopolise un worker PHP-FPM**. OVH partagé en a ~20-30. Saturation à ~15 tentatives finalisées simultanées.

### 🔴 Critique #2 — Hébergement partagé OVH (plafond bas)

| Ressource | OVH Pro partagé | Besoin 1 000 users |
|---|---|---|
| Workers PHP-FPM | ~20-30 | 80-150 |
| Connexions MySQL | ~20-50 | 100+ |
| Redis | ❌ absent | Indispensable |
| Queue workers persistants | ❌ cron only | 8-10 workers |
| RAM disponible | ~256 MB PHP | 512 MB+ |

### 🟡 Modéré #3 — Oracle chat synchrone

`OracleChatService::ask()` est un appel LLM bloquant (~3-8 s). Throttle actuel : 20 req/min/user. À 1 000 users actifs, ça représente potentiellement 333 req/s sur l'Oracle — chacune bloquant un worker. **À basculer en streaming SSE ou limiter davantage.**

### 🟡 Modéré #4 — Index manquants probables

`oracle_messages` (historique par user) et `profile_grimoires` (polling IA) n'ont pas de confirmation d'index sur `(user_id, created_at)` — requêtes full-scan sous charge.

---

## Phase 0 — Corrections immédiates (`.env` seulement, OVH actuel)

> **Effort : 0 migration, 0 code.** Les 3 tables (`sessions`, `jobs`, `cache`, `cache_locks`) existent déjà dans les migrations.
> **Gain estimé : ×3-4 sur la capacité actuelle.**

### Changements `.env` production

```dotenv
# Sessions → base de données (fin des verrous fichiers)
SESSION_DRIVER=database

# Cache → base de données (ShouldBeUnique fiable)
CACHE_STORE=database

# Queue → base de données (jobs IA sortent du process HTTP)
QUEUE_CONNECTION=database
```

### Worker queue sur OVH (cron toutes les minutes)

Sur OVH partagé, pas de `supervisor`. Ajouter dans le cron OVH :

```
* * * * * cd /home/xxx/www && php artisan queue:work --queue=ai,default --stop-when-empty --tries=3 --timeout=250 >> /dev/null 2>&1
```

> ⚠️ Limitation OVH : le cron démarre 1 process/minute maximum. Les jobs AI s'accumulent si le volume est élevé. C'est suffisant en phase de démarrage, pas en production intensive.

### Opcache (php.ini OVH)

Activer via `.user.ini` à la racine :

```ini
opcache.enable=1
opcache.memory_consumption=128
opcache.max_accelerated_files=10000
opcache.validate_timestamps=0
opcache.revalidate_freq=0
```

### Cache applicatif Laravel

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

**À intégrer dans `deploy-server.sh`** (après chaque déploiement).

---

## Phase 1 — Migration VPS (indispensable pour 1 000 users)

> **Seuil de déclenchement : dès 200 users simultanés.**
> **Coût : 12-20 €/mois** (Hetzner CX31 4 vCPU / 8 GB RAM, ou OVH VPS Comfort).

### Infrastructure cible

```
[Nginx] → [PHP-FPM 8.2] → [MySQL 8] + [Redis 7]
                        ↓
               [Supervisor → Queue workers]
```

### Redis : 3 usages distincts

```dotenv
REDIS_HOST=127.0.0.1
REDIS_PORT=6379

SESSION_DRIVER=redis
CACHE_STORE=redis
QUEUE_CONNECTION=redis
```

Redis remplace la base pour sessions + cache → **latence <1 ms vs ~5-20 ms DB**.

### PHP-FPM : pool tuning (`/etc/php/8.2/fpm/pool.d/www.conf`)

```ini
pm = dynamic
pm.max_children = 60          ; adapté à 8 GB RAM (≈120 MB/worker)
pm.start_servers = 15
pm.min_spare_servers = 10
pm.max_spare_servers = 25
pm.max_requests = 500         ; recycle pour éviter les fuites mémoire
```

### Supervisor : queues séparées par priorité

```ini
[program:praxiquest-queue-default]
command=php /var/www/praxiquest/artisan queue:work redis --queue=default --sleep=1 --tries=3 --timeout=60
numprocs=5
autostart=true
autorestart=true

[program:praxiquest-queue-ai]
command=php /var/www/praxiquest/artisan queue:work redis --queue=ai --sleep=3 --tries=3 --timeout=250
numprocs=3                    ; max 3 appels LLM simultanés (rate limit API)
autostart=true
autorestart=true

[program:praxiquest-queue-mail]
command=php /var/www/praxiquest/artisan queue:work redis --queue=mail --sleep=2 --tries=3 --timeout=60
numprocs=2
autostart=true
autorestart=true
```

### Affecter les jobs aux bonnes queues

Dans chaque job, ajouter `$queue` :

```php
// GenerateAttemptInsights.php
public string $queue = 'ai';

// GenerateGlobalGrimoire.php
public string $queue = 'ai';

// ExtractCvDataJob.php
public string $queue = 'ai';

// SendCampaignJob.php / SendSequenceStepJob.php
public string $queue = 'mail';
```

### Nginx : gzip + cache assets

```nginx
server {
    gzip on;
    gzip_types text/css application/javascript application/json;

    # Assets Vite (hash dans le nom → cache long)
    location /build/ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }

    # Pages Inertia : pas de cache navigateur
    location / {
        add_header Cache-Control "no-store";
        try_files $uri $uri/ /index.php?$query_string;
    }
}
```

---

## Phase 2 — Optimisations code (après migration VPS)

### Oracle chat : limiter les appels bloquants

Réduire le throttle Oracle de 20 à **10 req/min/user** (actuel : 20). L'Oracle reconstruit l'intégralité du contexte candidat à chaque message — c'est coûteux.

```php
// routes/web.php
Route::post('/oracle/messages', ...)->middleware('throttle:10,1');
```

Option future : streaming SSE (`stream: true` sur le driver Anthropic) pour libérer le worker plus tôt et améliorer l'UX.

### Index à ajouter (migration)

```php
// oracle_messages : historique paginé par user
$table->index(['user_id', 'created_at']);

// profile_grimoires : polling status IA
$table->index(['user_id', 'updated_at']);

// mgmt_journeys (praxilead) : progression par user
$table->index(['user_id', 'day']);
```

### Cache des données stables

Les données qui ne changent pas souvent (liste des tests publiés, contenu des questions) peuvent être mises en cache Redis :

```php
// Exemple : liste des tests (change rarement)
$tests = Cache::remember('tests.published', 3600, fn() =>
    Test::published()->with('plugin')->get()
);
```

### Horizon (monitoring des queues)

```bash
composer require laravel/horizon
php artisan horizon:install
```

Horizon donne une UI pour voir les queues en temps réel, les jobs échoués, le débit — indispensable en production.

---

## Phase 3 — Au-delà de 1 000 users (>500 rps)

Ces points ne sont pas urgents mais à prévoir si le SaaS décolle fort :

| Besoin | Solution |
|---|---|
| DB saturée en lecture | Replica MySQL read-only → `DB::connection('read')` |
| Assets lents | CDN (Cloudflare gratuit) devant le VPS |
| Pics d'inscription (campagnes) | Queue `mail` → driver SES/Mailgun (pas SMTP direct) |
| Monitoring | Sentry (erreurs) + Telescope en staging |
| Multi-instance | Load balancer + sessions Redis partagé (déjà prévu Phase 1) |

---

## Résumé des actions par priorité

| Priorité | Action | Effort | Gain |
|---|---|---|---|
| 🔴 1 | `.env` : `SESSION_DRIVER=database`, `CACHE_STORE=database`, `QUEUE_CONNECTION=database` | 5 min | ×3 capacité |
| 🔴 2 | Activer `config:cache` / `route:cache` dans le déploiement | 10 min | -30% temps boot |
| 🔴 3 | Migrer sur VPS + Redis + Supervisor | 1 jour | ×10 capacité |
| 🔴 4 | Séparer queues `ai` / `default` / `mail` + `$queue` dans les jobs | 30 min | Stabilité AI sous charge |
| 🟡 5 | Throttle Oracle → 10/min, index `oracle_messages` | 1h | -50% contention DB |
| 🟡 6 | Laravel Horizon | 30 min | Visibilité queues |
| 🟢 7 | CDN Cloudflare | 15 min | Assets <50ms monde |

---

## Capacité estimée par configuration

| Config | Users simultanés supportés | Remarque |
|---|---|---|
| Actuel (OVH, tout en `file`/`sync`) | ~50-80 | Saturation workers PHP |
| Phase 0 (`.env` DB drivers) | ~150-200 | Queue async, sessions stables |
| Phase 1 (VPS + Redis + Supervisor) | **800-1 200** | Cible atteinte |
| Phase 1 + Phase 2 (optimisations) | **1 500-2 000** | Marge confortable |
