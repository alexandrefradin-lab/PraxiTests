<script setup>
import { computed, nextTick, onMounted, ref } from 'vue'
import { Link } from '@inertiajs/vue3'
import CandidateLayout from '@/Layouts/CandidateLayout.vue'
import { Chart, RadialLinearScale, PointElement, LineElement, Filler, Tooltip } from 'chart.js'
Chart.register(RadialLinearScale, PointElement, LineElement, Filler, Tooltip)

const props = defineProps({ attempt: Object, result: Object })
const scoring = computed(() => props.result?.scoring ?? {})
const dims = computed(() => scoring.value.scores_dim ?? {})
const facettes = computed(() => scoring.value.scores_facette ?? {})
const metaDim = computed(() => scoring.value.meta_dimensions ?? {})
const metaFac = computed(() => scoring.value.meta_facettes ?? {})
const archetype = computed(() => scoring.value.archetype ?? null)

const facettesByDim = computed(() => {
    const out = {}
    Object.entries(metaFac.value).forEach(([fk, info]) => {
        if (!out[info.dim]) out[info.dim] = []
        out[info.dim].push({ key: fk, label: info.label, ...(facettes.value[fk] ?? {}) })
    })
    return out
})

const radarRef = ref(null)
const facCanvases = {}   // canvas refs par dimension (O/C/E/A/N)

const OCEAN_ORDER = ['O', 'C', 'E', 'A', 'N']

onMounted(async () => {
    // ── Radar global OCEAN (vue d'ensemble) ───────────────────────────
    if (radarRef.value) {
        const d = dims.value
        const m = metaDim.value
        const labels = OCEAN_ORDER.map(k => m[k]?.label ?? k)
        const data   = OCEAN_ORDER.map(k => d[k]?.pct ?? 0)
        const colors = OCEAN_ORDER.map(k => m[k]?.color ?? '#4F46E5')

        new Chart(radarRef.value, {
            type: 'radar',
            data: {
                labels,
                datasets: [{
                    data,
                    backgroundColor: 'rgba(79, 70, 229, 0.12)',
                    borderColor: '#4F46E5',
                    borderWidth: 2,
                    pointBackgroundColor: colors,
                    pointBorderColor: colors,
                    pointRadius: 5,
                }],
            },
            options: {
                scales: {
                    r: {
                        beginAtZero: true, min: 0, max: 100,
                        ticks: { stepSize: 25, font: { size: 10 } },
                        pointLabels: { font: { size: 12 } },
                    },
                },
                plugins: { legend: { display: false } },
            },
        })
    }

    // ── Radars par dimension — 1 par domaine (O/C/E/A/N) ──────────────
    await nextTick()
    Object.entries(facettesByDim.value).forEach(([dimKey, facs]) => {
        const canvas = facCanvases[dimKey]
        if (!canvas) return
        const color = metaDim.value[dimKey]?.color ?? '#4F46E5'
        // Hex alpha 20 % : si color est #RRGGBB on ajoute '33'
        const bg = color + (color.length === 7 ? '33' : '')

        new Chart(canvas, {
            type: 'radar',
            data: {
                labels: facs.map(f => f.label),
                datasets: [{
                    data: facs.map(f => f.pct ?? 50),
                    backgroundColor: bg,
                    borderColor: color,
                    borderWidth: 2,
                    pointBackgroundColor: color,
                    pointBorderColor: '#fff',
                    pointBorderWidth: 1.5,
                    pointRadius: 4,
                }],
            },
            options: {
                responsive: true,
                scales: {
                    r: {
                        min: 0, max: 100,
                        ticks: { display: false },
                        grid: { color: 'rgba(0,0,0,0.07)' },
                        angleLines: { color: 'rgba(0,0,0,0.07)' },
                        pointLabels: { font: { size: 10 }, padding: 6 },
                    },
                },
                plugins: { legend: { display: false } },
            },
        })
    })
})

const niveauLabel = {
    tres_bas: 'Très bas',  bas: 'Bas',  moyen: 'Moyen',
    haut: 'Élevé',         tres_haut: 'Très élevé',
}
const niveauColor = {
    tres_bas: '#dc2626', bas: '#ea580c', moyen: '#64748b',
    haut: '#0ea5e9',     tres_haut: '#16a34a',
}
</script>

<template>
    <CandidateLayout>
        <Head title="Tes résultats — PraxiMum" />

        <!-- FE-07 — guard result null -->
        <template v-if="!result">
            <div class="pt-card p-8 text-center text-slate-500">
                <p>Résultats non disponibles — veuillez réessayer dans quelques instants.</p>
                <Link :href="route('history')" class="pt-btn-ghost mt-4 inline-block text-sm">← Mon historique</Link>
            </div>
        </template>
        <template v-if="result">
        <div class="max-w-4xl mx-auto">
            <div class="text-center mb-12">
                <span class="pt-badge">PraxiMum · Big Five OCEAN</span>
                <h1 class="text-4xl font-semibold tracking-tight mt-4">Ta personnalité en 5 dimensions</h1>
                <p class="text-slate-600 mt-2 text-sm">Scores T normés (50 = moyenne). 30 facettes détaillées.</p>
            </div>

            <!-- Archétype principal -->
            <section v-if="archetype" class="pt-card overflow-hidden mb-8" :style="{ background: `linear-gradient(135deg, ${archetype.couleur1}, ${archetype.couleur2})` }">
                <div class="p-10 text-white">
                    <div class="flex items-start justify-between gap-6 flex-wrap">
                        <div class="flex items-center gap-4">
                            <span class="text-6xl">{{ archetype.emoji }}</span>
                            <div>
                                <p class="text-xs uppercase tracking-wider text-white/70">Ton archétype</p>
                                <h2 class="text-3xl font-semibold mt-1">{{ archetype.nom }}</h2>
                                <p class="text-white/90 mt-1 text-base">{{ archetype.tagline }}</p>
                            </div>
                        </div>
                        <div class="bg-white/15 backdrop-blur rounded-xl px-4 py-3 text-center">
                            <p class="text-xs uppercase tracking-wider text-white/70">Rareté</p>
                            <p class="text-2xl font-semibold mt-0.5">{{ archetype.rarete }}%</p>
                        </div>
                    </div>
                    <p class="mt-6 text-white/95 leading-relaxed text-[15px]">{{ archetype.description }}</p>
                    <div class="flex flex-wrap gap-2 mt-6">
                        <span v-for="trait in archetype.traits" :key="trait" class="bg-white/20 backdrop-blur text-white text-xs font-medium px-3 py-1 rounded-full">{{ trait }}</span>
                    </div>
                    <p v-if="archetype.distance > 0" class="text-xs text-white/60 mt-4">Prof