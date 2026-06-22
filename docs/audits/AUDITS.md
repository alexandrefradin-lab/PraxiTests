# PraxiQuest — Audits fonctionnel, technique & sécurité

> Date : 2 juin 2026 · Commit audité : `09ec99e` (reconstruction post-corruption)
> Nature : audits **statiques** (revue de code). Aucune exécution PHP/build n'a été
> possible dans l'environnement de travail — une passe runtime reste nécessaire (voir §2).

---

## 1. Audit fonctionnel

L'application couvre le cahier des charges PraxiQuest. Chaque exigence a été tracée
du routeur jusqu'à la vue.

| Exigence | État | Implémentation |
|----------|------|----------------|
| Tests en ligne | ✅ | `TestEngine` + 5 plugins (RIASEC, Schwartz, Karasek/MBI, EQ-i, Big Five) |
| Système de plugins | ✅ | `PluginManager` + auto-discovery `plugin.json` + commandes `praxiquest:plugins:*` |
| Invitations par mailing | ✅ | `CampaignService`, `SequenceRunner`, `InvitationController` (lien public `/i/{token}`) |
| Synthèse IA + 15 métiers | ✅ | `ProfileSynthesisService`, `JobSuggestionService`, job `GenerateAttemptInsights` |
| Onboarding préalable (statut, ancienneté, CV) | ✅ | `OnboardingController` + `config/praxiquest.php` (`cv_required`, statuts) ; l'accès aux tentatives est bloqué tant que `profile->isComplete()` est faux |
| Gamification (XP, niveaux, badges, narration) | ✅ | `GamificationEngine`, `BadgeEvaluator`, `NarrativeEngine` |
| Neuromarketing | ✅ | `NeuromarketingOptimizer` (8 biais) |
| Restitution + PDF + historique | ✅ | `ResultController` (show / status / pdf / history) |
| Réinitialisation de mot de passe | ✅ | `AuthController` + pages `ForgotPassword` / `ResetPassword` |

**Parcours vérifiés (route → contrôleur → vue) :** inscription/connexion, onboarding,
liste des tests, passation (`AttemptPlay`), complétion, restitution (`ResultsShow` +
pages plugin), historique, back-office (dashboard, leads, campagnes, tests, plugins, réglages IA).

**Limites / points ouverts fonctionnels**
- La fonctionnalité « édition du profil après onboarding » a été retirée : elle n'était
  câblée ni côté contrôleur ni côté UI. À recréer proprement si souhaitée.
- Les pages de résultats des 5 plugins ont été restaurées dans leur version stable
  (bb7a4a1) : fonctionnelles, mais sans la refonte visuelle (graphiques radar Chart.js,
  affichage des percentiles `norm_scores`) qui était en cours et irrécupérable.
- Validation runtime non faite : aucun parcours n'a été exécuté réellement (voir §2).

---

## 2. Audit technique

**Stack :** PHP 8.2 / Laravel 11 · Inertia + Vue 3 + Vite · Tailwind · MySQL/PostgreSQL.
**Architecture :** « plugin-first », cœur sous namespace `Praxis\Core\*`, plugins sous
`Praxis\Plugins\*`. ~14 contrôleurs, 15 modèles, 13 migrations, 21 pages Vue, 54 tests.

### 2.1 🔴 Cause racine majeure — synchronisation du dossier de travail
Le dossier projet est dans `Documents\…` (vraisemblablement synchronisé OneDrive). Cette
synchro **tronque les écritures de fichiers** : c'est ce qui a corrompu 39 fichiers de la
session précédente (écritures coupées en plein milieu), puis recommencé pendant cette
session. L'index Git lui-même se corrompt à l'écriture sur ce montage.

**Impact :** risque permanent de corruption silencieuse à chaque sauvegarde/commit.
**Recommandation (prioritaire) :**
- Déplacer le dépôt hors du dossier synchronisé (ex. `C:\dev\PraxiQuest`), **ou** suspendre
  la synchro OneDrive sur ce dossier.
- Travailler systématiquement via Git (commits fréquents) plutôt que de laisser du travail
  non commité s'accumuler.

### 2.2 🟠 Validation runtime non effectuée
PHP/Composer/Node n'étant pas disponibles ici, ces étapes restent **à exécuter en local** :
```
composer install
php artisan key:generate
php -l <chaque .php>        # lint syntaxe
php artisan migrate --seed # schéma + données
npm install && npm run build
./vendor/bin/pest          # 54 tests
```
La cohérence structurelle a été vérifiée statiquement (équilibrage accolades/parenthèses,
détection de troncatures : **0 fichier tronqué**), mais cela ne remplace pas un `php -l`
ni l'exécution des tests.

### 2.3 Qualité du code
- Bonne séparation cœur / plugins / contrôleurs ; services dédiés (IA, mailing, scoring).
- Race conditions corrigées (XP atomique, création de tentative sous transaction+lock).
- Dashboard optimisé (agrégat SQL unique + cache 60 s), index de performance ajoutés.
- Reste à extraire : la logique de `saveStructure` (volumineuse) pourrait migrer dans un
  `TestStructureService` (QC-05, non bloquant).

### 2.4 Points moyens/faibles non traités (non bloquants)
QC-04 (logs `SequenceRunner`), QC-06/QC-17 (refactor lisibilité scoring), P-03 (allègement
`history`), P-15 (cache `Setting::get` — déjà partiellement caché). À planifier.

---

## 3. Audit sécurité

| Réf | Sujet | État |
|-----|-------|------|
| SEC-01 | `install.php` contournable `?force=1` | ✅ Corrigé (guard en tête, aucune exception) |
| SEC-02 | Bloc AJAX avant le guard | ✅ Corrigé (guard prioritaire) |
| SEC-03 | Rate-limiting login/register/forgot | ✅ Corrigé (`throttle`) |
| SEC-04 | Divulgation `?force` dans la page | ✅ Corrigé |
| SEC-05 | `service_provider` plugin non validé (RCE) | ✅ Corrigé (FQCN + namespace) |
| SEC-06 | Page de résultats non whitelistée | ✅ Corrigé (liste blanche) |
| SEC-07 | **XSS stocké dans emails de campagne** | 🟠 **Ouvert** — `{!! $html !!}` non assaini |
| SEC-08 | `v-html` sur labels de pagination | ✅ Corrigé (`{{ }}`) |
| SEC-09 | Vérification email auto | ✅ Corrigé (conditionnel local/testing/log) |
| SEC-10 | Injection `.env` par newline | ✅ Corrigé (nettoyage `\n\r\0`) |
| SEC-11 | Filtre statut leads sans whitelist | ✅ Corrigé |
| SEC-12 | Nom de fichier CV non assaini | ✅ Corrigé (`basename`) |
| SEC-13 | Rôle `professional` trop permissif | ✅ Corrigé (tests/plugins/réglages = admin seul) |
| SEC-14 | Format clés API peu validé | 🟡 Faible — à renforcer |
| SEC-15 | `attemptId` en file sans contrôle d'ownership | 🟡 Faible — à renforcer |

### Point ouvert prioritaire — SEC-07
`resources/views/mail/layouts/campaign.blade.php` rend le corps HTML des campagnes via
`{!! $html !!}` sans assainissement. Un compte `professional`/`admin` malveillant (ou
compromis) peut injecter du JS dans des emails envoyés en masse.
**Atténuation actuelle :** création de campagnes réservée aux rôles authentifiés
(admin/professional) — pas au grand public.
**Recommandation :** assainir le HTML via **HTMLPurifier** avant stockage, ou restreindre
la création de campagnes au seul rôle `admin`.

### Recommandations générales
- Après mise en ligne : changer immédiatement le mot de passe admin par défaut.
- En production, éviter `MAIL_MAILER=log/array` (réactiverait la vérification email auto).
- Bloquer l'accès à `install.php` au niveau serveur (nginx/Apache) une fois `.installed` présent.
- Activer `QUEUE_CONNECTION=database` + `queue:work` sur OVH pour fiabiliser le job IA.

---

## Synthèse

Reconstruction réussie : **0 fichier tronqué**, structure cohérente, correctifs de sécurité
et de performance majeurs appliqués, commit propre (`09ec99e`). Avant production : (1) régler
la synchro du dossier (cause racine), (2) lancer la passe runtime (lint + migrate + build +
tests), (3) traiter SEC-07.
