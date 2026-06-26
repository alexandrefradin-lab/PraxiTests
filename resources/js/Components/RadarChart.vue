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
    size:       { type: Number, default: 460 },
    accent:     { type: String, default: '#A67520' },
    rings:      { type: Number, default: 4 },
    showValues: { type: Boolean, default: true },
})

// ViewBox 600×600 — espace généreux pour les labels
const VW = 600, VH = 600
const CX = 300, CY = 300, R = 155

const pts = computed(() => {
    const n = props.axes.length || 1
    return props.axes.map((a, i) => {
        const angle = (-Math.PI / 2) + (i * 2 * Math.PI / n)
        const ratio  = Math.max(0, Math.min(1, (a.value ?? 0) / (props.max || 100)))
        const cos = Math.cos(angle), sin = Math.sin(angle)
        // offset label depuis le bord extérieur
        const LP = 54
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
            label: Math.round(f * props.max),
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

// Graduation du bord extérieur du spoke 0 (12h) pour les ring labels
const ringLabelSpoke = computed(() => {
    if (!pts.value.length) return null
    // on utilise le spoke à 3h (cos positif) pour éviter collision avec labels
    // en réalité on prend le spoke indexé à env 1/4 du cercle
    const n = pts.value.length
    const idx = Math.round(n / 4) % n
    return pts.value[idx]
})
</script>

<template>
    <div :style="`width:100%;max-width:${size}px;margin:0 auto;overflow:visible`">
    <svg :viewBox="`0 0 ${VW} ${VH}`"
         overflow="visible"
         style="width:100%;height:auto;display:block;overflow:visible"
         role="img"
         :aria-label="`Graphique radar à ${axes.length} dimensions`">

        <defs>
            <!-- Gradient radial pour le remplissage de la zone données -->
            <radialGradient :id="`rc-grad-${uid}`" cx="50%" cy="50%" r="50%">
                <stop offset="0%"   :stop-color="accent" stop-opacity="0.06"/>
                <stop offset="100%" :stop-color="accent" stop-opacity="0.32"/>
            </radialGradient>

            <!-- Filtre ombre portée légère sur le polygone data -->
            <filter :id="`rc-shadow-${uid}`" x="-20%" y="-20%" width="140%" height="140%">
                <feGaussianBlur in="SourceAlpha" stdDeviation="6" result="blur"/>
                <feOffset dx="0" dy="3" result="offset"/>
                <feComponentTransfer result="shadow">
                    <feFuncA type="linear" slope="0.18"/>
                </feComponentTransfer>
                <feMerge>
                    <feMergeNode in="shadow"/>
                    <feMergeNode in="SourceGraphic"/>
                </feMerge>
            </filter>

            <!-- Filtre glow pour les points hauts scores -->
            <filter :id="`rc-glow-${uid}`" x="-50%" y="-50%" width="200%" height="200%">
                <feGaussianBlur in="SourceGraphic" stdDeviation="3" result="blur"/>
                <feMerge>
                    <feMergeNode in="blur"/>
                    <feMergeNode in="SourceGraphic"/>
                </feMerge>
            </filter>
        </defs>

        <!-- Zone radar : fond parchemin clair -->
        <polygon :points="bgPoly"
                 fill="rgba(250,248,244,0.90)"
                 stroke="none" />

        <!-- Anneaux de graduation -->
        <polygon v-for="(ring, i) in ringPolys" :key="'ring'+i"
                 :points="ring.poly"
                 fill="none"
                 :stroke="i === ringPolys.length - 1
                    ? 'rgba(42,30,8,0.30)'
                    : 'rgba(42,30,8,0.10)'"
                 :stroke-width="i === ringPolys.length - 1 ? 1.5 : 1"
                 :stroke-dasharray="i < ringPolys.length - 1 ? '4 3' : 'none'" />

        <!-- Rayons (spokes) -->
        <line v-for="(p, i) in pts" :key="'spoke'+i"
              :x1="CX" :y1="CY" :x2="p.gx" :y2="p.gy"
              stroke="rgba(42,30,8,0.14)" stroke-width="1" />

        <!-- Polygone de données — gradient + ombre -->
        <polygon :points="polygon"
                 :fill="`url(#rc-grad-${uid})`"
                 :stroke="accent"
                 stroke-width="2.5"
                 stroke-linejoin="round"
                 :filter="`url(#rc-shadow-${uid})`"
                 style="transition:all .6s ease" />

        <!-- Halo + point sur chaque sommet -->
        <g v-for="(p, i) in pts" :key="'dot'+i">
            <!-- glow sur les scores élevés (>= 70) -->
            <circle v-if="p.ratio >= 0.7"
                    :cx="p.dx" :cy="p.dy" r="12"
                    :fill="tint(p.color || accent, 0.22)"
                    :filter="`url(#rc-glow-${uid})`" />
            <!-- halo standard -->
            <circle :cx="p.dx" :cy="p.dy" r="10"
                    :fill="tint(p.color || accent, 0.15)" />
            <!-- point central -->
            <circle :cx="p.dx" :cy="p.dy" r="5.5"
                    :fill="p.color || accent"
                    stroke="#FAF8F4" stroke-width="2.5" />
        </g>

        <!-- Labels des axes -->
        <g v-for="(p, i) in pts" :key="'lbl'+i">
            <!-- Halo de lisibilité (stroke blanc) -->
            <text :x="p.lx" :y="p.ly - (showValues ? 8 : 0)"
                  :text-anchor="p.anchor"
                  dominant-baseline="middle"
                  font-family="'Space Grotesk','Inter','Segoe UI',system-ui,sans-serif"
                  font-size="13"
                  font-weight="600"
                  :fill="p.color || '#1C1408'"
                  paint-order="stroke"
                  stroke="#FAF8F4"
                  stroke-width="5"
                  stroke-linejoin="round">{{ p.label }}</text>
            <!-- Texte coloré avec la couleur de l'axe -->
            <text :x="p.lx" :y="p.ly - (showValues ? 8 : 0)"
                  :text-anchor="p.anchor"
                  dominant-baseline="middle"
                  font-family="'Space Grotesk','Inter','Segoe UI',system-ui,sans-serif"
                  font-size="13"
                  font-weight="600"
                  :fill="p.color || '#1C1408'">{{ p.label }}</text>

            <!-- Valeur numérique -->
            <text v-if="showValues"
                  :x="p.lx" :y="p.ly + 10"
                  :text-anchor="p.anchor"
                  dominant-baseline="middle"
                  font-family="'Space Mono','Roboto Mono','Courier New',monospace"
                  font-size="12"
                  :fill="p.color || accent"
                  paint-order="stroke"
                  stroke="#FAF8F4"
                  stroke-width="3"
                  font-weight="700">{{ Math.round(p.value ?? 0) }}</text>
            <text v-if="showValues"
                  :x="p.lx" :y="p.ly + 10"
                  :text-anchor="p.anchor"
                  dominant-baseline="middle"
                  font-family="'Space Mono','Roboto Mono','Courier New',monospace"
                  font-size="12"
                  :fill="p.color || accent"
                  font-weight="700">{{ Math.round(p.value ?? 0) }}</text>
        </g>

        <!-- Graduations sur le spoke 0 (12h) -->
        <g v-if="pts.length > 0">
            <text v-for="(ring, i) in ringPolys" :key="'rlbl'+i"
                  :x="CX + 5"
                  :y="CY - R * ring.f + 4"
                  font-family="'Space Mono','Courier New',monospace"
                  font-size="8.5"
                  fill="rgba(42,30,8,0.40)"
                  text-anchor="start">{{ ring.label }}</text>
        </g>

    </svg>
    </div>
</template>
