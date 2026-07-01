<script setup>
/**
 * Page résultats — Le Radar des Sens (PraxiSens, hypersensibilité)
 *
 * Thème premium hérité : classes pt-* et variables --pt-*. Ne jamais hardcoder
 * de couleurs Tailwind. Score global via le composant partagé ScoreGauge.
 */
import { computed } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import CandidateLayout from '@/Layouts/CandidateLayout.vue'
import ScoreGauge from '@/Components/ScoreGauge.vue'
import RadarChart from '@/Components/RadarChart.vue'
import SynthesisCard from '@/Components/SynthesisCard.vue'
import JobCard from '@/Components/JobCard.vue'
import Disclaimer from '@/Components/Disclaimer.vue'
import RestitutionHeader from '@/Components/RestitutionHeader.vue'
import ResultPanel from '@/Components/ResultPanel.vue'
import ResultPdfButton from '@/Components/ResultPdfButton.vue'

const props = defineProps({
    attempt:      Object,
    result:       Object,
})

const scoring     = computed(() => props.result?.scoring ?? {})
const dims        = computed(() => scoring.value.dimensions   ?? {})
const normScores  = computed(() => scoring.value.norm_scores  ?? {})
const meta        = computed(() => scoring.value.meta         ?? {})
const globalScore = computed(() => scoring.value.global_score ?? null)
const globalLabel = computed(() => scoring.value.global_label ?? '')
const globalText  = computed(() => scoring.value.global_text  ?? '')

const dotColor = (color) => ({
    gold:  'var(--pt-gold)',
    navy:  'var(--pt-navy)',
    slate: '#94A3B8',
    amber: '#F59E0B',
    muted: 'var(--pt-cream-dark)',
}[color] ?? '#94A3B8')

const barWidth = (dimKey) => {
    const norm = normScores.value[dimKey]
    if (norm?.percentile) return Math.round((norm.percentile / 99) * 100)
    return dims.value[dimKey] ?? 0
}

// Définitions neutres de repli (si meta.description absente).
const SUBDIM_DEF = {
    eoe: "Tendance à la saturation face au trop-plein de sollicitations.",
    aes: "Sensibilité fine au beau et profondeur de réflexion.",
    lst: "Réactivité élevée aux stimulations sensorielles fortes.",
    emo: "Intensité du ressenti et de la résonance émotionnelle.",
}
const subDimDef = (key) => SUBDIM_DEF[key] ?? ''

// Axes de la toile d'araignée — dimensions normalisées 0..100, ordre du meta conservé
const radarAxes = computed(() =>
    Object.entries(dims.value).map(([key, value]) => {
        const axis = { label: meta.value[key]?.label ?? key, value: value ?? 0 }
        if (meta.value[key]?.color) axis.color = meta.value[key].color
        return axis
    })
)
</script>

<template>
    <CandidateLayout>
        <Head title="Vos résultats — Le Radar des Sens" />

        <div style="max-width:780px;margin:0 auto">

            <!-- ⚠️ Avertissement (toujours visible, en tête) -->
            <Disclaimer>
                <strong>Ceci n'est pas un diagnostic.</strong> Cet outil explore ton profil de sensibilité
                à partir du modèle de la sensibilité de traitement sensoriel (E. Aron). La haute sensibilité
                est un trait de tempérament, pas un trouble ni une maladie. Ces résultats sont une invitation
                à mieux te connaître — ils ne remplacent pas l'avis d'un professionnel si tu traverses une difficulté.
            </Disclaimer>

            <!-- En-tête -->
            <RestitutionHeader
                kicker="Le Radar des Sens — Hypersensibilité"
                title="Votre profil de sensibilité"
                subtitle="D'après le modèle de la sensibilité de traitement sensoriel (E. Aron). Outil d'auto-réflexion, pas un diagnostic."
            />

            <!-- Score global + palier -->
            <div v-if="globalScore !== null" class="pt-card ac-card-dark ac-card-ornate"
                style="padding:1.5rem;margin-bottom:1rem;display:flex;align-items:center;gap:1.5rem">
                <div style="flex-shrink:0">
                    <ScoreGauge :score="globalScore" :size="140" />
                </div>
                <div>
                    <p style="font-size:16px;font-weight:600;color:var(--pt-text)">{{ globalLabel }}</p>
                    <p style="font-size:13px;color:var(--pt-text-muted);margin-top:6px;line-height:1.6">
                        {{ globalText }}
                    </p>
                </div>
            </div>

            <!-- Profil en un coup d'œil — toile d'araignée -->
            <ResultPanel v-if="radarAxes.length >= 3" label="Ton profil en un coup d'œil" class="mb-8">
                <div style="display:flex;justify-content:center">
                    <RadarChart :axes="radarAxes" dark />
                </div>
            </ResultPanel>

            <!-- Sous-dimensions -->
            <ResultPanel class="mb-8">
                <div style="display:flex;align-items:baseline;justify-content:space-between;margin-bottom:1.25rem">
                    <h2 class="ac-panel-title">Vos 3 sous-dimensions</h2>
                    <span v-if="Object.values(normScores).some(n => n?.label)"
                        class="ac-dark-muted" style="font-size:11px;font-style:italic">
                        Comparé à une population de référence
                    </span>
                </div>

                <div style="display:flex;flex-direction:column;gap:1.1rem">
                    <div v-for="(score, dimKey) in dims" :key="dimKey" class="ac-dark-item">
                        <div style="display:flex;align-items:center;gap:12px">
                            <span class="ac-dark-name" style="min-width:150px">
                                {{ meta[dimKey]?.label ?? dimKey }}
                            </span>

                            <div class="ac-dark-track" style="flex:1">
                                <div :style="{ width: barWidth(dimKey) + '%', background: 'var(--color-primary)' }"></div>
                            </div>

                            <div v-if="normScores[dimKey]?.label"
                                style="display:flex;align-items:center;gap:8px;flex-shrink:0;min-width:220px">
                                <div style="display:flex;gap:3px">
                                    <div v-for="n in 5" :key="n"
                                        style="width:9px;height:9px;border-radius:50%;transition:background .2s"
                                        :style="{ background: n <= normScores[dimKey].dots ? dotColor(normScores[dimKey].color) : 'rgba(240,232,212,0.18)' }">
                                    </div>
                                </div>
                                <span style="font-size:12px;font-weight:500"
                                    :style="{ color: dotColor(normScores[dimKey].color) }">
                                    {{ normScores[dimKey].label }}
                                </span>
                            </div>

                            <div v-else class="ac-dark-muted" style="font-size:12px;min-width:36px;text-align:right">
                                {{ score }}%
                            </div>
                        </div>

                        <p class="ac-dark-def">
                            {{ meta[dimKey]?.description ?? subDimDef(dimKey) }}
                        </p>
                    </div>
                </div>
            </ResultPanel>

            <!-- Synthèse IA — après les graphiques -->
            <SynthesisCard v-if="result?.ai_synthesis" :source="result.ai_synthesis" title="Votre synthèse" />
            <div v-else class="pt-card ac-card-dark" style="padding:3rem;text-align:center;margin-bottom:1rem">
                <div style="width:36px;height:36px;border-radius:50%;border:3px solid var(--pt-cream-dark);border-top-color:var(--pt-gold);animation:spin 1s linear infinite;margin:0 auto"></div>
                <p style="margin-top:1rem;color:var(--pt-text-muted)">Analyse en cours… (1 à 2 minutes)</p>
            </div>

            <!-- Actions -->
            <div style="display:flex;gap:.75rem;justify-content:center;margin-top:2rem;flex-wrap:wrap">

                <ResultPdfButton :attempt-id="attempt.id" />
            </div>

        </div>
    </CandidateLayout>
</template>

<style scoped>
@keyframes spin { to { transform: rotate(360deg) } }
</style>
