<script setup>
/**
 * ScoreGauge — jauge de score partagée PraxiQuest.
 * Arc 270° épuré, filet d'or, chiffre en Space Mono (palette parchemin AC).
 *
 * Usage :
 *   <ScoreGauge :score="globalScore" :color="gaugeColor" />
 */
import { computed } from 'vue'

const props = defineProps({
    score:      { type: Number, default: 0 },           // valeur affichée
    max:        { type: Number, default: 100 },          // borne haute
    color:      { type: String, default: '#A67520' },    // teinte de l'arc (or par défaut)
    size:       { type: Number, default: 180 },          // px
    trackColor: { type: String, default: 'rgba(42,30,8,0.12)' }, // sillon de fond
    showMax:    { type: Boolean, default: true },        // afficher « / max »
})

// Pourcentage borné 0–100 (l'arc est calé sur pathLength=100)
const pct = computed(() => {
    const m = props.max || 100
    return Math.min(100, Math.max(0, (props.score / m) * 100))
})
</script>

<template>
    <svg
        :width="size" :height="size"
        viewBox="0 0 120 120"
        :aria-label="`Score ${score} sur ${max}`"
        role="img"
    >
        <!-- Filet d'or discret -->
        <circle cx="60" cy="60" r="57" fill="none" stroke="#A67520" stroke-width="0.8" opacity="0.4" />

        <!-- Sillon de fond (arc 270° · centre 60,60 · r 50) -->
        <path
            d="M 24.645 95.355 A 50 50 0 1 1 95.355 95.355"
            fill="none"
            :stroke="trackColor"
            stroke-width="8"
            stroke-linecap="round"
        />
        <!-- Arc coloré — rempli via dasharray -->
        <path
            d="M 24.645 95.355 A 50 50 0 1 1 95.355 95.355"
            fill="none"
            :stroke="color"
            stroke-width="8"
            stroke-linecap="round"
            pathLength="100"
            :stroke-dasharray="`${pct} 100`"
            style="transition: stroke-dasharray 1s ease"
        />

        <!-- Score -->
        <text
            x="60" y="62"
            text-anchor="middle"
            font-family="'Space Mono', monospace"
            font-size="30" font-weight="700"
            fill="var(--text-primary, #2A1E08)"
        >{{ score }}</text>
        <text
            v-if="showMax"
            x="60" y="78"
            text-anchor="middle"
            font-family="'Space Mono', monospace"
            font-size="9" letter-spacing="1.5"
            fill="var(--text-secondary, #6B5A3E)"
        >/ {{ max }}</text>
    </svg>
</template>
