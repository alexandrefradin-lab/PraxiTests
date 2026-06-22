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

const props = defineProps({
    attempt: Object,
    result:  Object,
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

            <!-- En-tête -->
            <div style="text-align:center;margin-bottom:2.5rem">
                <span class="pt-badge" style="margin-bottom:.75rem">Le Radar des Sens — Hypersensibilité</span>
                <h1 style="font-size:28px;margin-top:6px">Votre profil de sensibilité</h1>
                <p style="font-size:14px;color:var(--pt-text-muted);margin-top:6px;line-height:1.5">
                    D'après le modèle de la sensibilité de traitement sensoriel (E. Aron). Outil d'auto-réflexion, pas un diagnostic.
                </p>
            </div>

            <!-- Score global + palier -->
            <div v-if="globalScore !== null" class="pt-card"
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

            <!-- Synthèse IA -->
            <SynthesisCard v-if="result?.ai_synthesis" :source="result.ai_synthesis" title="Votre synthèse" />
            <div v-else class="pt-card" style="padding:3rem;text-align:center;margin-bottom:1rem">
                <div style="width:36px;height:36px;border-radius:50%;border:3px solid var(--pt-cream-dark);border-top-color:var(--pt-gold);animation:spin 1s linear infinite;margin:0 auto"></div>
                <p style="margin-top:1rem;color:var(--pt-text-muted)">Analyse en cours… (1 à 2 minutes)</p>
            </div>

            <!-- Profil en un coup d'œil — toile d'araignée -->
            <div v-if="radarAxes.length >= 3" class="pt-card" style="padding:1.5rem;margin-bottom:1rem">
                <h2 style="font-size:16px;font-weight:500;margin-bottom:2px">Ton profil en un coup d'œil</h2>
                <p style="font-size:13px;color:var(--pt-text-muted);margin-bottom:1rem">
                    Vos 3 sous-dimensions de sensibilité, d'un seul regard.
                </p>
                <div style="display:flex;justify-content:center">
                    <RadarChart :axes="radarAxes" />
                </div>
            </div>

            <!-- Sous-dimensions -->
            <div class="pt-card" style="padding:1.5rem;margin-bottom:1rem">
                <div style="display:flex;align-items:baseline;justify-content:space-between;margin-bottom:1.25rem">
                    <h2 style="font-size:16px;font-weight:500">Vos 3 sous-dimensions</h2>
                    <span v-if="Object.values(normScores).some(n => n?.label)"
                        style="font-size:11px;color:var(--pt-text-light);font-style:italic">
                        Comparé à une population de référence
                    </span>
                </div>

                <div style="display:flex;flex-direction:column;gap:1rem">
                    <div v-for="(score, dimKey) in dims" :key="dimKey">
                        <div style="display:flex;align-items:center;gap:12px">
                            <span style="font-size:13px;font-weight:500;min-width:150px">
                                {{ meta[dimKey]?.label ?? dimKey }}
                            </span>

                            <div class="pt-progress-track" style="flex:1">
                                <div class="pt-progress-fill" :style="{ width: barWidth(dimKey) + '%' }"></div>
                            </div>

                            <div v-if="normScores[dimKey]?.label"
                                style="display:flex;align-items:center;gap:8px;flex-shrink:0;min-width:220px">
                                <div style="display:flex;gap:3px">
                                    <div v-for="n in 5" :key="n"
                                        style="width:9px;height:9px;border-radius:50%;transition:background .2s"
                                        :style="{ background: n <= normScores[dimKey].dots ? dotColor(normScores[dimKey].color) : 'var(--pt-cream-dark)' }">
                                    </div>
                                </div>
                                <span style="font-size:12px;font-weight:500"
                                    :style="{ color: dotColor(normScores[dimKey].color) }">
                                    {{ normScores[dimKey].label }}
                                </span>
                            </div>

                            <div v-else style="font-size:12px;color:var(--pt-text-light);min-width:36px;text-align:right">
                                {{ score }}%
                            </div>
                        </div>

                        <p v-if="meta[dimKey]?.description"
                            style="font-size:12px;color:var(--pt-text-light);margin-top:5px;padding-left:162px;line-height:1.4">
                            {{ meta[dimKey].description }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Métiers suggérés -->
            <div v-if="result?.suggested_jobs?.length" class="pt-card" style="padding:1.5rem;margin-bottom:1rem">
                <h2 style="font-size:16px;font-weight:500;margin-bottom:1.25rem">
                    {{ result.suggested_jobs.length }} métiers à explorer
                </h2>
                <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:.75rem">
                    <div v-for="(job, i) in result.suggested_jobs" :key="i"
                        style="border:.5px solid var(--pt-border);border-radius:10px;padding:1rem">
                        <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:8px;margin-bottom:4px">
                            <h3 style="font-size:14px;font-weight:500">{{ job.titre || job.title }}</h3>
                            <span style="font-size:11px;font-weight:600;padding:2px 8px;border-radius:99px;background:var(--pt-gold-pale);color:var(--pt-gold-hover);border:.5px solid var(--pt-gold-border);white-space:nowrap">
                                {{ job.fit_score }}%
                            </span>
                        </div>
                        <p style="font-size:10px;text-transform:uppercase;letter-spacing:.05em;color:var(--pt-text-light);margin-bottom:6px">
                            {{ job.secteur || job.sector }}
                        </p>
                        <p style="font-size:13px;color:var(--pt-text-muted);line-height:1.45">{{ job.pourquoi || job.why }}</p>
                        <p v-if="job.prochaine_étape || job.next_step"
                            style="font-size:12px;color:var(--pt-gold);margin-top:6px;font-weight:500">
                            → {{ job.prochaine_étape || job.next_step }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div style="display:flex;gap:.75rem;justify-content:center;margin-top:2rem;flex-wrap:wrap">
                <a :href="route('results.pdf', attempt.id)" class="pt-btn-ghost">Télécharger en PDF</a>
                <Link :href="route('tests.index')" class="pt-btn-ghost">Voir les autres tests</Link>
                <Link :href="route('history')" class="pt-btn-primary">Mon historique →</Link>
            </div>

        </div>
    </CandidateLayout>
</template>

<style scoped>
@keyframes spin { to { transform: rotate(360deg) } }
</style>
