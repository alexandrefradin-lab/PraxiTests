<script setup>
import { computed } from 'vue'

const props = defineProps({
    tests: { type: Array, default: () => [] }
})

// ── Nodes repositionnés pour viewBox 900×250 (compact) ──────────────────────
const NODES = [
    { slug: 'orientation-express', x: 450, y: 38,  label: 'Boussole' },
    { slug: 'praximet',            x: 602, y: 72,  label: 'La Voie' },
    { slug: 'praximum',            x: 660, y: 128, label: 'Grande Carte' },
    { slug: 'praxis360',           x: 602, y: 184, label: 'Constellation' },
    { slug: 'praxiemo',            x: 450, y: 215, label: 'Émotions' },
    { slug: 'praxicare',           x: 298, y: 184, label: 'Sentinelle' },
    { slug: 'praxiself',           x: 240, y: 128, label: 'Forge du Soi' },
    { slug: 'praxispeak',          x: 298, y: 72,  label: 'Voix du Héros' },
    { slug: 'praxivaleurs',        x: 552, y: 96,  label: 'Valeurs' },
    { slug: 'praxilink',           x: 552, y: 160, label: 'Liens' },
    { slug: 'praxizen',            x: 348, y: 160, label: 'Refuge' },
    { slug: 'praxitempo',          x: 348, y: 96,  label: 'Maître du Temps' },
    { slug: 'praxiflow',           x: 450, y: 122, label: 'Flux · Cœur' },
    { slug: 'praxiboost',          x: 450, y: 183, label: 'Éclat' },
]

const EDGES = [
    ['orientation-express', 'praximet'],
    ['praximet',  'praximum'],
    ['praximum',  'praxis360'],
    ['praxis360', 'praxiemo'],
    ['praxiemo',  'praxicare'],
    ['praxicare', 'praxiself'],
    ['praxiself', 'praxispeak'],
    ['praxispeak','orientation-express'],
    ['orientation-express', 'praxivaleurs'],
    ['praximet',   'praxivaleurs'],
    ['praximum',   'praxilink'],
    ['praxis360',  'praxilink'],
    ['praxiemo',   'praxiboost'],
    ['praxicare',  'praxiboost'],
    ['praxiself',  'praxizen'],
    ['praxispeak', 'praxitempo'],
    ['praxivaleurs','praxilink'],
    ['praxizen',    'praxiboost'],
    ['praxitempo',  'praxizen'],
    ['praxiflow', 'praxivaleurs'],
    ['praxiflow', 'praxitempo'],
    ['praxiflow', 'praxilink'],
    ['praxiflow', 'praxizen'],
    ['praxiflow', 'praxiboost'],
    ['praxiflow', 'orientation-express'],
]

const ICONS = {
    'orientation-express': '<circle cx="12" cy="12" r="9"/><polygon points="12,6.5 14,12 12,17.5 10,12" fill="currentColor" stroke="currentColor"/><path d="M12 2.5V4M12 20v1.5M2.5 12H4M20 12h1.5"/>',
    'praximet':   '<path d="M12 3.5V21M7 21h10M12 6h7l-2 2 2 2h-7M12 12H5l-2 2 2 2h7"/>',
    'praximum':   '<path d="M3 6.5l6-2 6 2 6-2v13l-6 2-6-2-6 2zM9 4.5v13M15 6.5v13"/>',
    'praxis360':  '<path d="M4 8l5 5 6-4 5 4"/><circle cx="4" cy="8" r="1.1" fill="currentColor" stroke="none"/><circle cx="9" cy="13" r="1.1" fill="currentColor" stroke="none"/><circle cx="15" cy="9" r="1.1" fill="currentColor" stroke="none"/><circle cx="20" cy="13" r="1.1" fill="currentColor" stroke="none"/>',
    'praxiemo':   '<circle cx="12" cy="12" r="9"/><path d="M12 16.5c-2.2-1.6-3.8-2.9-3.8-4.6 0-1.2 1-2.1 2.1-2.1.8 0 1.3.4 1.7 1 .4-.6.9-1 1.7-1 1.1 0 2.1.9 2.1 2.1 0 1.7-1.6 3-3.8 4.6z"/>',
    'praxicare':  '<path d="M12 3l7 3v5c0 4.5-3 7.6-7 9-4-1.4-7-4.5-7-9V6z"/>',
    'praxiself':  '<path d="M16.5 3.5l4 4-2.2 2.2-4-4zM14 6L4.5 15.5 3 19l3.5-1.5L16 8z"/>',
    'praxispeak': '<path d="M4 10.5v3l3 .8 9 4V5.7L7 9.7l-3 .8zM17 9c1.8.3 2.8 1.6 2.8 3s-1 2.7-2.8 3"/>',
    'praxiflow':  '<path d="M6 3h12M6 21h12M7 3v3c0 2 2 4 5 6 3-2 5-4 5-6V3M7 21v-3c0-2 2-4 5-6 3 2 5 4 5 6v3"/>',
    'praxitempo': '<path d="M3.5 20h17M6 20a6 6 0 0 1 12 0M12 20L9.5 9"/>',
    'praxivaleurs':'<path d="M12 4v17M7 21h10M5 7l7-1.5L19 7M5 7l-2 5a3 3 0 0 0 4 0zM19 7l-2 5a3 3 0 0 0 4 0z"/>',
    'praxizen':   '<path d="M12 5c1.6 2.6 1.6 5.4 0 8-1.6-2.6-1.6-5.4 0-8zM12 13C9.8 11.4 7 11.4 4.5 13c1.4 2.4 4 3.4 7.5 3M12 13c2.2-1.6 5-1.6 7.5 0-1.4 2.4-4 3.4-7.5 3"/>',
    'praxilink':  '<rect x="3" y="9" width="11" height="6" rx="3"/><rect x="10" y="9" width="11" height="6" rx="3"/>',
    'praxiboost': '<path d="M12 2.5l1.8 6.7 6.7 1.8-6.7 1.8L12 19.5l-1.8-6.7L3.5 11l6.7-1.8z" fill="currentColor" stroke="currentColor"/>',
}
const FALLBACK = '<path d="M12 3l7 3v5c0 4.5-3 7.6-7 9-4-1.4-7-4.5-7-9V6z"/>'

const testMap = computed(() => {
    const m = {}
    props.tests.forEach(t => { m[t.slug] = t })
    return m
})
const nodeMap = computed(() => {
    const m = {}
    NODES.forEach(n => { m[n.slug] = n })
    return m
})

function isCompleted(slug) {
    const t = testMap.value[slug]
    return !!(t && (t.completed_at || t.completed))
}
function isAvailable(slug) {
    return !!testMap.value[slug]
}

const completedCount = computed(() =>
    props.tests.filter(t => t.completed_at || t.completed).length
)
const explorationPct = computed(() => {
    if (!props.tests.length) return 0
    return Math.round(completedCount.value / props.tests.length * 100)
})
</script>

<template>
    <div class="cm-wrap">
        <!-- Header -->
        <div class="cm-header">
            <div class="cm-title">
                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <circle cx="12" cy="12" r="9"/>
                    <polygon points="12,6 14,12 12,18 10,12" fill="currentColor"/>
                </svg>
                Carte du Continent Intérieur
            </div>
            <div class="cm-right">
                <span class="cm-count">{{ completedCount }}/{{ tests.length }} épreuves</span>
                <div class="cm-bar"><div class="cm-fill" :style="{ width: explorationPct + '%' }"></div></div>
                <span class="cm-pct">{{ explorationPct }}%</span>
            </div>
        </div>

        <!-- Map -->
        <div class="cm-body">
            <svg viewBox="0 0 900 250" xmlns="http://www.w3.org/2000/svg" class="cm-svg"
                 role="img" aria-label="Carte de tes épreuves et de ta progression">

                <defs>
                    <!-- Parchemin dot-grid -->
                    <pattern id="cmgrid" x="0" y="0" width="28" height="28" patternUnits="userSpaceOnUse">
                        <circle cx="14" cy="14" r="0.7" fill="#8B6914" opacity="0.12"/>
                    </pattern>
                    <!-- Vignette parchemin -->
                    <radialGradient id="cmfade" cx="50%" cy="50%" r="55%">
                        <stop offset="55%" stop-color="transparent" stop-opacity="0"/>
                        <stop offset="100%" stop-color="#E8DFC0" stop-opacity="0.45"/>
                    </radialGradient>
                </defs>

                <!-- Fond parchemin clair -->
                <rect width="900" height="250" fill="#FAF6EB"/>
                <rect width="900" height="250" fill="url(#cmgrid)"/>

                <!-- Anneaux orbitaux -->
                <ellipse cx="450" cy="126" rx="215" ry="100" fill="none" stroke="#8B6914" stroke-width="0.6" opacity="0.14" stroke-dasharray="7 14"/>
                <ellipse cx="450" cy="126" rx="120"  ry="55"  fill="none" stroke="#8B6914" stroke-width="0.5" opacity="0.10" stroke-dasharray="5 10"/>
                <circle  cx="450" cy="126" r="40"   fill="none" stroke="#8B6914" stroke-width="0.6" opacity="0.18"/>

                <!-- Arêtes -->
                <g>
                    <line
                        v-for="([a, b], i) in EDGES"
                        :key="i"
                        :x1="nodeMap[a]?.x" :y1="nodeMap[a]?.y"
                        :x2="nodeMap[b]?.x" :y2="nodeMap[b]?.y"
                        :stroke="(isCompleted(a) && isCompleted(b)) ? '#C4860A' : '#8B6914'"
                        :stroke-width="(isCompleted(a) && isCompleted(b)) ? '1.3' : '0.8'"
                        :opacity="(isCompleted(a) && isCompleted(b)) ? '0.5' : '0.2'"
                        stroke-dasharray="4 6"
                    />
                </g>

                <!-- Nœuds -->
                <g v-for="node in NODES" :key="node.slug">

                    <!-- ACCOMPLI -->
                    <template v-if="isCompleted(node.slug)">
                        <circle :cx="node.x" :cy="node.y" r="25" fill="#C4860A" opacity="0.07"/>
                        <circle :cx="node.x" :cy="node.y"
                                :r="node.slug === 'praxiflow' ? 20 : 17"
                                fill="#FAF6EB"
                                stroke="#C4860A"
                                :stroke-width="node.slug === 'praxiflow' ? 2 : 1.8"/>
                        <svg :x="node.x-8" :y="node.y-8" width="16" height="16"
                             viewBox="0 0 24 24" fill="none" stroke="#C4860A"
                             stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"
                             overflow="visible">
                            <g v-html="ICONS[node.slug] || FALLBACK"></g>
                        </svg>
                        <circle :cx="node.x+12" :cy="node.y-12" r="5" fill="#10B981" stroke="#FAF6EB" stroke-width="1.2"/>
                        <text :x="node.x" :y="node.y+29" text-anchor="middle"
                              font-size="8" fill="#6B4C1A" opacity="0.9"
                              font-family="'Space Mono',monospace" letter-spacing="0.02em">
                            {{ node.label }}
                        </text>
                    </template>

                    <!-- DISPONIBLE non accompli -->
                    <template v-else-if="isAvailable(node.slug)">
                        <circle :cx="node.x" :cy="node.y"
                                :r="node.slug === 'praxiflow' ? 20 : 17"
                                fill="#F2EACF" stroke="#8B6914" stroke-width="1.1" opacity="0.8"/>
                        <svg :x="node.x-8" :y="node.y-8" width="16" height="16"
                             viewBox="0 0 24 24" fill="none" stroke="#8B6914"
                             stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"
                             opacity="0.55" overflow="visible">
                            <g v-html="ICONS[node.slug] || FALLBACK"></g>
                        </svg>
                        <text :x="node.x" :y="node.y+28" text-anchor="middle"
                              font-size="7.5" fill="#8B6914" opacity="0.55"
                              font-family="'Space Mono',monospace" letter-spacing="0.02em">
                            {{ node.label }}
                        </text>
                    </template>

                    <!-- INCONNU / plugin désactivé -->
                    <template v-else>
                        <circle :cx="node.x" :cy="node.y" r="17"
                                fill="#EDE5CB" stroke="#8B6914"
                                stroke-width="0.7" stroke-dasharray="4 5"
                                opacity="0.3"/>
                        <text :x="node.x" :y="node.y+4" text-anchor="middle"
                              font-size="10" fill="#8B6914" opacity="0.25"
                              font-family="'Space Mono',monospace">?</text>
                        <text :x="node.x" :y="node.y+28" text-anchor="middle"
                              font-size="7" fill="#8B6914" opacity="0.2"
                              font-family="'Space Mono',monospace" letter-spacing="0.02em">
                            {{ node.label }}
                        </text>
                    </template>
                </g>

                <!-- Vignette parchemin sur les bords -->
                <rect width="900" height="250" fill="url(#cmfade)" pointer-events="none"/>

                <!-- Rose des vents -->
                <g transform="translate(862,28)" opacity="0.25">
                    <circle cx="0" cy="0" r="16" fill="none" stroke="#8B6914" stroke-width="0.8"/>
                    <path d="M0,-11 L2,4 L0,2 L-2,4Z" fill="#C4860A" opacity="0.8"/>
                    <path d="M0,11 L2,-4 L0,-2 L-2,-4Z" fill="#8B6914" opacity="0.4"/>
                    <text x="0" y="-17" text-anchor="middle" font-size="5.5" fill="#8B6914" font-family="'Space Mono',monospace">N</text>
                </g>

                <!-- Watermark -->
                <text x="38" y="244" font-size="7" fill="#8B6914" opacity="0.1"
                      font-family="'Space Mono',monospace" letter-spacing="0.14em">
                    TERRA INCOGNITA · CARTOGRAPHIA INTERIOR
                </text>

            </svg>
        </div>

        <!-- Légende -->
        <div class="cm-legend">
            <div class="cm-leg-item"><span class="cm-dot cm-dot-gold"></span>Accomplie</div>
            <div class="cm-leg-item"><span class="cm-dot cm-dot-dim"></span>À explorer</div>
        </div>
    </div>
</template>

<style scoped>
.cm-wrap {
    border-radius: 12px;
    border: 1px solid rgba(139, 105, 20, 0.25);
    overflow: hidden;
    background: #FAF6EB;
    margin-bottom: 1.75rem;
    max-width: 860px;
}
.cm-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0.5rem 1rem;
    border-bottom: 1px solid rgba(139, 105, 20, 0.15);
    background: rgba(139, 105, 20, 0.04);
}
.cm-title {
    display: flex;
    align-items: center;
    gap: 0.4rem;
    font-size: 9.5px;
    font-weight: 600;
    letter-spacing: 0.12em;
    text-transform: uppercase;
    color: #6B4C1A;
    font-family: 'Space Mono', monospace;
}
.cm-right { display: flex; align-items: center; gap: 0.5rem; }
.cm-count {
    font-size: 9px;
    font-family: 'Space Mono', monospace;
    color: rgba(107, 76, 26, 0.5);
    white-space: nowrap;
}
.cm-bar {
    width: 80px;
    height: 3px;
    border-radius: 99px;
    background: rgba(139, 105, 20, 0.15);
    overflow: hidden;
}
.cm-fill {
    height: 100%;
    border-radius: 99px;
    background: linear-gradient(90deg, #8B6914, #C4860A);
    transition: width 0.6s ease;
}
.cm-pct {
    font-size: 9px;
    font-family: 'Space Mono', monospace;
    color: rgba(107, 76, 26, 0.55);
    white-space: nowrap;
}
.cm-body { padding: 0; }
.cm-svg  { width: 100%; height: auto; display: block; }
.cm-legend {
    display: flex;
    gap: 1rem;
    justify-content: flex-end;
    padding: 0.35rem 1rem;
    border-top: 1px solid rgba(139, 105, 20, 0.1);
}
.cm-leg-item {
    display: flex;
    align-items: center;
    gap: 5px;
    font-size: 9px;
    color: rgba(107, 76, 26, 0.45);
    font-family: 'Space Mono', monospace;
}
.cm-dot { display: inline-block; width: 6px; height: 6px; border-radius: 50%; }
.cm-dot-gold  { background: #C4860A; }
.cm-dot-dim   { background: transparent; border: 1px solid #8B6914; opacity: 0.5; }
</style>
