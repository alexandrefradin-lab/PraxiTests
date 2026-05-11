# PraxiTests

> SaaS propriétaire d'évaluation et orientation professionnelle, augmenté par IA, neuromarketing et gamification.

## Vision

PraxiTests aide professionnels (RH, cabinets, écoles, organismes d'orientation) **et** particuliers (candidats, demandeurs d'emploi, entrepreneurs en réflexion) à :

- administrer des tests en ligne riches et personnalisés
- collecter automatiquement profil + CV
- générer une **synthèse IA** + **15 métiers** pertinents
- engager grâce à la **gamification** (XP, badges, narration, progression)
- convertir grâce au **neuromarketing** (séquences emails optimisées)
- étendre librement la plateforme via un **système de plugins**

## Stack

| Couche | Technologie |
|--------|-------------|
| Backend | PHP 8.2+ · Laravel 11 |
| Frontend | Inertia.js · Vue 3 · Tailwind CSS |
| DB | MySQL 8 / PostgreSQL 15 |
| Cache · Queue · Sessions | Redis |
| IA | Drivers : Anthropic · OpenAI · Mistral · Ollama |
| Mail | SMTP / Mailgun / SES (configurable) |
| Storage | Local / S3 / Wasabi |

## Installation rapide (recommandée — installeur web)

1. Télécharge l'archive `praxitests-X.X.X.zip` ou clone le repo
2. Décompresse / déploie sur ton serveur web (PHP 8.2+ requis)
3. Pointe ton domaine vers `public/`
4. Ouvre `https://ton-domaine.com/install.php`
5. Suis l'assistant en 7 étapes (≈ 5 minutes)

L'installeur :
- vérifie les prérequis (PHP, extensions, permissions)
- teste la connexion à ta base de données
- crée ton compte administrateur
- configure la marque, l'URL, le SMTP, la clé de licence
- exécute les migrations
- s'auto-désactive à la fin (sécurité)

## Installation développeur

```bash
git clone <repo> praxitests
cd praxitests
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
npm run dev
php artisan serve
```

## Architecture

Voir [`ARCHITECTURE.md`](ARCHITECTURE.md) pour le détail :

- **Core** modulaire : tout ce qui peut bouger est en plugin
- **Plugin system** style WordPress (actions, filters, auto-discovery)
- **IA driver abstraction** (changer de modèle sans changer de code)
- **Multi-tenant flexible** (B2C, B2B, B2B2C, white-label)

## Plugins

```
plugins/mon-plugin/
├── plugin.json              # Manifest
├── PluginServiceProvider.php
├── src/
└── resources/
```

Voir [`docs/PLUGIN-DEVELOPER.md`](docs/PLUGIN-DEVELOPER.md) pour créer ton premier plugin.

Commandes :
```bash
php artisan praxitests:plugins:discover --sync
php artisan praxitests:plugins:activate <slug>
```

## Sécurité

- 2FA admin obligatoire
- CV chiffrés au rest (encryption disk)
- RGPD natif : consentement, anonymisation, droit à l'oubli, export
- Plugins sandboxés (permissions explicites)
- Audit log toutes actions sensibles

## Licence

Propriétaire — © Praxis Accompagnement. Tous droits réservés.
