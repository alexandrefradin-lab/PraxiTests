<script setup>
import { computed } from 'vue'
import CandidateLayout from '@/Layouts/CandidateLayout.vue'
import RadarChart from '@/Components/RadarChart.vue'
import MarkdownText from '@/Components/MarkdownText.vue'
import RestitutionHeader from '@/Components/RestitutionHeader.vue'
import ResultPanel from '@/Components/ResultPanel.vue'
import ResultPdfButton from '@/Components/ResultPdfButton.vue'

const props = defineProps({
    attempt:      Object,
    result:       Object,
})

const scoring = computed(() => props.result?.scoring ?? {})
const top5 = computed(() => Object.entries(scoring.value.top5 ?? {}))
const all = computed(() => Object.entries(scoring.value.dimensions ?? {}))
const meta = computed(() => scoring.value.meta ?? {})

// Toile d'araignée Schwartz — axes dans l'ordre du circumplex (ordre des clés
// de `meta`). Valeur = score final 0–100 ; label + couleur depuis `meta`.
const radarAxes = computed(() =>
    Object.keys(meta.value).map((key) => ({
        label: meta.value[key]?.label ?? key,
        value: Number(scoring.value.dimensions?.[key] ?? 0),
        color: meta.value[key]?.color,
    }))
)
</script>

<template>
    <CandidateLayout>
        <Head title="Tes valeurs prioritaires" />

        <div class="max-w-3xl mx-auto">
            <RestitutionHeader
                kicker="La Source des Valeurs · Schwartz"
                title="Voici ce qui te porte."
                subtitle="Tes 5 valeurs prioritaires, classées de la plus à la moins importante pour toi."
            />

            <!-- Top 5 -->
            <section class="pt-card ac-card-ornate ac-card-dark p-8 mb-8">
                <h2 class="text-xl font-semibold mb-6">Ton top 5</h2>
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

            <!-- Toile d'araignée Schwartz -->
            <ResultPanel label="Ton profil en un coup d'œil" class="mb-8">
                <div class="flex justify-center">
                    <RadarChart :axes="radarAxes" dark />
                </div>
            </ResultPanel>

            <!-- Toutes les dimensions -->
            <ResultPanel class="mb-8">
                <h2 class="ac-panel-title mb-6">Tes 10 valeurs</h2>
                <div class="space-y-4">
                    <div v-for="([key, score]) in all" :key="key" class="ac-dark-item">
                        <div class="flex justify-between text-sm mb-1">
                            <span class="ac-dark-name" :style="{ color: meta[key]?.color }">{{ meta[key]?.label }}</span>
                            <span class="ac-dark-muted">{{ score }}/100</span>
                        </div>
                        <div class="ac-dark-track">
                            <div :style="{ width: score + '%', backgroundColor: meta[key]?.color }"></div>
                        </div>
                        <p v-if="meta[key]?.court" class="ac-dark-def">{{ meta[key]?.court }}</p>
                    </div>
                </div>
            </ResultPanel>

            <!-- Définitions -->
            <section class="pt-card ac-card-dark p-8 mb-8">
                <h2 class="text-xl font-semibold mb-6">Comprendre tes valeurs</h2>
                <div class="space-y-5">
                    <div v-for="([key]) in top5" :key="key" class="border-l-4 pl-4 py-1" :style="{ borderColor: meta[key]?.color }">
                        <h3 class="font-semibold" :style="{ color: meta[key]?.color }">{{ meta[key]?.label }}</h3>
                        <p class="text-sm text-slate-700 mt-1">{{ meta[key]?.definition }}</p>
                    </div>
                </div>
            </section>

            <div v-if="result?.ai_synthesis" class="mt-6 pt-4 border-t border-amber-200">
                <h3 class="font-semibold mb-2">Synthèse personnalisée</h3>
                <MarkdownText :source="result.ai_synthesis" />
                <p style="margin-top:0.85rem;padding-top:0.7rem;border-top:1px solid rgba(217,119,6,0.25);font-size:11.5px;line-height:1.55;color:#9a8866">
                    <strong style="font-weight:600;color:#57534e">Outil d'auto-évaluation et de développement personnel.</strong>
                    Cette synthèse est générée par IA, à titre informatif. Elle ne constitue pas un avis
                    professionnel et ne remplace pas un psychologue, un médecin ou un coach.
                </p>
            </div>

            <ResultPdfButton :attempt-id="attempt.id" />
        </div>
    </CandidateLayout>
</template>
