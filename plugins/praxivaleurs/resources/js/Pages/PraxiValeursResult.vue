<script setup>
import { computed, onMounted, ref } from 'vue'
import { Link } from '@inertiajs/vue3'
import CandidateLayout from '@/Layouts/CandidateLayout.vue'
import { Chart, RadialLinearScale, ArcElement, Tooltip, Legend } from 'chart.js'
Chart.register(RadialLinearScale, ArcElement, Tooltip, Legend)

const props = defineProps({ attempt: Object, result: Object })

const scoring = computed(() => props.result?.scoring ?? {})
const top5 = computed(() => Object.entries(scoring.value.top5 ?? {}))
const all = computed(() => Object.entries(scoring.value.dimensions ?? {}))
const meta = computed(() => scoring.value.meta ?? {})

const polarRef = ref(null)

onMounted(() => {
    if (!polarRef.value) return
    const entries = Object.entries(scoring.value.top5 ?? {})
    const top5Labels = entries.map(([k]) => meta.value[k]?.label ?? k)
    const top5Values = entries.map(([, v]) => v)
    const top5Colors = entries.map(([k]) => {
        const hex = meta.value[k]?.color ?? '#4F46E5'
        return hex + 'B3' // alpha 0.7 via hex suffix
    })

    new Chart(polarRef.value, {
        type: 'polarArea',
        data: {
            labels: top5Labels,
            datasets: [{ data: top5Values, backgroundColor: top5Colors, borderWidth: 0 }]
        },
        options: {
            responsive: true,
            scales: {
                r: {
                    min: 0,
                    max: 100,
                    ticks: { display: false },
                    grid: { color: 'rgba(0,0,0,0.06)' }
                }
            },
            plugins: {
                legend: { position: 'bottom', labels: { font: { size: 12 }, padding: 12 } }
            }
        }
    })
})
</script>

<template>
    <CandidateLayout>
        <Head title="Tes valeurs prioritaires" />

        <!-- FE-07 — guard result null -->
        <template v-if="!result">
            <div class="pt-card p-8 text-center text-slate-500">
                <p>Résultats non disponibles — veuillez réessayer dans quelques instants.</p>
                <Link :href="route('history')" class="pt-btn-ghost mt-4 inline-block text-sm">← Mon historique</Link>
            </div>
        </template>
        <template v-if="result">
        <div class="max-w-3xl mx-auto">
            <div class="text-center mb-12">
                <span class="pt-badge">Schwartz · 10 valeurs universelles</span>
                <h1 class="text-4xl font-semibold tracking-tight mt-4">Voici ce qui te porte.</h1>
                <p class="text-slate-600 mt-2">Tes 5 valeurs prioritaires, classées de la plus à la moins importante pour toi.</p>
            </div>

            <!-- Top 5 -->
            <section class="pt-card p-8 mb-8">
                <h2 class="text-xl font-semibold mb-6">Ton top 5</h2>
                <div class="flex justify-center mb-8">
                    <div style="max-width: 320px; width: 100%">
                        <canvas ref="polarRef"></canvas>
                    </div>
                </div>
                <ol class="space-y-4">
                    <li v-for="([key, score], i) in top5" :key="key" class="flex items-center gap-4 p-4 rounded-xl border border-slate-100">
                        <span class="h-10 w-10 rounded-full flex items-center justify-center font-bold text-white shadow-sm" :style="{ backgroundColor: meta[key]?.color }">{{ i + 1 }}</span>
                        <div class="flex-1">
                            <p class="font-semibold" :style="{ color: meta[key]?.color }">{{ meta[key]?.label }}</p>
                            <p class="text-sm text-slate-600 mt-0.5">{{ meta[key]?.court }}</p>
                        </div>
                        <span class="text-2xl font-semibold text-slate-700">{{ score }}</span>
                    </li>
                </ol>
            </section>

            <!-- Toutes les dimensions -->
            <section class="pt-card p-8 mb-8">
                <h2 class="text-xl font-semibold mb-6">Tes 10 valeurs</h2>
                <div class="space-y-4">
                    <div v-for="([key, score]) in all" :key="key">
                        <div class="f