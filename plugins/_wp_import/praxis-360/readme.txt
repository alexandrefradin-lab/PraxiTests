=== Praxis 360 ===
Contributors: Alexandre Fradin — Praxis Accompagnement
Tags: 360, feedback, évaluation, soft skills, RH
Requires at least: 6.0
Tested up to: 6.5
Requires PHP: 8.0
Stable tag: 1.0.0
License: GPLv2 or later

Évaluation 360° multi-évaluateurs sur les soft skills : auto-évaluation + manager,
pairs et collaborateurs, invitations par email, agrégation anonyme et restitution comparative.

== Description ==

Praxis 360 permet de lancer une démarche d'évaluation à 360 degrés :

* 6 dimensions de soft skills, 36 items (échelle de fréquence à 5 points + « Non observé »).
* 3 questions ouvertes facultatives.
* Invitations par email (SMTP OVH) avec lien unique par évaluateur (token, sans création de compte).
* Anonymat garanti : les moyennes d'une catégorie ne s'affichent qu'à partir de 3 répondants.
* Restitution : radar auto vs regard des autres, tableau des écarts, forces, axes de progrès,
  angles morts et verbatims. Rapport imprimable (PDF via le navigateur).
* UX une-question-par-écran avec auto-avancement (~10 min de passation).
* Conçu pour OVH mutualisé (PHP 8.2), JavaScript vanilla, et portable vers PraxiQuest
  (préfixe de tables configurable via le filtre `praxis360_table_prefix`).

== Installation ==

1. Copiez le dossier `praxis-360` dans `wp-content/plugins/` (FTP) ou installez le .zip via
   Extensions > Ajouter > Téléverser une extension.
2. Activez « Praxis 360 ». Les tables sont créées automatiquement.
3. Créez une page WordPress contenant le shortcode [praxis360] et publiez-la.
4. Allez dans Praxis 360 > Réglages :
   - collez l'URL de cette page ;
   - renseignez le SMTP OVH (ssl0.ovh.net, port 465, ssl) avec un compte email du domaine.
5. Praxis 360 > Nouvelle campagne : saisissez le sujet et ses évaluateurs, puis envoyez les invitations.

== Changelog ==

= 1.0.0 =
* Version initiale.
