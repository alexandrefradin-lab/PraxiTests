# PraxiQuest — Quickstart local

Pour tester PraxiQuest en local en 5 minutes.

## 1. Prérequis

| Outil | Version min | Comment vérifier |
|-------|-------------|-------------------|
| PHP | 8.2+ | `php -v` |
| Composer | 2.x | `composer --version` |
| Node.js | 20+ | `node -v` |
| MySQL (ou MariaDB) | 8 / 10.6+ | `mysql --version` |

**Extensions PHP requises** : `pdo`, `pdo_mysql`, `mbstring`, `openssl`, `fileinfo`, `tokenizer`, `xml`, `ctype`, `curl`, `bcmath`, `json`.

### Sur Windows

- PHP : https://windows.php.net/download/ (choisir Thread Safe x64)
- Composer : https://getcomposer.org/Composer-Setup.exe
- Node : https://nodejs.org (version LTS)
- MySQL : XAMPP / Laragon (le plus simple) ou MySQL standalone

> **Astuce** : Laragon (https://laragon.org) installe PHP + MySQL + Apache + Composer + Node en un clic.

### Sur Mac

```bash
brew install php composer node mysql
brew services start mysql
```

### Sur Linux (Debian/Ubuntu)

```bash
sudo apt install php8.2-cli php8.2-mbstring php8.2-xml php8.2-mysql \
  php8.2-curl php8.2-zip php8.2-bcmath composer mysql-server nodejs npm
```

## 2. Préparer la base de données

Créer une base vide :

```sql
CREATE DATABASE praxiquest CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'praxiquest'@'localhost' IDENTIFIED BY 'praxiquest';
GRANT ALL ON praxiquest.* TO 'praxiquest'@'localhost';
FLUSH PRIVILEGES;
```

## 3. Installation automatique

### Windows

```cmd
cd C:\Users\Fradin Alexandre\Documents\Claude\Projects\PraxiQuest
setup.bat
```

Configure les variables DB dans `.env` (DB_DATABASE, DB_USERNAME, DB_PASSWORD) puis :

```cmd
setup.bat finish
```

### Mac / Linux

```bash
cd ~/Documents/Claude/Projects/PraxiQuest
chmod +x setup.sh dev.sh
./setup.sh deps     # composer + npm + .env
# édite .env (DB_DATABASE, DB_USERNAME, DB_PASSWORD)
./setup.sh finish   # migrate + seed + plugins + build
```

## 4. Installation manuelle (étape par étape)

```bash
composer install
npm install
cp .env.example .env       # ou copie sur Windows
php artisan key:generate

# Édite .env (DB_DATABASE, DB_USERNAME, DB_PASSWORD)

php artisan storage:link
php artisan migrate --force
php artisan db:seed --force

# Découvre + active les 5 plugins
php artisan praxiquest:plugins:discover --sync
php artisan praxiquest:plugins:activate praximet
php artisan praxiquest:plugins:activate praxivaleurs
php artisan praxiquest:plugins:activate praxicare
php artisan praxiquest:plugins:activate praxiemo
php artisan praxiquest:plugins:activate praximum

npm run build
```

## 5. Démarrer

### Mode dev (3 processus en parallèle)

**Windows** : `dev.bat`
**Mac/Linux** : `./dev.sh`

Ou manuellement, 3 terminaux :

```bash
php artisan serve              # Terminal 1 : http://127.0.0.1:8000
npm run dev                    # Terminal 2 : Vite hot-reload
php artisan queue:work         # Terminal 3 : queue (mails, IA)
```

### Mode prod (assets compilés)

```bash
npm run build
php artisan serve
```

## 6. Connexion

| Compte | Email | Mot de passe |
|--------|-------|--------------|
| Admin par défaut | `admin@praxiquest.local` | `changeme123` |

Personnalisable via `.env` (`PRAXIQUEST_ADMIN_EMAIL`, `PRAXIQUEST_ADMIN_PASSWORD`) **avant** de lancer le seeder.

## 7. Vérification rapide

Une fois démarré, vérifie :

- ✓ Landing : http://127.0.0.1:8000
- ✓ Login : http://127.0.0.1:8000/login
- ✓ Admin dashboard : http://127.0.0.1:8000/admin (après login admin)
- ✓ Tests dispo : http://127.0.0.1:8000/tests (après onboarding profil)

5 tests doivent apparaître :
- PraxiMet — RIASEC (84 questions)
- PraxiValeurs — Schwartz (40 questions)
- PraxiCare — Karasek + MBI (48 questions)
- PraxiEmo — Intelligence émotionnelle (86 questions)
- PraxiMum — Big Five (128 questions)

## 8. IA (optionnel)

Pour la synthèse + 15 métiers, configure une clé API dans `.env` :

```dotenv
AI_DEFAULT_DRIVER=anthropic       # ou openai / mistral / ollama
ANTHROPIC_API_KEY=sk-ant-xxxxx
ANTHROPIC_MODEL=claude-sonnet-4-6
```

Sans clé : les tests passent normalement, mais la synthèse IA reste vide.

## 9. Tests Pest

```bash
php artisan test                 # tous les tests
php artisan test --testsuite=Unit
php artisan test tests/Unit/Plugins/PraxiMet  # un seul plugin
```

## 10. Troubleshooting

### `Class "Praxis\Plugins\..." not found`

Régénère l'autoload :
```bash
composer dump-autoload
```

### `SQLSTATE[HY000] [1045] Access denied`

Vérifie credentials DB dans `.env`.

### `npm run dev` boucle / écran blanc

```bash
rm -rf node_modules public/build
npm install && npm run build
```

### Port 8000 occupé

```bash
php artisan serve --port=8001
```

### `Permission denied` sur storage/

Mac/Linux :
```bash
chmod -R 775 storage bootstrap/cache
```

### Plugins pas découverts

```bash
php artisan praxiquest:plugins:discover --sync
php artisan cache:clear
```

### Réinitialiser tout

```bash
php artisan migrate:fresh --seed
php artisan praxiquest:plugins:discover --sync
```

## 11. Désinstaller

```bash
DROP DATABASE praxiquest;
rm -rf vendor node_modules public/build .env storage/app/.installed
```

---

Besoin d'aide ? Vérifie [README.md](README.md), [INSTALL.md](INSTALL.md) ou [ARCHITECTURE.md](ARCHITECTURE.md).
