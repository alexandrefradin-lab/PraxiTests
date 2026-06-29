<script setup>
import { computed, ref } from 'vue'

const props = defineProps({
    tests: { type: Array, default: () => [] }
})

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
    'praximet':    '<path d="M12 3.5V21M7 21h10M12 6h7l-2 2 2 2h-7M12 12H5l-2 2 2 2h7"/>',
    'praximum':    '<path d="M3 6.5l6-2 6 2 6-2v13l-6 2-6-2-6 2zM9 4.5v13M15 6.5v13"/>',
    'praxis360':   '<path d="M4 8l5 5 6-4 5 4"/><circle cx="4" cy="8" r="1.1" fill="currentColor" stroke="none"/><circle cx="9" cy="13" r="1.1" fill="currentColor" stroke="none"/><circle cx="15" cy="9" r="1.1" fill="currentColor" stroke="none"/><circle cx="20" cy="13" r="1.1" fill="currentColor" stroke="none"/>',
    'praxiemo':    '<circle cx="12" cy="12" r="9"/><path d="M12 16.5c-2.2-1.6-3.8-2.9-3.8-4.6 0-1.2 1-2.1 2.1-2.1.8 0 1.3.4 1.7 1 .4-.6.9-1 1.7-1 1.1 0 2.1.9 2.1 2.1 0 1.7-1.6 3-3.8 4.6z"/>',
    'praxicare':   '<path d="M12 3l7 3v5c0 4.5-3 7.6-7 9-4-1.4-7-4.5-7-9V6z"/>',
    'praxiself':   '<path d="M16.5 3.5l4 4-2.2 2.2-4-4zM14 6L4.5 15.5 3 19l3.5-1.5L16 8z"/>',
    'praxispeak':  '<path d="M4 10.5v3l3 .8 9 4V5.7L7 9.7l-3 .8zM17 9c1.8.3 2.8 1.6 2.8 3s-1 2.7-2.8 3"/>',
    'praxiflow':   '<path d="M6 3h12M6 21h12M7 3v3c0 2 2 4 5 6 3-2 5-4 5-6V3M7 21v-3c0-2 2-4 5-6 3 2 5 4 5 6v3"/>',
    'praxitempo':  '<path d="M3.5 20h17M6 20a6 6 0 0 1 12 0M12 20L9.5 9"/>',
    'praxivaleurs':'<path d="M12 4v17M7 21h10M5 7l7-1.5L19 7M5 7l-2 5a3 3 0 0 0 4 0zM19 7l-2 5a3 3 0 0 0 4 0z"/>',
    'praxizen':    '<path d="M12 5c1.6 2.6 1.6 5.4 0 8-1.6-2.6-1.6-5.4 0-8zM12 13C9.8 11.4 7 11.4 4.5 13c1.4 2.4 4 3.4 7.5 3M12 13c2.2-1.6 5-1.6 7.5 0-1.4 2.4-4 3.4-7.5 3"/>',
    'praxilink':   '<rect x="3" y="9" width="11" height="6" rx="3"/><rect x="10" y="9" width="11" height="6" rx="3"/>',
    'praxiboost':  '<path d="M12 2.5l1.8 6.7 6.7 1.8-6.7 1.8L12 19.5l-1.8-6.7L3.5 11l6.7-1.8z" fill="currentColor" stroke="currentColor"/>',
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

// Seuls les nœuds ayant un test actif sont affichés
const visibleNodes = computed(() => NODES.filter(n => testMap.value[n.slug]))

function isCompleted(slug) {
    const t = testMap.value[slug]
    return !!(t && (t.completed_at || t.completed))
}

const completedCount = computed(() =>
    props.tests.filter(t => t.completed_at || t.completed).length
)
const explorationPct = computed(() => {
    if (!props.tests.length) return 0
    return Math.round(completedCount.value / props.tests.length * 100)
})

// Tooltip
const tooltip = ref(null) // { x, y, label, done }
function showTip(node) {
    const done = isCompleted(node.slug)
    tooltip.value = { x: node.x, y: node.y - 32, label: node.label, done }
}
function hideTip() { tooltip.value = null }
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
                    <!-- Dot-grid parchemin -->
                    <pattern id="cmgrid" x="0" y="0" width="30" height="30" patternUnits="userSpaceOnUse">
                        <circle cx="15" cy="15" r="0.7" fill="#8B6914" opacity="0.1"/>
                    </pattern>
                    <!-- Topographic faint lines -->
                    <pattern id="cmtopo" x="0" y="0" width="80" height="80" patternUnits="userSpaceOnUse">
                        <ellipse cx="40" cy="40" rx="35" ry="20" fill="none" stroke="#8B6914" stroke-width="0.4" opacity="0.06"/>
                        <ellipse cx="40" cy="40" rx="22" ry="12" fill="none" stroke="#8B6914" stroke-width="0.4" opacity="0.06"/>
                    </pattern>
                    <!-- Glow filter pour nœuds accomplis -->
                    <filter id="glow" x="-50%" y="-50%" width="200%" height="200%">
                        <feGaussianBlur stdDeviation="3" result="blur"/>
                        <feMerge><feMergeNode in="blur"/><feMergeNode in="SourceGraphic"/></feMerge>
                    </filter>
                    <!-- Vignette bord seulement -->
                    <radialGradient id="cmvign" cx="50%" cy="50%" r="50%">
                        <stop offset="70%" stop-color="transparent" stop-opacity="0"/>
                        <stop offset="100%" stop-color="#E8DFC0" stop-opacity="0.5"/>
                    </radialGradient>
                </defs>

                <!-- Fond parchemin -->
                <rect width="900" height="250" fill="#FAF6EB"/>
                <rect width="900" height="250" fill="url(#cmtopo)"/>
                <rect width="900" height="250" fill="url(#cmgrid)"/>

                <!-- Anneaux orbitaux légers -->
                <ellipse cx="450" cy="126" rx="220" ry="102" fill="none" stroke="#8B6914" stroke-width="0.5" opacity="0.12" stroke-dasharray="8 16"/>
                <ellipse cx="450" cy="126" rx="122"  ry="57"  fill="none" stroke="#8B6914" stroke-width="0.4" opacity="0.09" stroke-dasharray="6 12"/>
                <circle  cx="450" cy="126" r="42"    fill="none" stroke="#8B6914" stroke-width="0.5" opacity="0.16"/>

                <!-- Arêtes -->
                <g>
                    <template v-for="([a, b], i) in EDGES" :key="i">
                        <!-- Arête animée si les deux nœuds sont accomplis -->
                        <line v-if="isCompleted(a) && isCompleted(b)"
                              :x1="nodeMap[a]?.x" :y1="nodeMap[a]?.y"
                              :x2="nodeMap[b]?.x" :y2="nodeMap[b]?.y"
                              stroke="#C4860A" stroke-width="1.4" opacity="0.45"
                              stroke-dasharray="5 6"
                              class="edge-flow"/>
                        <!-- Arête statique sinon (si les deux nœuds sont visibles) -->
                        <line v-else-if="testMap[a] && testMap[b]"
                              :x1="nodeMap[a]?.x" :y1="nodeMap[a]?.y"
                              :x2="nodeMap[b]?.x" :y2="nodeMap[b]?.y"
                              stroke="#8B6914" stroke-width="0.8" opacity="0.2"
                              stroke-dasharray="4 7"/>
                    </template>
                </g>

                <!-- Nœuds visibles (seulement les tests actifs) -->
                <g v-for="node in visibleNodes" :key="node.slug"
                   class="cm-node"
                   @mouseenter="showTip(node)"
                   @mouseleave="hideTip">

                    <!-- ACCOMPLI -->
                    <template v-if="isCompleted(node.slug)">
                        <!-- Halo statique -->
                        <circle :cx="node.x" :cy="node.y" r="26"
                                fill="#C4860A" opacity="0.09"/>
                        <!-- Cercle -->
                        <circle :cx="node.x" :cy="node.y"
                                :r="node.slug === 'praxiflow' ? 20 : 17"
                                fill="#FAF6EB" stroke="#C4860A"
                                :stroke-width="node.slug === 'praxiflow' ? 2.2 : 1.9"
                                filter="url(#glow)"/>
                        <!-- Icône -->
                        <svg :x="node.x-8" :y="node.y-8" width="16" height="16"
                             viewBox="0 0 24 24" fill="none" stroke="#C4860A"
                             stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"
                             overflow="visible">
                            <g v-html="ICONS[node.slug] || FALLBACK"></g>
                        </svg>
                        <!-- Badge vert -->
                        <circle :cx="node.x+12" :cy="node.y-12" r="5"
                                fill="#10B981" stroke="#FAF6EB" stroke-width="1.2"/>
                        <!-- Label -->
                        <text :x="node.x" :y="node.y+30" text-anchor="middle"
                              font-size="8" fill="#6B4C1A" opacity="0.9"
                              font-family="'Space Mono',monospace" letter-spacing="0.02em">
                            {{ node.label }}
                        </text>
                    </template>

                    <!-- À EXPLORER — pulse doux -->
                    <template v-else>
                        <!-- Halo pulsé -->
                        <circle :cx="node.x" :cy="node.y"
                                :r="node.slug === 'praxiflow' ? 26 : 23"
                                fill="none" stroke="#8B6914" stroke-width="1"
                                opacity="0" class="node-pulse"/>
                        <!-- Cercle principal -->
                        <circle :cx="node.x" :cy="node.y"
                                :r="node.slug === 'praxiflow' ? 20 : 17"
                                fill="#F5EDD8" stroke="#8B6914"
                                stroke-width="1.2" opacity="0.75"/>
                        <!-- Icône -->
                        <svg :x="node.x-8" :y="node.y-8" width="16" height="16"
                             viewBox="0 0 24 24" fill="none" stroke="#8B6914"
                             stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"
                             opacity="0.5" overflow="visible">
                            <g v-html="ICONS[node.slug] || FALLBACK"></g>
                        </svg>
                        <!-- Label -->
                        <text :x="node.x" :y="node.y+30" text-anchor="middle"
                              font-size="8" fill="#8B6914" opacity="0.5"
                              font-family="'Space Mono',monospace" letter-spacing="0.02em">
                            {{ node.label }}
                        </text>
                    </template>
                </g>

                <!-- Tooltip -->
                <g v-if="tooltip">
                    <rect :x="tooltip.x - 42" :y="tooltip.y - 14"
                          width="84" height="20" rx="4"
                          fill="#3D2B0A" opacity="0.88"/>
                    <text :x="tooltip.x" :y="tooltip.y + 0" text-anchor="middle"
                          font-size="8.5" fill="#FAF6EB"
                          font-family="'Space Mono',monospace">
                        {{ tooltip.label }} {{ tooltip.done ? '✓' : '' }}
                    </text>
                </g>

                <!-- Vignette bord -->
                <rect width="900" height="250" fill="url(#cmvign)" pointer-events="none"/>

                <!-- Rose des vents -->
                <g transform="translate(862,28)" opacity="0.22">
                    <circle cx="0" cy="0" r="16" fill="none" stroke="#8B6914" stroke-width="0.8"/>
                    <path d="M0,-11 L2,4 L0,2 L-2,4Z" fill="#C4860A" opacity="0.85"/>
                    <path d="M0,11 L2,-4 L0,-2 L-2,-4Z" fill="#8B6914" opacity="0.4"/>
                    <text x="0" y="-17" text-anchor="middle" font-size="5.5" fill="#8B6914" font-family="'Space Mono',monospace">N</text>
                </g>

                <!-- Watermark -->
                <text x="38" y="244" font-size="7" fill="#8B6914" opacity="0.09"
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
    border: 1px solid rgba(139, 105, 20, 0.22);
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
    border-bottom: 1px solid rgba(139, 105, 20, 0.13);
    background: rgba(139, 105, 20, 0.03);
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

/* Nœud — curseur pointer + transition scale */
.cm-node { cursor: pointer; }
.cm-node:hover circle { opacity: 1 !important; }

/* Flux animé sur les arêtes accomplies */
@keyframes edge-flow {
    from { stroke-dashoffset: 0; }
    to   { stroke-dashoffset: -44; }
}
.edge-flow {
    animation: edge-flow 3s linear infinite;
}

/* Pulse sur les nœuds à explorer */
@keyframes node-pulse {
    0%   { opacity: 0;    transform: scale(0.8); }
    50%  { opacity: 0.35; transform: scale(1.15); }
    100% { opacity: 0;    transform: scale(1.4); }
}
.node-pulse {
    transform-box: fill-box;
    transform-origin: center;
    animation: node-pulse 2.8s ease-out infinite;
}

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
