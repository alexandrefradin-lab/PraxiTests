# Déploiement PraxiQuest sur OVH

Guide spécifique aux hébergements OVH (Mutualisé, Web Cloud, VPS, Dédié).

## Récap des plans OVH compatibles

| Plan OVH | SSH ? | Composer | Node | PHP 8.2 | Compatible PraxiQuest |
|----------|-------|----------|------|---------|----------------------|
| **Perso** | ✗ | ✗ | ✗ | ✓ | ⚠ Zip pré-buildé requis (build sur ta machine ou autre serveur) |
| **Pro** | ✓ (limité) | ✓ | ✓ | ✓ | ✓ Idéal |
| **Web Cloud** | ✓ (limité) | ✓ | ✓ | ✓ | ✓ Idéal |
| **Performance** | ✓ | ✓ | ✓ | ✓ | ✓ Idéal |
| **VPS** | ✓ (root) | install manuel | install manuel | install manuel | ✓ Total contrôle |
| **Dédié** | ✓ (root) | install manuel | install manuel | install manuel | ✓ Total contrôle |

> **Avant de commencer** : connecte-toi à ton **OVH Manager** (https://www.ovh.com/manager/) et identifie ton type d'hébergement.

---

## 1. Prérequis OVH (tous plans)

### 1.1 Activer PHP 8.2

Dans OVH Manager → ton hébergement → onglet **Multisite** → ton domaine → **Modifier** → choisir **PHP 8.2** (ou plus récent).

### 1.2 Créer une base MySQL

OVH Manager → onglet **Bases de données** → **Créer une base** :
- **Nom** : `praxiquest` (ou ce que tu veux)
- **Mot de passe** : génère un mot de passe fort
- **Utilisateur** : créé automatiquement avec le même nom que la base

> **Note** les identifiants : nom de base, utilisateur, mot de passe, **et l'hôte** (typiquement `praxiquestXXX.mysql.db` chez OVH, pas `localhost` !).

### 1.3 Créer un sous-domaine (recommandé)

OVH Manager → **Domaine** → ton domaine → **Sous-domaine** → **Ajouter** :
- **Sous-domaine** : `praxiquest` (ex : `praxiquest.tonsite.com`)
- **Cible** : laisse vide pour le moment (on configurera après upload)

### 1.4 Activer SSH (Pro / Web Cloud / VPS)

OVH Manager → **Hébergements** → **Multisite** → onglet **SSH** → **Activer** :
- Note tes identifiants SSH (host = `sshXX.cluster0XX.hosting.ovh.net`, user = `ton-login-ovh`, password = même que cPanel)

---

## 2. Méthode A — Plans **Pro / Web Cloud / Performance** (avec SSH)

C'est la méthode recommandée. Tu utilises le SSH d'OVH pour installer composer + faire le build directement sur le serveur.

### 2.1 Connexion SSH

Sur Windows, utilise **PuTTY** ou directement `ssh` depuis PowerShell :

```bash
ssh ton-login@sshXX.cluster0XX.hosting.ovh.net
```

Mot de passe : celui de ton compte OVH.

### 2.2 Naviguer dans le bon dossier

```bash
cd ~/www                    # OVH range les sites dans ~/www
ls
```

### 2.3 Cloner ou uploader PraxiQuest

#### Option 1 — Cloner depuis git (si tu as un repo)

```bash
git clone https://ton-repo.com/praxiquest.git praxiquest
cd praxiquest
```

#### Option 2 — Uploader le zip via FileZilla puis SSH

1. Upload `praxiquest-source.zip` via FileZilla dans `~/www/`
2. En SSH :
   ```bash
   cd ~/www
   unzip praxiquest-source.zip -d praxiquest
   cd praxiquest
   ```

### 2.4 Installer composer (en local user, pas root)

```bash
curl -sS https://getcomposer.org/installer | php
mv composer.phar composer
chmod +x composer
./composer --version
```

(Si OVH a déjà composer : tape `composer --version`. Si oui, saute l'étape.)

### 2.5 Installer les dépendances

```bash
./composer install --no-dev --optimize-autoloader
```

Ça peut prendre 5-10 min. Si OOM (`Killed`) : ajoute `--no-scripts` puis relance.

### 2.6 Builder les assets JS

#### Si Node est dispo sur le serveur

```bash
node -v
npm install
npm run build
```

#### Si pas de Node (rare sur Web Cloud)

Tu **builds en local sur ta machine** puis upload uniquement le dossier `public/build/` via FTP. Voir section 4.

### 2.7 Configurer le sous-domaine

OVH Manager → **Multisite** → **Modifier** ton sous-domaine `praxiquest.tonsite.com` :
- **Dossier racine** : `praxiquest/public` (très important — pas `praxiquest/` mais `praxiquest/public`)

Active SSL gratuit Let's Encrypt en cochant la case.

### 2.8 Lancer l'installeur web

Ouvre dans ton navigateur : `https://praxiquest.tonsite.com/install.php`

Suis l'assistant en 7 étapes. À l'étape **base de données**, utilise :
- **Hôte** : `praxiquestXXX.mysql.db` (pas `localhost` !)
- **Port** : `3306`
- **Base** : `praxiquest`
- **User** : `praxiquest` (ou ce que OVH a généré)
- **Password** : celui que tu as défini

L'installeur fait tout le reste automatiquement.

---

## 3. Méthode B — Plan **Perso** (sans SSH)

Tu n'as pas SSH, donc tu dois **builder le zip sur ta machine ou sur une autre machine** d'abord, puis uploader le zip pré-buildé.

### 3.1 Build sur ta machine (Windows)

> Si tu n'as ni PHP ni Node localement, saute à la section 3.2 (build cloud).

```cmd
cd C:\Users\Fradin Alexandre\Documents\Claude\Projects\PraxiQuest
make-release.bat
```

Tu obtiens `praxiquest-1.0.0-alpha.zip` (~50 Mo).

### 3.2 Build cloud (sans rien installer en local)

Si tu n'as ni PHP ni Node localement, deux options :

**a) GitHub Actions** (gratuit) : fork ce repo sur GitHub, ajoute un workflow `.github/workflows/release.yml`, télécharge le zip généré.

**b) Demande-moi** de te générer le zip — je le ferai dans un environnement avec composer + node (séparément).

### 3.3 Upload via FTP

1. Connecte FileZilla à `ftp.ton-domaine.com` avec tes credentials FTP OVH
2. Décompresse le zip sur ta machine
3. Drag & drop tout le contenu dans `~/www/praxiquest/` (créé sur le serveur)
4. ⚠ **Patience** : ~50 Mo de fichiers prend 15-30 min via FTP

### 3.4 Configurer sous-domaine + lancer install

Pareil qu'en 2.7 et 2.8.

---

## 4. Configuration avancée OVH

### 4.1 Cron jobs (queue worker, relances email)

OVH Manager → **Multisite** → onglet **Tâches Cron** → **Ajouter** :

```
*/5 * * * * cd /home/ton-login/www/praxiquest && /usr/bin/php artisan schedule:run >> /dev/null 2>&1
```

(Adapte le chemin PHP : OVH utilise parfois `/usr/local/php8.2/bin/php`.)

### 4.2 Worker queue dédié (recommandé pour IA)

Si tu utilises l'IA (synthèse + 15 métiers), un worker queue est nécessaire. Sur OVH mutualisé, c'est limité — utilise plutôt un **VPS Starter** (~5€/mois) si volume important.

Sur Pro/Web Cloud, ajoute aussi en cron :

```
*/1 * * * * cd /home/ton-login/www/praxiquest && /usr/bin/php artisan queue:work --max-time=55 --tries=3 >> /dev/null 2>&1
```

### 4.3 Permissions correctes

```bash
chmod -R 755 storage bootstrap/cache
```

(OVH gère généralement ça automatiquement, mais si l'installeur dit "non-writable", relance la commande.)

### 4.4 Activer SSL Let's Encrypt

OVH Manager → **Multisite** → **Modifier** sous-domaine → cocher **SSL gratuit**.

Attends ~30 min que le certificat soit émis. Puis ajoute dans `.env` :

```dotenv
APP_URL=https://praxiquest.tonsite.com
```

### 4.5 Désactiver l'installeur après installation

Une fois installé, **renomme** ou **supprime** `public/install.php` pour sécurité supplémentaire :

```bash
ssh ton-login@sshXX.cluster0XX.hosting.ovh.net
cd ~/www/praxiquest
mv public/install.php public/install.php.disabled
```

(L'installeur s'auto-désactive déjà via `storage/app/.installed`, mais c'est ceinture + bretelles.)

---

## 5. Troubleshooting OVH spécifique

### « 503 Service Unavailable » au premier accès

→ Permissions. SSH puis :
```bash
chmod -R 755 storage bootstrap/cache
chmod 644 .env
```

### « Class 'Pest\TestSuite' not found » ou similaire

→ Dépendances dev installées par erreur. Re-run :
```bash
./composer install --no-dev --optimize-autoloader
```

### Erreur SQL « Unknown database »

→ La base n'existe pas sur l'host configuré. Vérifie l'**hôte** (souvent `praxiquestXXX.mysql.db`, pas `localhost`).

### Sous-domaine retourne « Index of » au lieu de la landing

→ Document root mal configuré. Doit être `praxiquest/public`, **pas** `praxiquest/`.

### Installeur bloque sur étape 7 (install)

→ PHP timeout ou mémoire. Édite `.htaccess` à la racine du domaine :
```apache
php_value max_execution_time 300
php_value memory_limit 512M
```

### `npm run build` impossible (pas de Node)

→ Build en local + upload uniquement `public/build/` via FTP.

### Composer killed (OOM)

→ OVH limite RAM. Solutions :
```bash
./composer install --no-dev --optimize-autoloader --no-scripts
COMPOSER_MEMORY_LIMIT=-1 ./composer install --no-dev
```

Ou utilise un VPS pour build, copie ensuite `vendor/` via FTP.

### `php artisan` impossible

→ Vérifie chemin PHP : `which php` ou `which php8.2`. Souvent `/usr/local/php8.2/bin/php`.

---

## 6. Récap chemin SSH OVH typique

```
/home/<ton-login>/                    # home utilisateur OVH
├── www/                              # racine de tous tes sites
│   └── praxiquest/                   # ton install
│       ├── app/
│       ├── bootstrap/
│       ├── config/
│       ├── database/
│       ├── plugins/
│       ├── public/                   # ← document root du sous-domaine
│       │   ├── index.php
│       │   ├── install.php
│       │   └── build/
│       ├── resources/
│       ├── routes/
│       ├── storage/
│       ├── vendor/
│       ├── .env
│       ├── artisan
│       └── composer.json
└── logs/                             # logs Apache OVH
```

---

## 7. Estimations temps d'installation OVH

| Étape | Temps |
|-------|-------|
| Création base MySQL | 1 min |
| Création sous-domaine | 1 min |
| Activation SSH | 5 min (propagation OVH) |
| Upload source via FileZilla | 10-20 min |
| `composer install` SSH | 5-10 min |
| `npm run build` SSH | 2-3 min |
| Wizard install.php | 3-5 min |
| Activation SSL Let's Encrypt | 30 min (asynchrone) |
| **Total actif** | **~30-45 min** |

---

## 8. Et après ?

Une fois installé :

1. Connecte-toi : `https://praxiquest.tonsite.com/login`
2. Email : ce que tu as défini à l'étape 4 du wizard
3. Mot de passe : idem
4. Onboarding profil → tester un test → vérifier la restitution

Si tu veux activer l'IA : édite `.env` et ajoute `ANTHROPIC_API_KEY=sk-ant-...` puis redémarre la queue.

---

## 9. Support

Si tu bloques, les infos critiques à fournir :
- Plan OVH exact (Perso / Pro / VPS / etc.)
- Domaine ciblé
- Étape qui bloque
- Capture d'écran de l'erreur (URL incluse)
- `tail -50 storage/logs/laravel.log` (si SSH dispo)
