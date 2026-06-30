<script setup>
import { computed } from 'vue'
import CandidateLayout from '@/Layouts/CandidateLayout.vue'
import RadarChart from '@/Components/RadarChart.vue'
import MarkdownText from '@/Components/MarkdownText.vue'
import RestitutionHeader from '@/Components/RestitutionHeader.vue'
import ResultPanel from '@/Components/ResultPanel.vue'

const props = defineProps({
    attempt:      Object,
    result:       Object,
})
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

// Toile d'araignée sur les 4 familles (les 15 dimensions = trop d'axes).
// Valeur 0-100 par famille = moyenne des dimensions membres, chacune
// normalisée depuis son score brut /20 (même base que les barres de la page).
const radarAxes = computed(() =>
    Object.entries(byFamily.value).map(([famId, famDims]) => {
        const pcts = famDims.map((d) => ((d.score ?? 0) / 20) * 100)
        const avg = pcts.length ? pcts.reduce((a, b) => a + b, 0) / pcts.length : 0
        return {
            label: fams.value[famId]?.label ?? '',
            value: Math.round(avg),
            color: fams.value[famId]?.color,
        }
    })
)

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

        <div class="max-w-4xl mx-auto">
            <RestitutionHeader
                kicker="PraxiEmo · 16 dimensions"
                title="Ton intelligence émotionnelle"
                :subtitle="scoring.phrase_qe"
            />

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

            <!-- Radar — vue d'ensemble par famille -->
            <ResultPanel label="Ton profil en un coup d'œil" class="mb-8">
                <div class="flex justify-center">
                    <RadarChart :axes="radarAxes" dark />
                </div>
            </ResultPanel>

            <!-- 4 familles -->
            <section v-for="(famQuestions, famId) in byFamily" :key="famId" class="pt-card p-8 mb-6">
                <h2 class="text-xl font-semibold mb-6" :style="{ color: fams[famId]?.color }">{{ fams[famId]?.label }}</h2>
                <div class="space-y-4">
                    <div v-for="d in famQuestions" :key="d.id">
                        <div class="flex justify-between items-baseline mb-1">
                            <span class="font-medium">{{ d.label }}</span>
                            <span class="text-xs px-2 py-0.5 rounded-full" :style="{ backgroundColor: dimColor(d.score) + '20', color: dimColor(d.score) }">{{ dimLabel(d.score) }}</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <div class="pt-progress-track flex-1">
                                <div class="h-full rounded-full transition-all duration-700" :style="{ width: ((d.score / 20) * 100) + '%', backgroundColor: dimColor(d.score) }"></div>
                            </div>
                            <span class="text-sm font-semibold w-12 text-right">{{ d.score }}/20</span>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Top forces / dev -->
            <div class="grid md:grid-cols-2 gap-6 mb-8">
                <section class="pt-card p-6">
                    <h2 class="font-semibold mb-3">Tes 3 forces</h2>
                    <ol class="space-y-2 text-sm">
                        <li v-for="(id, i) in scoring.top_forces" :key="id" class="flex justify-between border-b border-slate-100 pb-2 last:border-0">
                            <span><span class="text-emerald-600 font-semibold">{{ i + 1 }}.</span> {{ dims[id]?.label }}</span>
                            <span class="text-slate-500">{{ dimScores[id] }}/20</span>
                        </li>
                    </ol>
                </section>
                <section v-if="scoring.top_dev?.length" class="pt-card p-6">
                    <h2 class="font-semibold mb-3">Axes de progression</h2>
                    <ol class="space-y-2 text-sm">
                        <li v-for="(id, i) in scoring.top_dev" :key="id" class="flex justify-between border-b border-slate-100 pb-2 last:border-0">
                            <span><span class="text-rose-600 font-semibold">{{ i + 1 }}.</span> {{ dims[id]?.label }}</span>
                            <span class="text-slate-500">{{ dimScores[id] }}/20</span>
                        </li>
                    </ol>
                </section>
            </div>

            <div v-if="result?.ai_synthesis" class="mt-6 pt-4 border-t border-amber-200">
                <h3 class="font-semibold mb-2">Synthèse personnalisée</h3>
                <MarkdownText :source="result.ai_synthesis" />
                <p style="margin-top:0.85rem;padding-top:0.7rem;border-top:1px solid rgba(217,119,6,0.25);font-size:11.5px;line-height:1.55;color:#9a8866">
                    <strong style="font-weight:600;color:#57534e">Outil d'auto-évaluation et de développement personnel.</strong>
                    Cette synthèse est générée par IA, à titre informatif. Elle ne constitue pas un avis
                    professionnel et ne remplace pas un psychologue, un médecin ou un coach.
                </p>
            </div>

            <div class="text-center mt-12">

                <a :href="route('results.pdf', attempt.id)" class="pt-btn-ghost">Télécharger en PDF</a>
            </div>
        </div>
    </CandidateLayout>
</template>
