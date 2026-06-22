# Guide d'installation — PraxiQuest

## 1. Prérequis serveur

| Composant | Version minimale |
|-----------|------------------|
| PHP | 8.2 |
| Extensions PHP | pdo, mbstring, openssl, fileinfo, json, tokenizer, xml, ctype, curl |
| MySQL | 8.0 (ou MariaDB 10.6+) |
| ou PostgreSQL | 15 |
| Redis | 6+ (recommandé pour cache/queue/sessions) |
| Composer | 2.x (dev uniquement) |
| Node.js | 20+ (dev uniquement) |
| Espace disque | 500 Mo + stockage CV |
| RAM | 1 Go minimum, 2 Go recommandé |

## 2. Installation via assistant web (production)

### 2.1 Préparer le serveur

```bash
# Apache : vhost pointant vers /chemin/praxiquest/public
# Nginx : root /chemin/praxiquest/public; try_files $uri $uri/ /index.php?$query_string;
```

### 2.2 Permissions

```bash
chown -R www-data:www-data praxiquest
chmod -R 755 praxiquest
chmod -R 775 praxiquest/storage praxiquest/bootstrap/cache
```

### 2.3 Lancer l'assistant

Ouvre `https://ton-domaine.com/install.php` puis suis les 7 étapes :

1. **Welcome** — présentation
2. **Requirements** — vérification PHP, extensions, permissions
3. **Database** — host, port, user, mot de passe (test connexion)
4. **Admin** — nom, email, mot de passe du compte super-admin
5. **Branding** — nom plateforme, URL, couleur
6. **Mail** — config SMTP
7. **License** — clé de licence ou mode trial 30 jours

À la fin, l'assistant se verrouille automatiquement.

## 3. Installation pour développement

```bash
git clone https://github.com/praxis/praxiquest.git
cd praxiquest
composer install
npm install
cp .env.example .env
php artisan key:generate
# Édite .env (DB, mail, IA)
php artisan migrate --seed
php artisan storage:link
npm run dev
php artisan serve
# Dans un autre terminal :
php artisan queue:work
```

## 4. Configuration IA

Renseigne dans `.env` la clé du driver souhaité :

```dotenv
AI_DEFAULT_DRIVER=anthropic
ANTHROPIC_API_KEY=sk-ant-...
ANTHROPIC_MODEL=claude-sonnet-4-6
```

Drivers supportés : `anthropic`, `openai`, `mistral`, `ollama`. Tu peux aussi assigner un driver différent par tâche dans `config/ai.php` (synthèse, suggestions métiers, extraction CV).

## 5. Mise à jour

```bash
php artisan down
git pull
composer install --no-dev --optimize-autoloader
npm ci && npm run build
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan up
```

## 6. Réinstallation

```bash
rm storage/app/.installed
# Puis ouvre /install.php?force=1
```

## 7. Backup

```bash
# Base de données
mysqldump praxiquest > backup.sql

# Storage (CV, exports)
tar czf storage-backup.tar.gz storage/app
```

## 8. Sécurité production

- Forcer HTTPS au niveau du reverse proxy
- 2FA admin obligatoire (activable dans le profil)
- Désactiver `APP_DEBUG` en production
- Placer `storage/`, `bootstrap/cache/` hors public
- Exclure `.env` et `vendor/` des sauvegardes accessibles au web
- Chiffrer le disque storage si CV stockés en local
