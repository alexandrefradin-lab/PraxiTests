# Design system PraxiTests — règles à respecter dans les pages plugin

> **Tout plugin DOIT utiliser ces tokens, aucune couleur ni police custom.**

## Composants Tailwind disponibles

```html
<!-- Carte / panneau -->
<div class="pt-card p-6">…</div>

<!-- Bouton primaire -->
<button class="pt-btn-primary">Action</button>

<!-- Bouton secondaire / ghost -->
<button class="pt-btn-ghost">Annuler</button>

<!-- Input / select / textarea -->
<input class="pt-input">
<select class="pt-input">…</select>
<textarea class="pt-input"></textarea>

<!-- Badge -->
<span class="pt-badge">Niveau 2</span>

<!-- Barre progression -->
<div class="pt-progress-track">
    <div class="pt-progress-fill" :style="{ width: percent + '%' }"></div>
</div>
```

## Couleurs — uniquement via tokens

```css
:root {
    --pt-primary: #4F46E5;     /* Indigo 600 */
    --pt-secondary: #10B981;   /* Emerald 500 */
    --pt-bg: #F8FAFC;
    --pt-text: #0F172A;
}
```

Tailwind utility classes valides :
- Indigo : `indigo-50`, `indigo-500`, `indigo-600`, `indigo-700`
- Emerald : `emerald-50`, `emerald-500`, `emerald-600`, `emerald-700`
- Slate : `slate-50`, `slate-100`, `slate-300`, `slate-500`, `slate-600`, `slate-700`, `slate-900`
- Couleurs sémantiques : `rose-600` (erreur), `amber-50/700` (warning)

**Gradient signature** : `bg-gradient-to-br from-indigo-500 to-emerald-500`

## Typographie

- Font : **Inter** (par défaut globale)
- Titres : `text-2xl font-semibold tracking-tight` (h1 page) / `text-xl font-semibold` (h2)
- Body : `text-sm` ou `text-base` selon densité
- Texte secondaire : `text-slate-500` ou `text-slate-600`

## Espacements

- Padding card standard : `p-6` ou `p-8`
- Gap grille : `gap-6` (lg) / `gap-4` (md) / `gap-3` (sm)
- Marge entre sections : `mb-8` / `mb-12`

## Layouts

- Candidat : importer `@/Layouts/CandidateLayout.vue`
- Admin : importer `@/Layouts/AdminLayout.vue`
- Auth : importer `@/Layouts/AuthLayout.vue`

**Ne jamais créer un layout custom** — tout plugin doit s'intégrer dans les layouts existants.

## Transitions / animations

- Hover card : `hover:shadow-md transition`
- Hover bouton : déjà dans `pt-btn-primary`
- Apparition : `transition-all duration-700` pour barres / fills
- Confettis (si débloquage / succès) : `canvas-confetti` (déjà installé)

## Iconographie

- `@heroicons/vue` — solid pour CTA, outline pour décoration

```vue
<script setup>
import { CheckCircleIcon, ArrowRightIcon } from '@heroicons/vue/24/outline'
</script>
```

## Anti-patterns à proscrire

- ❌ Couleurs hex inline (`style="color: #ff0000"`)
- ❌ Polices custom (Roboto, Open Sans, etc.)
- ❌ Layouts ou headers custom
- ❌ jQuery
- ❌ CSS pas via Tailwind
- ❌ Émojis sauf si explicitement justifié par le contenu
- ❌ Boutons rouges sauf actions destructrices
