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
    { slug: 'praxiflow',           x: 450, y: 122, label: 'Flux · Cœur', center: true },
    { slug: 'praxiboost',          x: 450, y: 183, label: 'Éclat' },
]

const EDGES = [
    ['orientation-express','praximet'],
    ['praximet','praximum'],
    ['praximum','praxis360'],
    ['praxis360','praxiemo'],
    ['praxiemo','praxicare'],
    ['praxicare','praxiself'],
    ['praxiself','praxispeak'],
    ['praxispeak','orientation-express'],
    ['orientation-express','praxivaleurs'],
    ['praximet','praxivaleurs'],
    ['praximum','praxilink'],
    ['praxis360','praxilink'],
    ['praxiemo','praxiboost'],
    ['praxicare','praxiboost'],
    ['praxiself','praxizen'],
    ['praxispeak','praxitempo'],
    ['praxivaleurs','praxilink'],
    ['praxizen','praxiboost'],
    ['praxitempo','praxizen'],
    ['praxiflow','praxivaleurs'],
    ['praxiflow','praxitempo'],
    ['praxiflow','praxilink'],
    ['praxiflow','praxizen'],
    ['praxiflow','praxiboost'],
    ['praxiflow','orientation-express'],
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

// ── Computed maps ─────────────────────────────────────────────────────────────
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

const isCompleted = (slug) => {
    const t = testMap.value[slug]
    return !!(t && (t.completed_at || t.completed))
}
const isActive = (slug) => !!testMap.value[slug]

// Nœuds avec leurs propriétés de rendu précalculées
const displayNodes = computed(() =>
    NODES
        .filter(n => isActive(n.slug))
        .map(n => ({
            ...n,
            done:   isCompleted(n.slug),
            r:      n.center ? 20 : 17,
            rPulse: n.center ? 27 : 24,
        }))
)

// Arêtes entre nœuds actifs uniquement
const displayEdges = computed(() =>
    EDGES
        .filter(([a, b]) => isActive(a) && isActive(b))
        .map(([a, b]) => ({
            x1: nodeMap.value[a].x, y1: nodeMap.value[a].y,
            x2: nodeMap.value[b].x, y2: nodeMap.value[b].y,
            done: isCompleted(a) && isCompleted(b),
        }))
)

// Progression
const completedCount = computed(() =>
    props.tests.filter(t => t.completed_at || t.completed).length
)
const explorationPct = computed(() =>
    props.tests.length ? Math.round(completedCount.value / props.tests.length * 100) : 0
)

// Tooltip
const hovered = ref(null)
</script>

<template>
    <div class="cm-wrap">

        <!-- ── Header ── -->
        <div class="cm-header">
            <div class="cm-title">
                <svg width="10" height="10" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <circle cx="12" cy="12" r="9"/>
                    <polygon points="12,6 14,12 12,18 10,12" fill="currentColor"/>
                </svg>
                Carte du Continent Intérieur
            </div>
            <div class="cm-right">
                <span class="cm-count">{{ completedCount }}/{{ tests.length }} épreuves</span>
                <div class="cm-bar">
                    <div class="cm-fill" :style="{ width: explorationPct + '%' }"></div>
                </div>
                <span class="cm-pct">{{ explorationPct }}%</span>
            </div>
        </div>

        <!-- ── SVG Map ── -->
        <div class="cm-body">
            <svg viewBox="0 0 900 250" xmlns="http://www.w3.org/2000/svg"
                 class="cm-svg" role="img" aria-label="Carte de tes épreuves">

                <defs>
                    <!-- Grille de points parchemin -->
                    <pattern id="cmpx" x="0" y="0" width="30" height="30"
                             patternUnits="userSpaceOnUse">
                        <circle cx="15" cy="15" r="0.65" fill="#8B6914" opacity="0.09"/>
                    </pattern>
                    <!-- Lignes topographiques subtiles -->
                    <pattern id="cmtopo" x="0" y="0" width="90" height="60"
                             patternUnits="userSpaceOnUse">
                        <ellipse cx="45" cy="30" rx="40" ry="24"
                                 fill="none" stroke="#8B6914" stroke-width="0.35" opacity="0.055"/>
                        <ellipse cx="45" cy="30" rx="25" ry="14"
                                 fill="none" stroke="#8B6914" stroke-width="0.35" opacity="0.055"/>
                    </pattern>
                    <!-- Filtre glow pour nœuds accomplis -->
                    <filter id="cmglow" x="-60%" y="-60%" width="220%" height="220%">
                        <feGaussianBlur in="SourceGraphic" stdDeviation="2.5" result="blur"/>
                        <feMerge>
                            <feMergeNode in="blur"/>
                            <feMergeNode in="SourceGraphic"/>
                        </feMerge>
                    </filter>
                    <!-- Vignette douce sur les bords uniquement -->
                    <radialGradient id="cmvign" cx="50%" cy="50%" r="50%">
                        <stop offset="62%" stop-color="transparent" stop-opacity="0"/>
                        <stop offset="100%" stop-color="#DDD4A8" stop-opacity="0.4"/>
                    </radialGradient>
                </defs>

                <!-- Fond parchemin -->
                <rect width="900" height="250" fill="#FAF6EB"/>
                <rect width="900" height="250" fill="url(#cmtopo)"/>
                <rect width="900" height="250" fill="url(#cmpx)"/>

                <!-- Anneaux orbitaux -->
                <ellipse cx="450" cy="126" rx="222" ry="103"
                         fill="none" stroke="#8B6914" stroke-width="0.5"
                         opacity="0.11" stroke-dasharray="9 18"/>
                <ellipse cx="450" cy="126" rx="123" ry="58"
                         fill="none" stroke="#8B6914" stroke-width="0.45"
                         opacity="0.09" stroke-dasharray="6 12"/>
                <circle  cx="450" cy="126" r="43"
                         fill="none" stroke="#8B6914" stroke-width="0.5" opacity="0.16"/>

                <!-- ── Arêtes ── -->
                <g>
                    <line
                        v-for="(edge, i) in displayEdges"
                        :key="i"
                        :x1="edge.x1" :y1="edge.y1"
                        :x2="edge.x2" :y2="edge.y2"
                        :stroke="edge.done ? '#C4860A' : '#8B6914'"
                        :stroke-width="edge.done ? 1.3 : 0.75"
                        :opacity="edge.done ? 0.42 : 0.18"
                        stroke-dasharray="5 7"
                        :class="edge.done ? 'edge-flow' : ''"
                    />
                </g>

                <!-- ── Nœuds ── -->
                <g
                    v-for="node in displayNodes"
                    :key="node.slug"
                    class="cm-node"
                    @mouseenter="hovered = node"
                    @mouseleave="hovered = null"
                >
                    <!-- Halo accompli (statique) -->
                    <circle
                        v-if="node.done"
                        :cx="node.x" :cy="node.y"
                        :r="node.r + 9"
                        fill="#C4860A" opacity="0.07"
                    />

                    <!-- Anneau pulsé pour les nœuds à explorer -->
                    <circle
                        v-if="!node.done"
                        :cx="node.x" :cy="node.y"
                        :r="node.rPulse"
                        fill="none" stroke="#8B6914" stroke-width="0.9"
                        class="node-pulse"
                    />

                    <!-- Cercle principal -->
                    <circle
                        :cx="node.x" :cy="node.y"
                        :r="node.r"
                        :fill="node.done ? '#FAF6EB' : '#F4EDD6'"
                        :stroke="node.done ? '#C4860A' : '#8B6914'"
                        :stroke-width="node.done ? (node.center ? 2.2 : 1.9) : 1.1"
                        :opacity="node.done ? 1 : 0.72"
                        :filter="node.done ? 'url(#cmglow)' : ''"
                    />

                    <!-- Icône SVG imbriquée -->
                    <svg
                        :x="node.x - 8" :y="node.y - 8"
                        width="16" height="16"
                        viewBox="0 0 24 24"
                        fill="none"
                        :stroke="node.done ? '#C4860A' : '#8B6914'"
                        stroke-width="1.6"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        :opacity="node.done ? 1 : 0.45"
                        overflow="visible"
                    >
                        <g v-html="ICONS[node.slug] || FALLBACK"/>
                    </svg>

                    <!-- Badge vert accompli -->
                    <circle
                        v-if="node.done"
                        :cx="node.x + node.r - 4"
                        :cy="node.y - node.r + 4"
                        r="5"
                        fill="#10B981"
                        stroke="#FAF6EB"
                        stroke-width="1.2"
                    />

                    <!-- Label -->
                    <text
                        :x="node.x"
                        :y="node.y + node.r + 13"
                        text-anchor="middle"
                        font-size="7.5"
                        :fill="node.done ? '#6B4C1A' : '#8B6914'"
                        :opacity="node.done ? 0.88 : 0.48"
                        font-family="'Space Mono',monospace"
                        letter-spacing="0.025em"
                    >{{ node.label }}</text>
                </g>

                <!-- ── Tooltip au survol ── -->
                <g v-if="hovered" style="pointer-events:none">
                    <rect
                        :x="hovered.x - 46"
                        :y="hovered.y - hovered.r - 26"
                        width="92" height="18" rx="4"
                        fill="#3D2B0A" opacity="0.85"
                    />
                    <text
                        :x="hovered.x"
                        :y="hovered.y - hovered.r - 12"
                        text-anchor="middle"
                        font-size="8"
                        fill="#FAF6EB"
                        font-family="'Space Mono',monospace"
                    >{{ hovered.label }}{{ hovered.done ? ' ✓' : '' }}</text>
                </g>

                <!-- Vignette bord -->
                <rect width="900" height="250" fill="url(#cmvign)"
                      pointer-events="none"/>

                <!-- Rose des vents -->
                <g transform="translate(862,28)" opacity="0.2">
                    <circle cx="0" cy="0" r="16" fill="none"
                            stroke="#8B6914" stroke-width="0.8"/>
                    <path d="M0,-11 L2,4 L0,2 L-2,4Z" fill="#C4860A" opacity="0.85"/>
                    <path d="M0,11 L2,-4 L0,-2 L-2,-4Z"
                          fill="#8B6914" opacity="0.4"/>
                    <text x="0" y="-17" text-anchor="middle"
                          font-size="5.5" fill="#8B6914"
                          font-family="'Space Mono',monospace">N</text>
                </g>

                <!-- Watermark -->
                <text x="38" y="244" font-size="6.5" fill="#8B6914" opacity="0.08"
                      font-family="'Space Mono',monospace"
                      letter-spacing="0.15em">TERRA INCOGNITA · CARTOGRAPHIA INTERIOR</text>

            </svg>
        </div>

        <!-- ── Légende ── -->
        <div class="cm-legend">
            <div class="cm-leg-item">
                <span class="cm-dot cm-dot-done"></span>Accomplie
            </div>
            <div class="cm-leg-item">
                <span class="cm-dot cm-dot-todo"></span>À explorer
            </div>
        </div>

    </div>
</template>

<style scoped>
/* ── Conteneur ─────────────────────────────────────────────── */
.cm-wrap {
    border-radius: 12px;
    border: 1px solid rgba(139, 105, 20, 0.2);
    overflow: hidden;
    background: #FAF6EB;
    margin-bottom: 1.75rem;
    max-width: 860px;
}

/* ── Header ────────────────────────────────────────────────── */
.cm-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0.48rem 1rem;
    border-bottom: 1px solid rgba(139, 105, 20, 0.12);
    background: rgba(139, 105, 20, 0.03);
}
.cm-title {
    display: flex;
    align-items: center;
    gap: 0.4rem;
    font-size: 9px;
    font-weight: 700;
    letter-spacing: 0.13em;
    text-transform: uppercase;
    color: #6B4C1A;
    font-family: 'Space Mono', monospace;
}
.cm-right   { display: flex; align-items: center; gap: 0.5rem; }
.cm-count   {
    font-size: 8.5px;
    font-family: 'Space Mono', monospace;
    color: rgba(107, 76, 26, 0.45);
    white-space: nowrap;
}
.cm-bar {
    width: 72px;
    height: 3px;
    border-radius: 99px;
    background: rgba(139, 105, 20, 0.14);
    overflow: hidden;
}
.cm-fill {
    height: 100%;
    border-radius: 99px;
    background: linear-gradient(90deg, #8B6914 0%, #C4860A 100%);
    transition: width 0.7s cubic-bezier(0.4, 0, 0.2, 1);
}
.cm-pct {
    font-size: 8.5px;
    font-family: 'Space Mono', monospace;
    color: rgba(107, 76, 26, 0.5);
    white-space: nowrap;
}

/* ── Map body ──────────────────────────────────────────────── */
.cm-body { padding: 0; }
.cm-svg  { width: 100%; height: auto; display: block; }

/* ── Nœud interactif ───────────────────────────────────────── */
.cm-node {
    cursor: pointer;
    transition: opacity 0.15s;
}
.cm-node:hover { opacity: 0.85; }

/* ── Arête animée (nœuds accomplis) ───────────────────────── */
@keyframes edge-march {
    to { stroke-dashoffset: -48; }
}
.edge-flow {
    animation: edge-march 3s linear infinite;
}

/* ── Halo pulsé (nœuds à explorer) ────────────────────────── */
@keyframes pulse-ring {
    0%   { opacity: 0.4; transform: scale(0.88); }
    60%  { opacity: 0.0; transform: scale(1.22); }
    100% { opacity: 0.0; transform: scale(1.22); }
}
.node-pulse {
    transform-box: fill-box;
    transform-origin: center;
    animation: pulse-ring 2.6s ease-out infinite;
}

/* ── Légende ───────────────────────────────────────────────── */
.cm-legend {
    display: flex;
    gap: 1rem;
    justify-content: flex-end;
    padding: 0.32rem 1rem;
    border-top: 1px solid rgba(139, 105, 20, 0.09);
}
.cm-leg-item {
    display: flex;
    align-items: center;
    gap: 5px;
    font-size: 8.5px;
    color: rgba(107, 76, 26, 0.4);
    font-family: 'Space Mono', monospace;
}
.cm-dot {
    display: inline-block;
    width: 6px; height: 6px;
    border-radius: 50%;
}
.cm-dot-done { background: #C4860A; }
.cm-dot-todo { background: transparent; border: 1px solid #8B6914; opacity: 0.45; }
</style>
