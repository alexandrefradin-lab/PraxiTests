# Refonte typographique des PDF (2026-06-22)

## Problème
Les rapports PDF (synthèse par test + Grimoire) paraissaient « moches » : DomPDF
n'utilisait que les polices **DejaVu** livrées par défaut (Serif/Sans), au rendu
lourd et bureautique. Le gabarit « Codex » lui-même était soigné — seule la
typographie trahissait le tout.

## Solution retenue
On **garde DomPDF** (contrainte OVH mutualisé = pur PHP, pas de Chromium) et on
embarque deux belles polices libres (licence **OFL**, embarquables) :

- **Lora** (serif chaleureux, façon manuscrit) → titres, verdict, métiers, médaillon.
- **Lato** (sans humaniste très lisible) → corps de texte, synthèse, profil.
- **DejaVu Sans Mono** conservée pour les « kickers », données chiffrées et les
  symboles géométriques (● ▲) qu'elle est la seule à couvrir.

Repli **DejaVu** conservé dans chaque pile `font-family` : si l'hôte ne charge
pas les TTF, le rendu actuel reste intact (aucune régression possible).

## Fichiers modifiés / ajoutés
- `resources/fonts/` — 8 TTF (Lora + Lato, Regular/Bold/Italic/BoldItalic),
  sous-ensemblées au Latin + ponctuation + symboles utilisés (~136 Ko au total).
- `resources/views/pdf/results.blade.php` — bloc `@font-face`, familles Serif→Lora,
  Sans→Lato.
- `resources/views/pdf/grimoire.blade.php` — même duo de polices.
- `storage/fonts/.gitkeep` — dossier de cache DomPDF (doit être inscriptible).

Aucune dépendance Composer ajoutée. Aucun chargement de police distant.

## Détail technique
- Glyphes vérifiés : Lato couvre → ● • — · … € œ et tous les accents FR.
  Lora ne sert qu'aux titres (texte pur), il n'a donc pas besoin de → ● ▲.
- Les `@font-face` pointent en chemin absolu via `resource_path('fonts/...')`,
  lu par DomPDF puis mis en cache dans `storage/fonts` (config barryvdh par défaut :
  `font_dir` = `font_cache` = `storage_path('fonts')`).

## Déploiement OVH
1. Commiter les TTF (binaires) : `git add -f resources/fonts/*.ttf storage/fonts/.gitkeep`
   puis le workflow habituel `deploy-ovh.ps1`.
2. Sur le serveur, s'assurer que le cache de polices est inscriptible :
   `chmod -R 775 storage/fonts`
3. Vider le cache de vues compilées : `php artisan view:clear`
4. Régénérer un PDF de test (`/results/{attempt}/pdf`) et un Grimoire pour valider.

> Si les polices ne s'affichent pas (repli DejaVu visible) : vérifier les droits
> d'écriture sur `storage/fonts`, et que `resources/fonts/*.ttf` ont bien été
> poussés (les binaires peuvent être ignorés selon la config git).

## Passe « rendu d'excellence » (mise en page)
En plus des polices, refonte de la mise en page (100 % dompdf-safe, repli conservé) :

- **Chapitres en chiffres romains** (I, II, III…) en or serif devant chaque
  intitulé de section — esprit Codex/manuscrit. Numérotation dynamique
  (`$roman` + `$chapN`), reste séquentielle même si des sections sont masquées.
- **Filets de section raffinés** : segment or épais (46 px) prolongé d'un filet
  filiforme — remplace les anciens `section-rule`/`section-hair`.
- **Sceau monogramme** « Q » en or sur la couverture + **ornement** ✦ ◆ ✦.
- **Barres de dimensions affinées** (7 px, plus fines et nettes).
- **Cartes métiers** avec accent or à gauche ; **verdict** à coins plus doux.
- Rythme typographique resserré (tailles de titres, interlignes, espacements).

Tout passe par des surcharges CSS en fin de feuille + quelques retouches de
balisage ; aucune structure `<table>` existante n'a été cassée.

## Aperçu
- `pdf-fonts-avant-apres.png` — comparatif polices DejaVu vs Lora+Lato.
- `pdf-rendu-excellence.png` — aperçu pleine page du nouveau design (simulation
  fidèle avec les vraies polices ; le rendu dompdf réel se valide au 1er déploiement).
