<script setup>
/**
 * RadarChart — toile d'araignée PraxiQuest, style cabinet de conseil.
 * SVG pur, sans dépendance, palette parchemin cohérente avec ScoreGauge.
 *
 * Usage :
 *   <RadarChart :axes="[
 *      { label: 'Écoute', value: 72, color: '#A67520' },
 *      { label: 'Assertivité', value: 54 },
 *   ]" :max="100" />
 *
 * - 3 à 12 axes supportés.
 * - `value` exprimée sur l'échelle 0..max (défaut 100).
 * - `color` par axe : optionnelle (teinte le sommet et la valeur).
 */
import { computed, getCurrentInstance } from 'vue'

const props = defineProps({
    axes:       { type: Array,  default: () => [] },
    max:        { type: Number, default: 100 },
    size:       { type: Number, default: 400 },
    accent:     { type: String, default: '#A67520' },
    rings:      { type: Number, default: 4 },
    showValues: { type: Boolean, default: true },
})

// ViewBox 540×540 — espace généreux pour les labels
const VW = 540, VH = 540
const CX = 270, CY = 270, R = 130

const pts = computed(() => {
    const n = props.axes.length || 1
    return props.axes.map((a, i) => {
        const angle = (-Math.PI / 2) + (i * 2 * Math.PI / n)
        const ratio  = Math.max(0, Math.min(1, (a.value ?? 0) / (props.max || 100)))
        const cos = Math.cos(angle), sin = Math.sin(angle)
        // offset label = R + 40px depuis le centre
        const LP = 42
        return {
            ...a, angle, cos, sin, ratio,
            gx: CX + cos * R,
            gy: CY + sin * R,
            dx: CX + cos * R * ratio,
            dy: CY + sin * R * ratio,
            lx: CX + cos * (R + LP),
            ly: CY + sin * (R + LP),
            anchor: cos > 0.25 ? 'start' : cos < -0.25 ? 'end' : 'middle',
        }
    })
})

const polygon = computed(() =>
    pts.value.map(p => `${p.dx.toFixed(1)},${p.dy.toFixed(1)}`).join(' ')
)

// Polygone de fond de la zone radar (plein rayon)
const bgPoly = computed(() =>
    pts.value.map(p => `${p.gx.toFixed(1)},${p.gy.toFixed(1)}`).join(' ')
)

// Anneaux concentriques
const ringPolys = computed(() => {
    const out = []
    for (let r = 1; r <= props.rings; r++) {
        const f = r / props.rings
        out.push({
            f,
            poly: pts.value
                .map(p => `${(CX + p.cos * R * f).toFixed(1)},${(CY + p.sin * R * f).toFixed(1)}`)
                .join(' '),
        })
    }
    return out
})

const uid = getCurrentInstance().uid

// hex → rgba pour le remplissage
const tint = (hex, a) => {
    const h = (hex || '#A67520').replace('#', '')
    const r = parseInt(h.slice(0, 2), 16)
    const g = parseInt(h.slice(2, 4), 16)
    const b = parseInt(h.slice(4, 6), 16)
    return `rgba(${r},${g},${b},${a})`
}

const fill = computed(() => tint(props.accent, 0.22))
</script>

<template>
    <div :style="`width:100%;max-width:${size}px;margin:0 auto;overflow:visible`">
    <svg :viewBox="`0 0 ${VW} ${VH}`"
         overflow="visible"
         style="width:100%;height:auto;display:block;overflow:visible"
         role="img"
         :aria-label="`Graphique radar à ${axes.length} dimensions`">

        <defs>
            <!-- Filtre ombre portée légère sur le polygone data -->
            <filter :id="`rc-shadow-${uid}`" x="-20%" y="-20%" width="140%" height="140%">
                <feGaussianBlur in="SourceAlpha" stdDeviation="5" result="blur"/>
                <feOffset dx="0" dy="3" result="offset"/>
                <feComponentTransfer result="shadow">
                    <feFuncA type="linear" slope="0.15"/>
                </feComponentTransfer>
                <feMerge>
                    <feMergeNode in="shadow"/>
                    <feMergeNode in="SourceGraphic"/>
                </feMerge>
            </filter>
        </defs>

        <!-- Zone radar : fond parchemin clair -->
        <polygon :points="bgPoly"
                 fill="rgba(250,248,244,0.85)"
                 stroke="none" />

        <!-- Anneaux de graduation -->
        <polygon v-for="(ring, i) in ringPolys" :key="'ring'+i"
                 :points="ring.poly"
                 fill="none"
                 :stroke="i === ringPolys.length - 1
                    ? 'rgba(42,30,8,0.28)'
                    : 'rgba(42,30,8,0.10)'"
                 :stroke-width="i === ringPolys.length - 1 ? 1.5 : 1" />

        <!-- Rayons (spokes) -->
        <line v-for="(p, i) in pts" :key="'spoke'+i"
              :x1="CX" :y1="CY" :x2="p.gx" :y2="p.gy"
              stroke="rgba(42,30,8,0.16)" stroke-width="1" />

        <!-- Polygone de données — avec ombre -->
        <polygon :points="polygon"
                 :fill="fill"
                 :stroke="accent"
                 stroke-width="2.5"
                 stroke-linejoin="round"
                 :filter="`url(#rc-shadow-${uid})`"
                 style="transition:all .6s ease" />

        <!-- Halo + point sur chaque sommet -->
        <g v-for="(p, i) in pts" :key="'dot'+i">
            <circle :cx="p.dx" :cy="p.dy" r="9"
                    :fill="tint(p.color || accent, 0.18)" />
            <circle :cx="p.dx" :cy="p.dy" r="4.5"
                    :fill="p.color || accent"
                    stroke="#FAF8F4" stroke-width="2" />
        </g>

        <!-- Labels des axes -->
        <g v-for="(p, i) in pts" :key="'lbl'+i">
            <!-- Fond blanc léger derrière le label pour lisibilité -->
            <text :x="p.lx" :y="p.ly - (showValues ? 7 : 0)"
                  :text-anchor="p.anchor"
                  dominant-baseline="middle"
                  font-family="'Space Grotesk','Inter','Segoe UI',system-ui,sans-serif"
                  font-size="13.5"
                  font-weight="600"
                  fill="#1C1408"
                  paint-order="stroke"
                  stroke="#FAF8F4"
                  stroke-width="4"
                  stroke-linejoin="round">{{ p.label }}</text>
            <text :x="p.lx" :y="p.ly - (showValues ? 7 : 0)"
                  :text-anchor="p.anchor"
                  dominant-baseline="middle"
                  font-family="'Space Grotesk','Inter','Segoe UI',system-ui,sans-serif"
                  font-size="13.5"
                  font-weight="600"
                  fill="#1C1408">{{ p.label }}</text>

            <text v-if="showValues"
                  :x="p.lx" :y="p.ly + 10"
                  :text-anchor="p.anchor"
                  dominant-baseline="middle"
                  font-family="'Space Mono','Roboto Mono','Courier New',monospace"
                  font-size="12"
                  :fill="p.color || '#A67520'"
                  font-weight="normal">{{ Math.round(p.value ?? 0) }}</text>
        </g>

        <!-- Légende des anneaux (droite, sur l'axe 12h) -->
        <g v-if="pts.length > 0">
            <text v-for="(ring, i) in ringPolys" :key="'rlbl'+i"
                  :x="CX + 6"
                  :y="CY - R * ring.f + 4"
                  font-family="'Space Mono','Courier New',monospace"
                  font-size="9"
                  fill="rgba(42,30,8,0.38)"
                  text-anchor="start">{{ Math.round(ring.f * max) }}</text>
        </g>

    </svg>
    </div>
</template>
