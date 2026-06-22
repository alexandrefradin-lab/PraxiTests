# PraxiQuest — Journal de session

> Session du 19 juin 2026 · Thème aventure + déploiement OVH

---

## Objectif de session

Déployer le thème AC Parchment sur `praxiquest.decisionpro.fr` et refondre la landing page avec un univers **voyage intérieur / aventure** — sans aucune référence au bilan de compétences.

---

## Ce qui a été fait

### 1. Diagnostic du thème non appliqué

- Le bundle Vite compilé (`Landing-CCaRgnIh.js`) était issu de l'**ancienne** Landing.vue avec des classes Tailwind (`bg-white`, `text-slate-900`, gradients indigo/teal)
- Le fichier `resources/css/app.css` compilé définissait `--pt-cream: #F7F5F0` (presque blanc) et `--pt-navy: #1B2B3A` (bleu marine) — l'opposé du parchemin voulu
- La nouvelle Landing.vue (avec variables CSS inline) n'avait **jamais été compilée** en production

### 2. Correctif temporaire — override CSS dans Blade

Injection d'un bloc `<style>` dans `resources/views/app.blade.php`, placé **après** `@vite` pour écraser les variables compilées :

- Redéfinition des variables `--pt-*` (parchemin, or, brun foncé)
- Redéfinition des variables `--bg-base`, `--color-primary`, etc.
- Override des classes Tailwind de l'ancien bundle : `.bg-white`, `.text-slate-900`, `.bg-indigo-600`, etc.
- Override des gradients : `.bg-clip-text.text-transparent { -webkit-text-fill-color: #A67520 !important }`

### 3. Résolution des conflits Git

- Suppression manuelle du `.git/HEAD.lock` bloquant les opérations
- Résolution d'une divergence de branches (commit accidentel sur OVH)
- Push via `--force-with-lease`
- Résolution d'un conflit de merge dans `app.blade.php` (éditeur nano ouvert par git)

### 4. Refonte de la Landing page — univers aventure

**Concept retenu :** voyage intérieur, terra incognita, cartographie de soi.  
Zéro mention de bilan de compétences, reconversion, ou terminologie RH.

**Champ sémantique :**
- Expédition, quête, horizons, terra incognita, exploration profonde
- Actes narratifs (I, II, III, IV) au lieu d'étapes numérotées
- « L'ancrage / L'exploration / La révélation / L'horizon »

**Headline retenu :** *La plus grande aventure commence en toi.*

**Éléments visuels :**
- Hero sombre (`#0D0800`) avec grille de carte ancienne (rose des vents, méridiens, cercles concentriques) en SVG
- Badge : « Voyage intérieur · Augmenté par IA »
- Stats : 15 horizons révélés · 100% personnalisé · RGPD
- Section intro avec boussole SVG (compas de navigation)
- 4 actes du voyage (ligne connectrice + cercles romains)
- 3 piliers (Intelligence augmentée / Narration immersive / Psychologie de la décision)
- Citation testimonial en Cormorant Garamond italique
- CTA final : *Ta terra incognita t'attend.*
- Footer minimaliste

**Typographie :**
- Cormorant Garamond (serif, poids 300/400/600, italic) — titres et headings
- DM Sans (300/400/500) — corps et navigation

**Palette :**
| Rôle | Valeur |
|---|---|
| Fond hero / dark sections | `#0D0800` |
| Fond parchment | `#F5EDD8` |
| Or principal | `#A67520` |
| Or lumineux | `#C8A040` |
| Or sombre | `#7D5510` |
| Texte sombre | `#110900` |
| Texte muted | `#6B5535` |

### 5. Correction du bug de build — History.vue

Erreur Rollup lors de `npm run build` :

```
Expected ',', got 'as'
($event.currentTarget as HTMLElement).style.cssText
```

**Cause :** cast TypeScript dans un handler inline de template Vue — non supporté par Rollup sans plugin dédié.

**Fix :** suppression du cast `as HTMLElement` dans `History.vue` (ligne 161-162).

```vue
<!-- Avant -->
@mouseenter="($event.currentTarget as HTMLElement).style.cssText += '...'"

<!-- Après -->
@mouseenter="$event.currentTarget.style.cssText += '...'"
```

### 6. Build et déploiement

Script `rebuild-full.ps1` exécuté avec succès :

```
✓ 210 modules transformed.
Landing-bD9eCV4A.js   21.77 kB  ← nouveau bundle Landing
✓ built in 4.52s
```

Fichiers clés :
- `Landing-CCaRgnIh.js` → **supprimé** (ancien bundle)
- `Landing-bD9eCV4A.js` → **créé** (nouveau design aventure)
- `app-B3htSr-z.css` → **supprimé**
- `app-DsZ007TJ.css` → **créé** (nouveau CSS compilé)

Commit pushé : `feat: landing page aventure — voyage interieur, terra incognita`

Pull effectué sur OVH (`~/praxiquest`) → `Déjà à jour` (push reçu).

---

## Fichiers modifiés

| Fichier | Changement |
|---|---|
| `resources/js/Pages/Public/Landing.vue` | Réécriture complète — design aventure |
| `resources/js/Pages/Candidate/History.vue` | Fix cast TypeScript ligne 161-162 |
| `resources/views/app.blade.php` | Override CSS thème parchemin (correctif temporaire) |
| `public/build/*` | Rebuild complet Vite |
| `rebuild-full.ps1` | Mise à jour du message de commit |

---

## À faire / à surveiller

- [ ] Vérifier le rendu sur `praxiquest.decisionpro.fr` (Ctrl+Shift+R)
- [ ] Éventuellement alléger le bloc `<style>` dans `app.blade.php` maintenant que le bon bundle est en prod
- [ ] Tester la navigation complète (Register, Login, Onboarding, Tests)
- [ ] Vérifier que les pages Candidate (History, Results) s'affichent correctement

---

*Généré automatiquement · PraxiQuest · Praxis Accompagnement*
