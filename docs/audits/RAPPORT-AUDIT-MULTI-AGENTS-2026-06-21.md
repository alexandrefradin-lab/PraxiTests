# Rapport d'audit multi-agents — PraxiQuest

**Date :** 21 juin 2026
**Périmètre :** Laravel 11 + Inertia/Vue 3, ~7 650 lignes PHP, 41 fichiers Vue/JS, 14 plugins de tests.
**Méthode :** 5 agents spécialisés en parallèle (sécurité, bugs/fonctionnalité, cohérence/technique, ergonomie/UX, design). Lecture seule — aucun fichier modifié.

---

## Verdict global

Codebase **au-dessus de la moyenne pour un SaaS solo** : séparation `app/Core` / `app/Http` nette, moteur de tests propre (transactions + `lockForUpdate`), traitements IA correctement déportés en Jobs, sécurité plugins réfléchie (allow-list de namespaces anti-RCE, double validation MIME upload, clés API chiffrées, vérifs d'ownership systématiques contre l'IDOR). **Aucune faille critique directement exploitable** dans le code applicatif.

Les risques réels et actionnables sont **peu nombreux mais nets**, et plusieurs ont été signalés indépendamment par 2 ou 3 agents — ce sont les plus fiables :

| Convergence inter-agents | Signalé par |
|---|---|
| **`HtmlSanitizer` jamais branché** → XSS possible dans les emails | Sécurité, Bugs, Technique |
| **Violation du contrat d'échelle** (`value/max` au lieu de `(value-1)/(max-1)`) → scores gonflés | Bugs |
| **Header candidat non responsive** (pas de menu burger) | UX, Design |
| **Duplication des 5 plugins « parcours »** + migration cassante | Technique |
| **3 systèmes de tokens parallèles** + plugins hors-palette | Design |
| **Vérification email retirée** / dette de config prod | Sécurité, Technique |

---

## 1. Priorités CRITIQUES / ÉLEVÉES — à traiter en premier

### S1 · XSS dans le mailing — `HtmlSanitizer` écrit mais jamais appelé
La classe `HtmlSanitizer` (estampillée « SEC-07 ») n'est référencée nulle part. Le `body_html` des campagnes et séquences passe par `NeuromarketingOptimizer::enhanceHtml()` (simple `str_replace`) puis directement dans `CampaignMail` **sans assainissement**.
- Fichiers : `app/Core/Mailing/Services/CampaignService.php:31-36`, `app/Core/Mailing/Services/SequenceRunner.php:56-65`.
- Correction : `HtmlSanitizer::clean($html)` après `enhanceHtml`, dans les deux services. *(Sinon, supprimer le code mort — mais ici il faut le câbler.)*

### S2 · Scoring faussé — normalisation qui plancher les scores à >0 %
Le moteur générique normalise par `($raw / $max) * 100`. Comme le front émet `1..max` (jamais 0), une réponse minimale (1 sur 1-5) donne **20 % au lieu de 0 %**. Tous les tests sur le moteur `default` ont des scores mécaniquement gonflés (plage 20-100 au lieu de 0-100), ce qui fausse percentiles, labels, **synthèse IA et 15 métiers**.
- Fichiers : `app/Core/TestEngine/Scoring/DefaultScoringEngine.php:40-48` ; même bug dans `plugins/praxiself/src/Scoring/PraxiSelfScoringEngine.php:64`.
- Bonus PraxiSelf : un `arsort($rawScores)` (ligne 82-88) trie le tableau **en place** et désaligne libellés/radar côté Vue.
- Correction : `(value-1)/(max-1)` centré sur le minimum d'échelle ; supprimer le `arsort` en place (trier une copie).

### S3 · Contrat d'échelle non garanti côté serveur
La validation de `scale` est `['required','numeric']` : elle accepte `0`, des négatifs, des valeurs > max. Le « contrat 1..max » n'est garanti que par le front. Un client buggé/malveillant peut injecter `0` et casser le scoring.
- Fichier : `app/Http/Controllers/Candidate/AttemptController.php:135` (+ `TestEngine.php:65` stocke sans clamp).
- Correction : valider `between:min,max` depuis `question.options`, ou clamper dans `recordAnswer`.

### S4 · Durcir la configuration de production
`config/` ne contient ni `session.php`, ni `cors.php`, ni `sanctum.php` → dépendance totale aux defaults + `.env`. Or `.env.example` a `APP_DEBUG=true`, `APP_ENV=local`, et ne définit pas `SESSION_SECURE_COOKIE`. Risque : cookies de session en clair (vol via MITM), pages Ignition exposant secrets/stack si le `.env` de prod hérite de ces valeurs.
- Fichiers : `.env.example:2,4,29-30`.
- Correction : publier `config/session.php` (`secure`/`same_site=lax`/`http_only`), `APP_ENV=production` + `APP_DEBUG=false`, documenter `SESSION_SECURE_COOKIE=true`.

### S5 · Endpoints publics sans rate limiting
`GET /p/{token}`, `GET /i/{token}`, `/360/{token}*` (dont `answer` qui écrit en DB) et `POST /panel/{panel}/send` (envoi de mails) n'ont aucun `throttle`. Vecteurs : énumération de tokens, spam mail (relais), DoS IA.
- Fichiers : `routes/profile_share.php:14`, `routes/web.php` (Invitation/Evaluation360/Panel360).
- Correction : `->middleware('throttle:20,1')` sur les routes publiques, plafonner les envois par panel. *(Le login `throttle:5,1` et le reset `throttle:3,10` sont déjà bien protégés.)*

### S6 · États mailing & invitations incohérents
- Invitation 360° marquée `sent` **même si l'envoi échoue** (pas de try/catch) → l'évaluateur ne reçoit rien, jamais ré-émise. `Panel360Controller.php:95-98`.
- Campagne : statut `sending` **jamais clôturé** → reste « en cours » indéfiniment ; les échecs des workers async ne sont jamais comptés. `CampaignService.php:65-72`.
- Correction : ne passer `sent` qu'en cas de succès ; brancher les compteurs sur les events `MessageSent`/`MessageFailed`.

### S7 · Webhook Stripe/Cashier absent
Aucune route `cashier.webhook`. `EnsureSubscribed` s'appuie sur `subscribed()/onTrial()`, jamais mis à jour sans webhook → un utilisateur ayant annulé conserve l'accès. `STRIPE_WEBHOOK_SECRET` figure dans `.env.example` mais n'est jamais consommé.
- Correction : enregistrer le `WebhookController` de Cashier avec vérification de signature.

### S8 · Promesse « 15 métiers » non garantie
`JobSuggestionService` demande « exactement {count} » dans le prompt mais ne **vérifie/tronque/complète jamais** le tableau retourné. Si l'IA renvoie 9 ou 20 métiers, c'est stocké tel quel.
- Fichier : `app/Core/AI/Services/JobSuggestionService.php:27-45`.
- Correction : comptage + `array_slice($jobs, 0, $count)` + relance/alerte si sous le seuil.

---

## 2. Technique & cohérence (architecture)

- **T1 · Duplication des 5 plugins « parcours »** (`praxiflow/praxiself/praxispeak/praxizen/praxilink`) : chacun redéfinit modèle `JourneyProgress`, migration `journey_progress`, `Journey`, `Exercises`, seeder — pour **une seule table partagée**. Les 5 migrations font `Schema::create('journey_progress', …)` sans garde → un `migrate` global échoue à la 2ᵉ (masqué aujourd'hui par l'activation `--path` ciblée). → Centraliser table + modèle dans le Core, trait `JourneyPlugin`.
- **T2 · Gamification synchrone sur le hot path** : `AwardXpOnAnswer` branché sur `attempt.answered` (à chaque clic). À chaque réponse : `firstOrCreate` + `increment` + reload + `BadgeEvaluator` qui charge `Badge::all()` et fait un `exists()` **par badge**. N+1 proportionnel au nombre de badges. → Passer en queue, cacher `Badge::all()`, évaluer au `attempt.completed`.
- **T3 · Envoi de campagne 100 % synchrone dans la requête HTTP** (`CampaignService` non `ShouldQueue`) → risque de timeout PHP/Nginx sur OVH. → `SendCampaignJob` chunké (comme déjà fait proprement pour CV/Grimoire).
- **T4 · `Praxis360Result.vue` orpheline** : `praxis360` n'enregistre aucun filtre `results.inertia_page` et n'est pas dans l'allow-list de `ResultController` → les résultats 360° retombent sur la page générique, composant dédié mort. → Enregistrer le filtre + ajouter à l'allow-list.
- **T5 · Dépendances fantômes** : `spatie/laravel-activitylog` (0 usage, alors qu'un `AuditLog` maison existe en parallèle) et `predis/predis` (config `file`/`sync`, pas de Redis sur OVH) → à retirer.
- **T6 · Aucun Form Request** : validation 100 % inline (20 occurrences). 6 modèles encore en `$guarded = []` (`TestInvitation`, `Setting`, `TestQuestion`, etc.) contre la convention `$fillable` du projet → risque mass-assignment + incohérence.
- **T7 · Désordre racine** : 26 fichiers `.md` (5 docs de déploiement qui se chevauchent, 3 rapports d'audit datés, HANDOVER 27 Ko…), un binaire suspect `ziCvafWC` (248 Ko sans extension), `praxitests-upload.zip`, `praxitests-ftp.zip` (0 octet), 7 scripts deploy redondants. → Déplacer dans `docs/`, gitignorer zips/binaires, investiguer `ziCvafWC`.
- **T8 · Index manquants** : `test_attempts(panel_id, rater_relation)`, `test_invitations.professional_account_id` (belongsTo sans index/FK).

---

## 3. Ergonomie & UX (parcours candidat)

**Constat transversal : la métaphore RPG médiéval-fantasy envahit TOUT le wording fonctionnel.** Le CV devient « Codex de Compétences », le consentement RGPD un « Serment », la déconnexion « Quitter la Quête », les tests des « Épreuves ». Pour la cible (demandeurs d'emploi, cadres en reconversion, seniors), c'est un **risque d'infantilisation et d'abandon dès l'onboarding**, et de perte de crédibilité. Recommandation centrale : **garder l'habillage graphique, neutraliser le lexique des actions critiques** (« Votre profil / Votre CV / Mes résultats »).

Problèmes à fort impact :
- **U1 · PDF gated 90 secondes** : `setTimeout(... 90000)` cache le bouton « Télécharger mon Grimoire » pendant 90 s (« règle neuromarketing »). Le livrable principal est masqué sans raison visible → frustration/abandon. `ResultsShow.vue`. → Afficher dès la fin de lecture.
- **U2 · Typewriter non skippable** : la synthèse s'écrit lettre par lettre (~12 s, 40 ms/caractère) avant d'être lisible. → Rendre skippable (clic = tout afficher).
- **U3 · Absence de disclaimer sur la page résultats** : aucun cadre « résultat indicatif, ne remplace pas un conseiller » pour des 15 métiers générés par IA, public potentiellement fragile. Enjeu éthique. → Encart bienveillant + lien aide/France Travail.
- **U4 · Date d'ancienneté exacte requise** (`<input type=date>`) : friction inutile. → Select de tranches (« 6-12 mois / 1-2 ans… »).
- **U5 · Type `ranking` listé dans `needsConfirmButton` mais sans interface de réponse** → cul-de-sac si un test l'utilise. À vérifier/implémenter. `AttemptPlay.vue`.
- **U6 · Aucun mail candidat** (bienvenue, « résultats prêts », relance d'abandon) → abandon silencieux si le candidat quitte pendant la génération IA.

**À préserver** : sauvegarde/reprise de l'avancement (exemplaire), affichage mono-question, helpers + insights provisoires, définitions de dimensions au clic, fallback CV manuel, cartes métiers avec « prochaine étape », mail d'invitation 360° (sobre et pro — modèle à généraliser).

---

## 4. Design & frontend

- **D1 · 3 systèmes de tokens parallèles non synchronisés** : `tailwind.config.js` (`ac.*`), `app.css` (`--color-*`), `app.blade.php` (`--pt-*`) + redéfinitions par page. La même couleur or `#A67520` est écrite en dur dans 5+ endroits, déjà désynchronisée (`--text-secondary` diffère entre `app.css` et blade). Les overrides `!important` dans blade trahissent un **retrofit** (pages codées en SaaS générique puis « repeintes »). → Source unique = `tailwind.config.js`.
- **D2 · Plugins hors-palette** : les plugins consomment des tokens **non définis** (`--pt-info/success/warning/danger/bg/ink/phase-*`) → fallbacks hardcodés Material/Tailwind (indigo `#4F46E5`, vert `#16A34A`, `#f44336`…) sans rapport avec la palette AC. `praxivaleurs/praxicare/praxiemo/praximet/praximum` utilisent du `text-slate-*` générique. → Définir les tokens manquants, bannir les hex, corriger le `_template` à la source.
- **D3 · 1 seul composant réutilisable** (`ShareProfileButton.vue`) pour 35 pages + 15 plugins. Le pattern header titre+sous-titre+ligne or est copié-collé ; boutons en 3 formes concurrentes ; ~300 styles inline (`Login.vue` ~100, `Landing.vue` 98). → Bibliothèque atomique (`PageHeader`, `AcButton`, `AcCard`, `FormField`, `XpBar`, `EmptyState`, `Logo`…).
- **D4 · Accessibilité** : `prefers-reduced-motion` respecté **nulle part** (WCAG 2.3.3 + risque vestibulaire avec typewriter/glow/pulse) ; labels de formulaires non associés (`for`/`id` absents en Auth) ; ARIA quasi absent ; contraste du texte « ghost » `#B0A08A` sous AA ; tailles `text-[10px]` fréquentes.
- **D5 · Header candidat non responsive** (= U du parcours UX) : `<nav>` en flex inline sans breakpoint ni burger → débordement sur mobile, où une grande part des demandeurs d'emploi navigue. **Priorité partagée UX/Design.**
- **D6 · Marque** : `landing-praxiquest.html` (racine, 62 Ko) est une **3ᵉ identité divergente** (serif Cormorant + fond sombre) en contradiction avec l'app (parchemin clair, Space Grotesk) et avec le brief — orphelin à archiver. Logo incohérent (boussole SVG sur la vraie landing vs simple « P » ailleurs). `app.blade.php` précharge des fontes (Playfair+DM Sans) non utilisées par `app.css`.
- **D7 · Barre de progression Inertia violette** `#4F46E5` (`app.js`) hors-palette. → or `#A67520`.

---

## Top 10 priorités consolidées

1. **Brancher `HtmlSanitizer`** dans CampaignService + SequenceRunner (XSS emails). *[S1]*
2. **Corriger la normalisation `(value-1)/(max-1)`** dans `DefaultScoringEngine` + `PraxiSelfScoringEngine` (scores faussés → impacte synthèse IA + 15 métiers). *[S2]*
3. **Valider/clamper `scale` côté serveur** (contrat d'échelle non garanti par le back). *[S3]*
4. **Durcir la config prod** (`session.php`, `APP_DEBUG=false`) + **rate-limiter les endpoints publics**. *[S4, S5]*
5. **Fiabiliser les états mailing/invitations** (sent seulement si succès, clôture `sending`) + **brancher le webhook Stripe**. *[S6, S7]*
6. **Garantir 15 métiers** (comptage/troncature/relance après parsing IA). *[S8]*
7. **Mutualiser les 5 plugins « parcours »** (table+modèle Core, supprime la duplication ET la migration cassante) + **désynchroniser la gamification**. *[T1, T2]*
8. **Header candidat responsive** (menu burger) + **supprimer le gating 90 s du PDF / typewriter skippable**. *[D5/U1, U2]*
9. **Unifier les tokens design** (source unique) + **réparer les plugins hors-palette** + amorcer la bibliothèque de composants. *[D1, D2, D3]*
10. **Neutraliser le wording RPG des actions critiques** + **ajouter un disclaimer bienveillant** sur la page résultats. *[U-transversal, U3]*

---

*Audit produit par 5 agents spécialisés. Sévérités indiquées par chaque agent ; les items « convergents » (signalés par plusieurs agents) sont les plus fiables. Aucune correction n'a été appliquée — ce rapport est un point de départ priorisé.*
