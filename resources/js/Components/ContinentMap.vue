<script setup>
import { computed } from 'vue'

const props = defineProps({
    tests: { type: Array, default: () => [] }
})

const VIEW_W = 900
const VIEW_H = 410

// Node positions & labels on the constellation map
const NODES = [
    // Outer ring — clockwise from top
    { slug: 'orientation-express', x: 450, y: 55,  label: 'Boussole' },
    { slug: 'praximet',            x: 598, y: 100, label: 'La Voie' },
    { slug: 'praximum',            x: 660, y: 200, label: 'Grande Carte' },
    { slug: 'praxis360',           x: 598, y: 300, label: 'Constellation' },
    { slug: 'praxiemo',            x: 450, y: 350, label: 'Émotions' },
    { slug: 'praxicare',           x: 302, y: 300, label: 'Sentinelle' },
    { slug: 'praxiself',           x: 240, y: 200, label: 'Forge du Soi' },
    { slug: 'praxispeak',          x: 302, y: 100, label: 'Voix du Héros' },
    // Inner ring
    { slug: 'praxivaleurs',        x: 550, y: 148, label: 'Valeurs' },
    { slug: 'praxilink',           x: 550, y: 258, label: 'Liens' },
    { slug: 'praxizen',            x: 350, y: 258, label: 'Refuge' },
    { slug: 'praxitempo',          x: 350, y: 148, label: 'Maître du Temps' },
    // Center
    { slug: 'praxiflow',           x: 450, y: 185, label: 'Flux · Cœur' },
    { slug: 'praxiboost',          x: 450, y: 275, label: 'Éclat' },
    // Extra (praxifocus etc.) handled via fallback
]

// Connection topology
const EDGES = [
    // Outer ring
    ['orientation-express', 'praximet'],
    ['praximet',  'praximum'],
    ['praximum',  'praxis360'],
    ['praxis360', 'praxiemo'],
    ['praxiemo',  'praxicare'],
    ['praxicare', 'praxiself'],
    ['praxiself', 'praxispeak'],
    ['praxispeak','orientation-express'],
    // Outer → inner spokes
    ['orientation-express', 'praxivaleurs'],
    ['praximet',   'praxivaleurs'],
    ['praximum',   'praxilink'],
    ['praxis360',  'praxilink'],
    ['praxiemo',   'praxiboost'],
    ['praxicare',  'praxiboost'],
    ['praxiself',  'praxizen'],
    ['praxispeak', 'praxitempo'],
    // Inner ring
    ['praxivaleurs','praxilink'],
    ['praxizen',    'praxiboost'],
    ['praxitempo',  'praxizen'],
    // Center hub
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
    'praxicare':  '<path d="M12 3l7 3v5c0 4.5-3 7.6-7 9-4-1.4-7-4.5-7-9V6z"/><path d="M12 14.5c-1.7-1.2-3-2.2-3-3.5 0-1 .8-1.6 1.6-1.6.6 0 1.1.3 1.4.8.3-.5.8-.8 1.4-.8.8 0 1.6.6 1.6 1.6 0 1.3-1.3 2.3-3 3.5z"/>',
    'praxiself':  '<path d="M16.5 3.5l4 4-2.2 2.2-4-4zM14 6L4.5 15.5 3 19l3.5-1.5L16 8z"/>',
    'praxispeak': '<path d="M4 10.5v3l3 .8 9 4V5.7L7 9.7l-3 .8zM17 9c1.8.3 2.8 1.6 2.8 3s-1 2.7-2.8 3"/>',
    'praxiflow':  '<path d="M6 3h12M6 21h12M7 3v3c0 2 2 4 5 6 3-2 5-4 5-6V3M7 21v-3c0-2 2-4 5-6 3 2 5 4 5 6v3"/>',
    'praxitempo': '<path d="M3.5 20h17M6 20a6 6 0 0 1 12 0M12 20L9.5 9M7.5 16l-1.2-.6M16.5 16l1.2-.6"/>',
    'praxivaleurs':'<path d="M12 4v17M7 21h10M5 7l7-1.5L19 7M5 7l-2 5a3 3 0 0 0 4 0zM19 7l-2 5a3 3 0 0 0 4 0z"/>',
    'praxizen':   '<path d="M12 5c1.6 2.6 1.6 5.4 0 8-1.6-2.6-1.6-5.4 0-8zM12 13C9.8 11.4 7 11.4 4.5 13c1.4 2.4 4 3.4 7.5 3M12 13c2.2-1.6 5-1.6 7.5 0-1.4 2.4-4 3.4-7.5 3"/>',
    'praxilink':  '<rect x="3" y="9" width="11" height="6" rx="3"/><rect x="10" y="9" width="11" height="6" rx="3"/>',
    'praxiboost': '<path d="M12 2.5l1.8 6.7 6.7 1.8-6.7 1.8L12 19.5l-1.8-6.7L3.5 11l6.7-1.8z" fill="currentColor" stroke="currentColor"/>',
}
const FALLBACK_ICON = '<path d="M12 3l7 3v5c0 4.5-3 7.6-7 9-4-1.4-7-4.5-7-9V6z"/>'

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
function exists(slug) {
    return !!testMap.value[slug]
}
function edgeDone(a, b) {
    return isCompleted(a) && isCompleted(b)
}

const explorationPct = computed(() => {
    if (!props.tests.length) return 0
    const done = props.tests.filter(t => t.completed_at || t.completed).length
    return Math.round(done / props.tests.length * 100)
})
</script>

<template>
    <div class="cm-wrapper">

        <!-- Header bar -->
        <div class="cm-header">
            <div class="cm-title">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <circle cx="12" cy="12" r="9"/>
                    <polygon points="12,6 14,12 12,18 10,12" fill="currentColor" stroke="currentColor"/>
                    <path d="M12 3v1.5M12 19.5V21M3 12h1.5M19.5 12H21"/>
                </svg>
                Carte du Continent Intérieur
            </div>
            <div class="cm-progress-wrap">
                <div class="cm-progress-bar">
                    <div class="cm-progress-fill" :style="{ width: explorationPct + '%' }"></div>
                </div>
                <span class="cm-progress-label">{{ explorationPct }}% exploré</span>
            </div>
        </div>

        <!-- SVG map -->
        <div class="cm-svg-wrap">
            <svg
                :viewBox="`0 0 ${VIEW_W} ${VIEW_H}`"
                xmlns="http://www.w3.org/2000/svg"
                class="cm-svg"
                role="img"
                aria-label="Carte du continent intérieur — tes zones explorées et ta terra incognita"
            >
                <defs>
                    <pattern id="cm-grid" x="0" y="0" width="30" height="30" patternUnits="userSpaceOnUse">
                        <circle cx="15" cy="15" r="0.6" fill="#D4A843" opacity="0.06"/>
                    </pattern>
                    <radialGradient id="cm-fog" cx="50%" cy="50%" r="50%">
                        <stop offset="55%" stop-color="#0d0a04" stop-opacity="0"/>
                        <stop offset="100%" stop-color="#0d0a04" stop-opacity="0.65"/>
                    </radialGradient>
                </defs>

                <!-- Grid dot texture -->
                <rect width="900" height="410" fill="url(#cm-grid)"/>

                <!-- Decorative rings -->
                <circle cx="450" cy="205" r="188" fill="none" stroke="#D4A843" stroke-width="0.4" opacity="0.08" stroke-dasharray="5 10"/>
                <circle cx="450" cy="205" r="110" fill="none" stroke="#D4A843" stroke-width="0.4" opacity="0.06" stroke-dasharray="3 8"/>
                <circle cx="450" cy="205" r="38" fill="none" stroke="#D4A843" stroke-width="0.5" opacity="0.1"/>

                <!-- Edges -->
                <g>
                    <line
                        v-for="([a, b], i) in EDGES"
                        :key="i"
                        :x1="nodeMap[a]?.x" :y1="nodeMap[a]?.y"
                        :x2="nodeMap[b]?.x" :y2="nodeMap[b]?.y"
                        :stroke="edgeDone(a, b) ? '#D4A843' : 'rgba(240,232,212,0.1)'"
                        :stroke-width="edgeDone(a, b) ? '1.2' : '0.7'"
                        :opacity="edgeDone(a, b) ? '0.4' : '1'"
                        stroke-dasharray="5 7"
                    />
                </g>

                <!-- Nodes -->
                <g v-for="node in NODES" :key="node.slug">
                    <template v-if="exists(node.slug)">
                        <!-- Glow halo for completed -->
                        <circle
                            v-if="isCompleted(node.slug)"
                            :cx="node.x" :cy="node.y" r="28"
                            fill="#D4A843" opacity="0.09"
                        />
                        <!-- Circle bg -->
                        <circle
                            :cx="node.x" :cy="node.y"
                            :r="node.slug === 'praxiflow' ? 22 : 20"
                            :fill="isCompleted(node.slug) ? '#1a0e00' : '#141008'"
                            :stroke="isCompleted(node.slug) ? '#D4A843' : 'rgba(240,232,212,0.18)'"
                            :stroke-width="isCompleted(node.slug) ? (node.slug === 'praxiflow' ? 2 : 1.5) : 0.7"
                            :opacity="isCompleted(node.slug) ? 1 : 0.5"
                        />
                        <!-- Icon — nested svg viewport so v-html works -->
                        <svg
                            :x="node.x - 9" :y="node.y - 9"
                            width="18" height="18"
                            viewBox="0 0 24 24"
                            fill="none"
                            :stroke="isCompleted(node.slug) ? '#D4A843' : 'rgba(240,232,212,0.25)'"
                            stroke-width="1.5"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            :opacity="isCompleted(node.slug) ? 1 : 0.5"
                            overflow="visible"
                        >
                            <g v-html="ICONS[node.slug] || FALLBACK_ICON"></g>
                        </svg>
                        <!-- Completion badge -->
                        <circle
                            v-if="isCompleted(node.slug)"
                            :cx="node.x + (node.slug === 'praxiflow' ? 16 : 14)"
                            :cy="node.y - (node.slug === 'praxiflow' ? 16 : 14)"
                            r="4.5"
                            fill="#10B981" stroke="#0d0a04" stroke-width="1.2"
                        />
                        <!-- Label -->
                        <text
                            :x="node.x"
                            :y="node.y + (node.slug === 'praxiflow' ? 36 : 33)"
                            text-anchor="middle"
                            :font-size="isCompleted(node.slug) ? 8.5 : 7.5"
                            :fill="isCompleted(node.slug) ? '#D4A843' : 'rgba(240,232,212,0.2)'"
                            :opacity="isCompleted(node.slug) ? 0.9 : 1"
                            font-family="'Space Mono', monospace"
                            letter-spacing="0.02em"
                        >{{ node.label }}</text>
                    </template>

                    <!-- Unknown zone (plugin not active) -->
                    <template v-else>
                        <circle
                            :cx="node.x" :cy="node.y" r="16"
                            fill="none"
                            stroke="rgba(240,232,212,0.08)"
                            stroke-width="0.5"
                            stroke-dasharray="3 6"
                        />
                    </template>
                </g>

                <!-- Fog of war vignette overlay -->
                <rect width="900" height="410" fill="url(#cm-fog)" pointer-events="none"/>

                <!-- Compass rose — top-right -->
                <g transform="translate(857, 36)" opacity="0.28">
                    <circle cx="0" cy="0" r="18" fill="none" stroke="#D4A843" stroke-width="0.8"/>
                    <circle cx="0" cy="0" r="3" fill="none" stroke="#D4A843" stroke-width="0.5"/>
                    <path d="M0,-13 L2.5,5 L0,2 L-2.5,5Z" fill="#D4A843" opacity="0.9"/>
                    <path d="M0,13 L2.5,-5 L0,-2 L-2.5,-5Z" fill="#D4A843" opacity="0.3"/>
                    <text x="0" y="-20" text-anchor="middle" font-size="6" fill="#D4A843" font-family="'Space Mono',monospace">N</text>
                </g>

                <!-- Watermark -->
                <text x="45" y="398" font-size="7.5" fill="#D4A843" opacity="0.1" font-family="'Space Mono',monospace" letter-spacing="0.14em">TERRA INCOGNITA · CARTOGRAPHIA INTERIOR</text>

            </svg>
        </div>

        <!-- Legend -->
        <div class="cm-legend">
            <div class="cm-legend-item">
                <span class="cm-dot cm-dot-gold"></span>
                Zone explorée
            </div>
            <div class="cm-legend-item">
                <span class="cm-dot cm-dot-gray"></span>
                Terra incognita
            </div>
            <div class="cm-legend-item">
                <span class="cm-dot cm-dot-green"></span>
                Épreuve accomplie
            </div>
        </div>

    </div>
</template>

<style scoped>
.cm-wrapper {
    border-radius: 16px;
    border: 1px solid rgba(212, 168, 67, 0.18);
    overflow: hidden;
    background: #0d0a04;
    margin-bottom: 2rem;
}
.cm-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0.65rem 1.25rem;
    border-bottom: 1px solid rgba(212, 168, 67, 0.1);
    background: rgba(212, 168, 67, 0.03);
}
.cm-title {
    display: flex;
    align-items: center;
    gap: 0.45rem;
    font-size: 10px;
    font-weight: 600;
    letter-spacing: 0.12em;
    text-transform: uppercase;
    color: #D4A843;
    font-family: 'Space Mono', monospace;
}
.cm-progress-wrap {
    display: flex;
    align-items: center;
    gap: 0.65rem;
}
.cm-progress-bar {
    width: 110px;
    height: 3px;
    border-radius: 99px;
    background: rgba(255, 255, 255, 0.07);
    overflow: hidden;
}
.cm-progress-fill {
    height: 100%;
    border-radius: 99px;
    background: linear-gradient(90deg, #A67520, #D4A843);
    transition: width 0.6s ease;
}
.cm-progress-label {
    font-size: 10px;
    font-family: 'Space Mono', monospace;
    color: rgba(240, 232, 212, 0.4);
    white-space: nowrap;
}
.cm-svg-wrap { padding: 0.5rem 0.5rem 0; }
.cm-svg { width: 100%; height: auto; display: block; }
.cm-legend {
    display: flex;
    gap: 1.25rem;
    justify-content: flex-end;
    padding: 0.55rem 1.25rem;
    border-top: 1px solid rgba(212, 168, 67, 0.07);
}
.cm-legend-item {
    display: flex;
    align-items: center;
    gap: 5px;
    font-size: 9.5px;
    color: rgba(240, 232, 212, 0.35);
    font-family: 'Space Mono', monospace;
}
.cm-dot { display: inline-block; width: 7px; height: 7px; border-radius: 50%; }
.cm-dot-gold  { background: #D4A843; }
.cm-dot-gray  { background: rgba(240,232,212,0.12); border: 1px solid rgba(240,232,212,0.2); }
.cm-dot-green { background: #10B981; }
</style>
