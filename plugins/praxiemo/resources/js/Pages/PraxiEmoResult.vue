<script setup>
import { computed, onMounted, ref } from 'vue'
import { Link } from '@inertiajs/vue3'
import CandidateLayout from '@/Layouts/CandidateLayout.vue'
import { Chart, BarController, BarElement, CategoryScale, LinearScale, Tooltip } from 'chart.js'
Chart.register(BarController, BarElement, CategoryScale, LinearScale, Tooltip)

const props = defineProps({ attempt: Object, result: Object })
const scoring = computed(() => props.result?.scoring ?? {})
const dims = computed(() => scoring.value.meta_dimensions ?? {})
const fams = computed(() => scoring.value.meta_families ?? {})
const dimScores = computed(() => scoring.value.dim_scores ?? {})

const byFamily = computed(() => {
    const groups = {}
    Object.entries(dims.value).forEach(([id, d]) => {
        if (!groups[d.famille]) groups[d.famille] = []
        groups[d.famille].push({ id: parseInt(id), ...d, score: dimScores.value[id] ?? 0 })
    })
    return groups
})

const radarRef = ref(null)

const famScores = computed(() => {
    const totals = { 1: 0, 2: 0, 3: 0, 4: 0 }
    const counts  = { 1: 0, 2: 0, 3: 0, 4: 0 }
    Object.entries(dims.value).forEach(([id, d]) => {
        const s = dimScores.value[id] ?? 0
        totals[d.famille] += s
        counts[d.famille]++
    })
    return Object.keys(totals).map(fid => {
        const max = counts[fid] * 20
        return counts[fid] > 0 ? Math.round((totals[fid] / max) * 100) : 0
    })
})

onMounted(() => {
    if (!radarRef.value) return
    const f = fams.value
    const famIds = ['1', '2', '3', '4']
    const labels = famIds.map(id => f[id]?.label ?? id)
    const colors = famIds.map(id => f[id]?.color ?? '#4F46E5')
    // Alpha 80 % pour les barres : hex + CC (hex alpha) ou fallback rgba
    const bgColors = colors.map(c => c.startsWith('#') && c.length === 7 ? c + 'CC' : c)

    // Bar chart horizontal : plus lisible que le radar pour 4 valeurs
    // → chaque famille a sa couleur propre, la longueur = % de développement
    new Chart(radarRef.value, {
        type: 'bar',
        data: {
            labels,
            datasets: [{
                data: famScores.value,
                backgroundColor: bgColors,
                borderColor: colors,
                borderWidth: 2,
                borderRadius: 6,
                borderSkipped: false,
            }],
        },
        options: {
            indexAxis: 'y',   // barres horizontales
            responsive: true,
            scales: {
                x: {
                    min: 0, max: 100,
                    grid: { color: 'rgba(0,0,0,0.05)' },
                    ticks: { callback: v => v + '%', font: { size: 11 } },
                },
                y: {
                    grid: { display: false },
                    ticks: { font: { size: 12 } },
                },
            },
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: { label: ctx => `${ctx.raw}%` }
                }
            },
        },
    })
})

const dimColor = (score) => {
    if (score <= 8)  return '#dc2626'
    if (score <= 12) return '#ea580c'
    if (score <= 16) return '#2563eb'
    return '#16a34a'
}
const dimLabel = (score) => {
    if (score <= 8)  return 'Zone de développement prioritaire'
    if (score <= 12) return 'Compétence en construction'
    if (score <= 16) return 'Compétence développée'
    return 'Point fort'
}
</script>

<template>
    <CandidateLayout>
        <Head title="Tes résultats — PraxiEmo" />

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
                <span class="pt-badge">PraxiEmo · 16 dimensions</span>
                <h1 class="text-4xl font-semibold tracking-tight mt-4">Ton intelligence émotionnelle</h1>
                <p class="text-slate-600 mt-2">{{ scoring.phrase_qe }}</p>
            </div>

            <!-- Score global -->
            <section class="pt-card p-8 mb-8 text-center">
                <p class="text-xs uppercase tracking-wide text-slate-400">Score global</p>
                <p class="text-6xl font-semibold mt-2 bg-gradient-to-r from-indigo-600 to-emerald-600 bg-clip-text text-transparent">{{ scoring.score_global }}</p>
                <p class="text-sm text-slate-500 mt-1">/ {{ scoring.score_max }} — {{ scoring.niveau_qe }}</p>
            </section>

            <!-- Alerte désirabilité -->
            <section v-if="scoring.desirabilite?.alerte" class="pt-card p-6 mb-8 border-l-4 border-amber-400 bg-amber-50">
                <p class="font-semibold text-amber-900">{{ scoring.desirabilite.niveau }}</p>
                <p class="text-sm text-amber-800 mt-1">{{ scoring.desirabilite.message }}</p>
            </section>

            <!-- Radar familles EQ -->
            <section class="pt-card p-8 mb-8">
                <h2 class="text-xl font-semibold mb-6 text-center">Vue d'ensemble</h2>
                <div class="flex justify-center">
                    <div style="max-width: 360px; width: 100%">
                        <canvas ref="radarRef"></canvas>
                    </div>
                </div>
            </section>

            <!-- 4 familles -->
            <section v-for="(famQuestions, famId) in byFamily" :key="famId" class="pt-card p-8 mb-6">
                <h2 class="text-xl font-semibold mb-6" :style="{ co