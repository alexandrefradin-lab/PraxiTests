<script setup>
/**
 * RadarChart — toile d'araignée partagée PraxiQuest.
 * SVG pur, sans dépendance, palette parchemin (cohérent avec ScoreGauge).
 *
 * Usage :
 *   <RadarChart :axes="[
 *      { label: 'Écoute', value: 72, color: '#A67520' },
 *      { label: 'Assertivité', value: 54 },
 *      ...
 *   ]" :max="100" />
 *
 * - 3 à 12 axes supportés.
 * - `value` exprimée sur l'échelle 0..max (défaut 100).
 * - `color` par axe : optionnelle (sert à teinter le sommet) ; la couleur
 *   globale du polygone est `accent`.
 */
import { computed } from 'vue'

const props = defineProps({
    axes:    { type: Array,  default: () => [] },      // [{ label, value, color? }]
    max:     { type: Number, default: 100 },
    size:    { type: Number, default: 340 },           // viewBox carré
    accent:  { type: String, default: '#A67520' },     // teinte du polygone (or)
    rings:   { type: Number, default: 4 },             // anneaux de graduation
    showValues: { type: Boolean, default: true },
})

const CX = 160, CY = 160, R = 120   // centre + rayon dans un viewBox 0 0 320 320

const pts = computed(() => {
    const n = props.axes.length || 1
    return props.axes.map((a, i) => {
        const angle = (-Math.PI / 2) + (i * 2 * Math.PI / n)   // départ en haut, sens horaire
        const ratio = Math.max(0, Math.min(1, (a.value ?? 0) / (props.max || 100)))
        const cos = Math.cos(angle), sin = Math.sin(angle)
        return {
            ...a,
            angle, cos, sin, ratio,
            // sommet de la grille (rayon plein)
            gx: CX + cos * R,
            gy: CY + sin * R,
            // sommet de la donnée
            dx: CX + cos * R * ratio,
            dy: CY + sin * R * ratio,
            // ancrage du label selon la position horizontale
            lx: CX + cos * (R + 18),
            ly: CY + sin * (R + 18),
            anchor: cos > 0.25 ? 'start' : cos < -0.25 ? 'end' : 'middle',
        }
    })
})

const polygon = computed(() => pts.value.map(p => `${p.dx.toFixed(1)},${p.dy.toFixed(1)}`).join(' '))

const ringPolys = computed(() => {
    const out = []
    for (let r = 1; r <= props.rings; r++) {
        const f = r / props.rings
        const poly = pts.value.map(p => `${(CX + p.cos * R * f).toFixed(1)},${(CY + p.sin * R * f).toFixed(1)}`).join(' ')
        out.push(poly)
    }
    return out
})

// hex → rgba (pour le remplissage translucide)
const tint = (hex, a) => {
    const h = (hex || '#A67520').replace('#', '')
    const r = parseInt(h.slice(0, 2), 16), g = parseInt(h.slice(2, 4), 16), b = parseInt(h.slice(4, 6), 16)
    return `rgba(${r},${g},${b},${a})`
}
const fill = computed(() => tint(props.accent, 0.16))
</script>

<template>
    <svg :width="size" :height="size" viewBox="0 0 320 320" role="img"
         :aria-label="`Graphique radar à ${axes.length} dimensions`">
        <!-- Anneaux de graduation -->
        <polygon v-for="(poly, i) in ringPolys" :key="'ring' + i"
                 :points="poly" fill="none"
                 stroke="rgba(42,30,8,0.10)" stroke-width="1" />
        <!-- Rayons -->
        <line v-for="(p, i) in pts" :key="'spoke' + i"
              :x1="CX" :y1="CY" :x2="p.gx" :y2="p.gy"
              stroke="rgba(42,30,8,0.10)" stroke-width="1" />

        <!-- Polygone de données -->
        <polygon :points="polygon" :fill="fill" :stroke="accent" stroke-width="2"
                 stroke-linejoin="round" style="transition: all .8s ease" />

        <!-- Sommets -->
        <circle v-for="(p, i) in pts" :key="'pt' + i"
                :cx="p.dx" :cy="p.dy" r="4"
                :fill="p.color || accent" stroke="#F0E8D4" stroke-width="1.5" />

        <!-- Labels d'axes -->
        <g v-for="(p, i) in pts" :key="'lbl' + i">
            <text :x="p.lx" :y="p.ly - (showValues ? 4 : 0)"
                  :text-anchor="p.anchor" dominant-baseline="middle"
                  font-family="'Space Grotesk', sans-serif" font-size="11" font-weight="600"
                  fill="#4A3C20">{{ p.label }}</text>
            <text v-if="showValues" :x="p.lx" :y="p.ly + 9"
                  :text-anchor="p.anchor" dominant-baseline="middle"
                  font-family="'Space Mono', monospace" font-size="10"
                  :fill="p.color || '#8a7550'">{{ Math.round(p.value ?? 0) }}</text>
        </g>
    </svg>
</template>
