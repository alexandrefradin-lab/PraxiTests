# Mapping WordPress → PraxiQuest

Référence de conversion des concepts WordPress vers leur équivalent PraxiQuest.

## Manifest

| WordPress (header de plugin PHP) | PraxiQuest (`plugin.json`) |
|----------------------------------|-----------------------------|
| `Plugin Name`                    | `name` |
| `Version`                        | `version` |
| `Author`                         | `author` |
| `Description`                    | `description` |
| `Text Domain`                    | `slug` (slugifié) |
| `Requires PHP`                   | `requires.php` |
| `Requires at least`              | (ignoré) |

## Hooks

| WordPress | PraxiQuest |
|-----------|------------|
| `add_action('init', $cb)` | `PluginHooks::action('plugin.booted', $cb)` (ou hook PraxiQuest pertinent) |
| `add_filter('the_content', $cb)` | `PluginHooks::filter('xxx.output', $cb)` |
| `do_action('mon_event', ...)` | `PluginHooks::doAction('plugin_slug.mon_event', ...)` |
| `apply_filters('mon_filter', $val)` | `PluginHooks::applyFilters('plugin_slug.mon_filter', $val)` |
| `register_activation_hook` | `onActivate()` dans `PluginServiceProvider` |
| `register_deactivation_hook` | `onDeactivate()` |
| `register_uninstall_hook` | `onUninstall()` |

## Stockage

| WordPress | PraxiQuest |
|-----------|------------|
| Custom post type (`register_post_type`) | Modèle Eloquent + migration dédiée |
| Custom taxonomy | Table relation many-to-many |
| `get_option('xxx')` / `update_option` | `config('plugins.{slug}.xxx')` ou table `plugin_xxx_settings` |
| Tables custom (`$wpdb->prefix . 'xxx'`) | Migrations `plugin_{slug}_*.php` |
| `wp_users` | `users` |
| `wp_usermeta` | `profiles.metadata` JSON ou table dédiée |
| ACF (Advanced Custom Fields) | Champs JSON dans modèles ou tables relation |

## Routes / endpoints

| WordPress | PraxiQuest |
|-----------|------------|
| Shortcode `[mon_test]` | Route Inertia + page Vue |
| `register_rest_route` | Route Laravel `routes/plugin.php` |
| Page admin (`add_menu_page`) | Page admin Inertia + entrée nav |
| `wp_ajax_xxx` | Route POST Laravel |

## Frontend

| WordPress | PraxiQuest |
|-----------|------------|
| Templates PHP | Composants Vue 3 (`<script setup>`) |
| jQuery | Vue reactivity (`ref`, `computed`) |
| `wp_enqueue_script` / `wp_enqueue_style` | Vite asset pipeline |
| WP REST API call | `Inertia.post()` ou `axios` direct |
| `wp_nonce_field` | CSRF token Laravel automatique |

## Auth

| WordPress | PraxiQuest |
|-----------|------------|
| `current_user_can('edit_posts')` | `auth()->user()->can('edit:tests')` (Spatie) |
| Capabilities | Permissions Spatie |
| Roles | Roles Spatie (`admin`, `professional`, `candidate`) |
| `wp_get_current_user()` | `auth()->user()` |

## Mail

| WordPress | PraxiQuest |
|-----------|------------|
| `wp_mail` | `Mail::to($user)->send(new XxxMail())` |
| Hook `wp_mail_from` | Config `MAIL_FROM_ADDRESS` |
| Plugin SMTP (WP Mail SMTP, etc.) | Config natif Laravel `.env` |

## Quoi garder / quoi jeter

### À **garder** systématiquement
- Logique métier du scoring (formules, dimensions, profils typés)
- Bibliothèque de questions (textes, options, ordre)
- Règles de validation des réponses
- Formats de restitution (PDF/email contenus)
- Logique d'invitation / tokens

### À **adapter**
- Styles : porter sur design system PraxiQuest (`pt-card`, `pt-btn-primary`, `pt-progress-fill`)
- Couleurs : remplacer par variables `--pt-primary`, `--pt-secondary`, ou gradient indigo→emerald
- Polices : utiliser Inter (par défaut)
- Boutons / inputs : classes Tailwind PraxiQuest

### À **jeter**
- Code spécifique WordPress (hooks WP, custom post types redondants)
- Templates PHP de rendu (réécrits en Vue)
- jQuery / scripts vanilla (réécrits en Vue)
- Code admin WordPress (réécrit en pages Inertia)
- Dépendances WP-only (ACF, etc.)

## Pattern type d'un test converti

```
plugins/test-mon-plugin/
├── plugin.json
├── PluginServiceProvider.php
├── README.md
├── src/
│   ├── Scoring/
│   │   └── MonScoringEngine.php
│   ├── Http/
│   │   └── Controllers/
│   ├── Listeners/
│   └── Mail/
├── resources/
│   ├── js/
│   │   └── Pages/
│   │       └── ...vue
│   └── views/
├── routes/
│   └── plugin.php
├── database/
│   ├── migrations/
│   └── seeders/
│       └── QuestionsSeeder.php
└── tests/
    └── ScoringTest.php
```
