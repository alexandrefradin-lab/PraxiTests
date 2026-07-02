# Changelog — 2 juillet 2026

Récapitulatif des évolutions livrées en production (commits `303e56a` → `db13e55`).

---

## 🖥️ Expérience candidat

### Grimoire — cartes d'épreuves épurées
- Chaque carte de l'onglet « Mes épreuves » affiche un libellé **« Dans le détail »** suivi d'une phrase annonçant ce que la page de résultats révèle (radar, scores par dimension, analyse complète). Phrases curées par test (17 slugs) + repli générique.
- Le résumé IA tronqué (« …précisi... ») est supprimé des cartes.
- Intro de la section reformulée : « Ce que chaque épreuve te révèle. Ouvre le détail ou télécharge-le en PDF. »
- Commits : `303e56a`, `4268bdd`

### Mini-apps — descriptions complètes visibles
- **Salle du Trésor** : les descriptions des cartes ne sont plus coupées à 2 lignes (suppression du `line-clamp`).
- **Chaque page de mini-app** affiche désormais un encadré d'introduction avec la description complète du module (filet doré à gauche) :
  - via le `LibraryController` partagé : Le Refuge Intérieur, La Forge du Soi, La Voix du Héros, L'Art des Liens, Le Maître du Temps ;
  - individuellement : L'Étincelle, La Forge de l'Identité, Le Sanctuaire, Le Cap, L'Éveilleur.
- Nouvelle méthode `RewardCatalog::descriptionFor()` : lit la description du manifest de chaque plugin — tout futur module en bénéficie automatiquement.
- Commit : `5944c75`

### Compte — changement de mot de passe en libre-service
- Nouvelle page **« Changer mon sceau secret »** (`/account/password`) : mot de passe actuel + nouveau ×2, validation en français (min 8 caractères, confirmé, différent de l'actuel).
- Accessible depuis le menu utilisateur (desktop) et le drawer mobile.
- Complète le flux « Sceau oublié ? » (réinitialisation par email).
- Commit : `0277720`

---

## 🐛 Corrections de bugs

### « Ouvrir le trésor » ne faisait rien (www vs apex)
- Le catalogue de la Salle du Trésor (cache 5 min partagé) figeait des **URLs absolues** avec l'hôte de la requête ayant rempli le cache. Un visiteur sur l'autre domaine (`www.praxiquest.fr` vs `praxiquest.fr`) obtenait des liens cross-origin qu'Inertia refusait silencieusement.
- Correctif : URLs **relatives** dans `RewardCatalog::resolveEntry()` + clé de cache bumpée (`reward_catalog_v2`).
- Commit : `a3a42c2`

### Résultats de tests bloqués sur « Ton Grimoire se révèle… »
- Une passation terminée dont le job IA avait été tué (limite d'exécution OVH) restait **sans synthèse ni marque d'échec** : l'écran d'attente tournait indéfiniment (candidat comme admin).
- Correctif : la page de résultats détecte l'état zombie (terminée > 5 min, sans synthèse ni `ai_failed`), purge le verrou `ShouldBeUnique` et **relance automatiquement** `GenerateAttemptInsights`, avec cooldown anti-boucle de 5 min. Toute passation zombie se répare à sa prochaine consultation.
- Commit : `25763ce`

---

## 🌐 Domaine & infrastructure

### Domaine canonique : www.praxiquest.fr
- Redirection **301 `praxiquest.fr` → `www.praxiquest.fr`** dans `public/.htaccess` : un seul hôte = plus de doubles sessions (cookie par domaine), plus de 419 CSRF, plus de contenu dupliqué SEO.
- `APP_URL` de production alignée sur `https://www.praxiquest.fr`.
- Commits : `47a2d8a` (première version, sens inverse), `c6732d2` (sens définitif)

### 2FA admin débrayable
- L'obligation d'activer le 2FA pour les admins (SEC-M5) devient configurable : `PRAXIQUEST_ADMIN_2FA_REQUIRED` (défaut `true` ; passé à `false` en production à la demande). Le 2FA volontaire reste fonctionnel.
- ⚠️ Recommandation : réactiver après avoir configuré le 2FA une bonne fois.
- Commit : `758a24b`

### Compte administrateur
- Compte admin `alex.fradin@gmail.com` créé en production (rôle `admin`, email vérifié) via script serveur one-shot.

---

## 📣 Page d'accueil (landing)

- **Contenu aligné sur le produit actuel** : bandeau de stats à 4 chiffres animés (12 épreuves fondées · 50 horizons max · 10 modules offerts · RGPD), actes II-IV réécrits (modèles reconnus, Grimoire + impact de l'IA sur le métier, horizons réordonnables), **nouvelle section « Trois espaces, un seul voyage »** (L'Armurerie / Le Grimoire / La Salle du Trésor), CTA final et métadonnées SEO à jour.
- **Vocabulaire** : « horizons métiers » remplace « pistes métiers » partout (textes + SEO).
- **Chiffre corrigé** : 12 épreuves publiées (pas 15 — le 15 venait d'un compte de passations personnelles).
- **Accents restaurés** sur tous les textes visibles (le source avait été écrit sans accents).
- **Effets mobiles corrigés** : les hovers (cercles des 4 actes, cartes) sont réservés aux appareils à pointeur (`@media (hover:hover)`) — plus d'état « collé » après un tap. En remplacement, une **vague dorée** parcourt les chiffres romains I→IV à l'entrée de la section à l'écran (IntersectionObserver, jouée une fois, respecte `prefers-reduced-motion`).
- Commits : `3ff2e98`, `384a32b`, `508583d`, `c3c72be`, `04f3657`, `524c888`

---

## 📊 CRM / Back-office

### Leads à l'inscription
- **Chaque nouvelle inscription crée un lead** (`source: inscription`, `status: new`, score 20). Un lead existant pour le même email est rattaché au compte sans écraser sa source. Le listener RIASEC continue d'upgrader en `qualified` à la fin de « La Quête de la Voie ».
- Rattrapage one-shot exécuté en production pour les inscrits existants (2 leads créés).
- Commit : `f258cbc`

### Épreuves et résultats de chaque lead
- **Liste des leads** : colonne « Épreuves » (pastille dorée avec le nombre d'épreuves terminées, sous-requête SQL).
- **Fiche lead** : section « Épreuves » — tableau des passations (nom, statut badge, dates, synthèse IA), hors regards 360°. États vides gérés (lead sans compte / sans épreuve).
- **Consultation des résultats** : colonne « Résultats » avec liens **Voir ↗** (page complète du candidat) et **PDF**. `ResultController` autorise désormais propriétaire **ou admin** (les comptes professionnels restent exclus — cloisonnement à traiter séparément). Les contextes user-spécifiques (parcours mini-apps, panel 360) utilisent le propriétaire de la tentative, pas le visiteur.
- Ergonomie : tableaux défilables horizontalement sur mobile, dates insécables.
- Commits : `9209881`, `9210a81`, `640842d`

### Politique de confidentialité v1.1
- Nouvelle finalité « **Suivi et accompagnement** » (intérêt légitime) et paragraphe « **Accès interne** » dans les destinataires — couvre la consultation des résultats par les administrateurs. Version 1.1, datée du 2 juillet 2026.
- Commit : `9033d07`

### Invitations multi-épreuves
- Le formulaire « Inviter un candidat » (`/admin/invitations/create`) passe du sélecteur mono-test à une **grille de cartes cochables** : titre thématique en gras + descriptif discret, case alignée, bord doré à la sélection, « Tout cocher / Tout décocher », compteur.
- Champs : épreuves (≥ 1), email, prénom, nom, expiration (défaut 30 jours).
- **Un seul email** par invitation, listant toutes les épreuves cochées (sujet et bouton adaptés au pluriel, « Bonjour {prénom} »).
- **Message d'invitation standard identique pour tous** (intégré au template email, affiché à titre informatif dans le formulaire) — plus de message à saisir à chaque envoi.
- Techniquement : une seule `TestInvitation` (`test_id` = première épreuve, liste complète dans `metadata.test_ids`) — pas de migration.
- Commits : `a1c96c8`, `c329037`, `db13e55`

---

## 🔧 Points de vigilance / suites possibles

- **2FA admin** : réactiver `PRAXIQUEST_ADMIN_2FA_REQUIRED=true` après configuration du 2FA (compte admin = accès aux données utilisateurs).
- **SMTP** : vérifier qu'un email d'invitation arrive réellement (tester en s'auto-invitant) — la config `MAIL_*` OVH a un historique de mode `log`.
- **UTM** : le champ existe sur les leads mais rien ne le remplit à l'inscription — à brancher le jour où des campagnes trackées démarrent.
- **Résultats pour les professionnels** : ouvrir la consultation aux conseillers exigera un cloisonnement par compte pro (et une mise à jour de la politique de confidentialité).
- **Harmonisation « horizons »** : l'application (page Grimoire) parle encore de « pistes métiers » — passe complète possible si souhaité.
