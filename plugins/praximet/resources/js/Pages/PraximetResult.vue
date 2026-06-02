<script setup>
import { computed, onMounted, ref } from 'vue'
import { Link } from '@inertiajs/vue3'
import CandidateLayout from '@/Layouts/CandidateLayout.vue'
import { Chart, RadialLinearScale, PointElement, LineElement, Filler, Tooltip } from 'chart.js'
Chart.register(RadialLinearScale, PointElement, LineElement, Filler, Tooltip)

const props = defineProps({
    attempt: Object,
    result: Object,
})

const scoring = computed(() => props.result?.scoring ?? {})
const dimensions = computed(() => scoring.value.dimensions ?? {})
const code = computed(() => scoring.value.code ?? '')
const meta = computed(() => scoring.value.types_meta ?? {})

const codeLetters = computed(() => code.value.split(''))

// Sous-domaines en ordre Holland standard (R→I→A→S→E→C) avec pourcentages calculés
const sousDomairesOrdered = computed(() => {
    const raw = scoring.value.sous_domaines ?? {}
    return ['R', 'I', 'A', 'S', 'E', 'C']
        .filter(k => raw[k] !== undefined)
        .map(k => ({
            type: k,
            subs: Object.entries(raw[k]).map(([label, value]) => ({
                label,
                value,
                pct: Math.round((value / 7) * 100),
            })),
        }))
})

const radarRef = ref(null)

onMounted(() => {
    if (!radarRef.value) return
    // Ordre Holland standard — fixé explicitement pour ne pas dépendre de Object.keys()
    const keys = ['R', 'I', 'A', 'S', 'E', 'C']
    const labels = keys.map(k => meta.value[k]?.label ?? k)
    const data = keys.map(k => dimensions.value[k] ?? 0)

    new Chart(radarRef.value, {
        type: 'radar',
        data: {
            labels,
            datasets: [{
                data,
                backgroundColor: 'rgba(79, 70, 229, 0.15)',
                borderColor: '#4F46E5',
                borderWidth: 2,
                pointBackgroundColor: keys.map(k => meta.value[k]?.color ?? '#4F46E5'),
                pointRadius: 4,
                pointHoverRadius: 6,
            }]
        },
        options: {
            responsive: true,
            scales: {
                r: {
                    min: 0,
                    max: 100,
                    ticks: { stepSize: 25, display: false },
                    grid: { color: 'rgba(0,0,0,0.07)' },
                    pointLabels: { font: { size: 13 }, color: '#475569' },
                    angleLines: { color: 'rgba(0,0,0,0.07)' },
                }
            },
            plugins: { tooltip: { enabled: true }, legend: { display: false } },
        }
    })
})
</script>

<template>
    <CandidateLayout>
        <Head title="Tes résultats RIASEC" />

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
                <span class="pt-badge">Test RIASEC · Modèle Holland</span>
                <h1 class="text-4xl font-semibold tracking-tight mt-4">Ton code RIASEC</h1>
                <div class="flex items-center justify-center gap-3 mt-6">
                    <span v-for="(letter, i) in codeLetters" :key="i" class="h-16 w-16 rounded-2xl flex items-center justify-center text-3xl font-bold text-white shadow-md" :style="{ backgroundColor: meta[letter]?.color ?? '#4F46E5' }">
                        {{ letter }}
                    </span>
                </div>
                <p class="text-slate-600 mt-4">{{ scoring.profile_label }}</p>
            </div>

            <!-- Synthèse IA si dispo -->
            <section v-if="attempt.result?.ai_synthesis" class="pt-card p-8 mb-8">
                <h2 class="text-xl font-semibold mb-4">Ta synthèse</h2>
                <div class="prose prose-slate max-w-none whitespace-pre-line text-[15px] leading-relaxed">{{ attempt.result.ai_synthesis }}</div>
            </section>

            <!-- Radar chart RIASEC -->
            <section class="pt-card p-8 mb-8 flex justify-center">
                <div style="max-width: 380px; width: 100%">
                    <canvas ref="radarRef"></canvas>
                </div>
            </section>

            <!-- 6 types RIASEC -->
            <section class="pt-card p-8 mb-8">
                <h2 class="text-xl font-semibold mb-6">Tes 6 dimensions</h2>
                <div class="space-y-5">
                    <div v-for="(score, key) in dimensions" :key="key">
                        <div class="flex items-center justify-between mb-1">
                            <div>
                                <span class="font-semibold" :style="{ color: meta[key]?.color }">{{ meta[key]?.label ?? key }}</span>
                                <p class="text-xs text-slate-500 mt-0.5">{{ meta[key]?.desc }}</p>
                            </div>
                            <span class="text-sm font-medium text-slate-700">{{ score }}%</span>
                        </div>
                        <div class="pt-progress-track">
                            <div class="h-full rounded-full transition-all duration-700" :style="{ width: score + '%', backgroundColor: meta[key]?.color }"></div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Sous-domaines — ordre Holland fixé, barres de progression, pourcentages --