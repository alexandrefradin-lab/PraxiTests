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

// Définitions neutres et brèves par type RIASEC (repli si absentes des données).
const DIM_DEF = {
    R: "Goût du concret, du manuel et du technique.",
    I: "Curiosité, analyse et résolution de problèmes.",
    A: "Créativité et expression artistique.",
    S: "Attrait pour aider et accompagner les autres.",
    E: "Goût de convaincre, diriger et entreprendre.",
    C: "Préférence pour l'ordre, la méthode et les données.",
}

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
            <ResultPanel class="mb-8">
                <h2 class="ac-panel-title mb-6">Tes 6 dimensions</h2>
                <div class="space-y-5">
                    <div v-for="(score, key) in dimensions" :key="key" class="ac-dark-item">
                        <div class="flex items-center justify-between mb-1">
                            <div>
                                <span class="ac-dark-name" :style="{ color: meta[key]?.color }">{{ meta[key]?.label ?? key }}</span>
                            </div>
                            <span class="text-sm font-medium ac-dark-muted">{{ score }}%</span>
                        </div>
                        <div class="ac-dark-track">
                            <div :style="{ width: score + '%', backgroundColor: meta[key]?.color }"></div>
                        </div>
                        <p v-if="meta[key]?.desc || DIM_DEF[key]" class="ac-dark-def">{{ meta[key]?.desc || DIM_DEF[key] }}</p>
                    </div>
                </div>
            </ResultPanel>

            <!-- Sous-domaines -->
            <ResultPanel v-if="scoring.sous_domaines" class="mb-8">
                <h2 class="ac-panel-title mb-6">Détail par sous-domaine</h2>
                <div class="grid md:grid-cols-2 gap-5">
                    <div v-for="(subs, type) in scoring.sous_domaines" :key="type" class="ac-dark-item">
                        <h3 class="ac-dark-name mb-1" :style="{ color: meta[type]?.color }">{{ meta[type]?.label }}</h3>
                        <p v-if="meta[type]?.desc || DIM_DEF[type]" class="ac-dark-def" style="margin-top:0;margin-bottom:0.75rem">{{ meta[type]?.desc || DIM_DEF[type] }}</p>
                        <div v-for="(value, label) in subs" :key="label" class="flex justify-between text-sm py-1">
                            <span class="ac-dark-muted">{{ label }}</span>
                            <span class="font-medium ac-dark-name">{{ value }}/7</span>
                        </div>
                    </div>
                </div>
            </ResultPanel>

            <ResultPdfButton :attempt-id="attempt.id" />
        </div>
    </CandidateLayout>
</template>
