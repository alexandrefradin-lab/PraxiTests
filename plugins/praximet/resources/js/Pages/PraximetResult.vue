<script setup>
import { computed } from 'vue'
import CandidateLayout from '@/Layouts/CandidateLayout.vue'
import SynthesisCard from '@/Components/SynthesisCard.vue'
import RadarChart from '@/Components/RadarChart.vue'
import JobCard from '@/Components/JobCard.vue'
import RestitutionHeader from '@/Components/RestitutionHeader.vue'
import ResultPanel from '@/Components/ResultPanel.vue'
import ResultPdfButton from '@/Components/ResultPdfButton.vue'

const props = defineProps({
    attempt:      Object,
    result:       Object,
})

const scoring = computed(() => props.result?.scoring ?? {})
const dimensions = computed(() => scoring.value.dimensions ?? {})
const code = computed(() => scoring.value.code ?? '')
const meta = computed(() => scoring.value.types_meta ?? {})

const codeLetters = computed(() => code.value.split(''))

// Hexagone RIASEC — axes dans l'ordre iconique R, I, A, S, E, C (valeurs 0–100).
const RIASEC_ORDER = ['R', 'I', 'A', 'S', 'E', 'C']
const radarAxes = computed(() =>
    RIASEC_ORDER.map((key) => ({
        label: meta.value[key]?.label ?? key,
        value: Number(dimensions.value[key] ?? 0),
        color: meta.value[key]?.color,
    }))
)
</script>

<template>
    <CandidateLayout>
        <Head title="Tes résultats RIASEC" />

        <div class="max-w-4xl mx-auto">
            <RestitutionHeader
                kicker="Test RIASEC · Modèle Holland"
                title="Ton code RIASEC"
                :subtitle="scoring.profile_label"
            />

            <div class="flex items-center justify-center gap-3 mb-10">
                <span v-for="(letter, i) in codeLetters" :key="i" class="h-16 w-16 rounded-2xl flex items-center justify-center text-3xl font-bold shadow-md" style="color:var(--bg-base)" :style="{ backgroundColor: meta[letter]?.color ?? 'var(--color-primary)' }">
                    {{ letter }}
                </span>
            </div>

            <!-- Synthèse IA si dispo -->
            <SynthesisCard :source="attempt.result?.ai_synthesis" title="Ta synthèse" />

            <!-- Hexagone RIASEC — panneau constellation -->
            <ResultPanel label="Ton hexagone RIASEC" class="mb-8">
                <div class="flex justify-center">
                    <RadarChart :axes="radarAxes" dark />
                </div>
            </ResultPanel>

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

            <!-- Sous-domaines -->
            <section v-if="scoring.sous_domaines" class="pt-card p-8 mb-8">
                <h2 class="text-xl font-semibold mb-6">Détail par sous-domaine</h2>
                <div class="grid md:grid-cols-2 gap-5">
                    <div v-for="(subs, type) in scoring.sous_domaines" :key="type" class="border border-slate-100 rounded-xl p-5">
                        <h3 class="font-semibold mb-3" :style="{ color: meta[type]?.color }">{{ meta[type]?.label }}</h3>
                        <div v-for="(value, label) in subs" :key="label" class="flex justify-between text-sm py-1">
                            <span class="text-slate-600">{{ label }}</span>
                            <span class="font-medium">{{ value }}/7</span>
                        </div>
                    </div>
                </div>
            </section>

            <ResultPdfButton :attempt-id="attempt.id" />
        </div>
    </CandidateLayout>
</template>
