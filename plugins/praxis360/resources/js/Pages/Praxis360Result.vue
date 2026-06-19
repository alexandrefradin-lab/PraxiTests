<script setup>
/**
 * Page résultats — Praxis 360 (auto-évaluation soft skills)
 *
 * Hérite du thème premium via CandidateLayout + classes pt-* + variables --pt-*.
 * RÈGLE : n'utiliser que pt-* et var(--pt-*). Aucune couleur Tailwind hardcodée.
 *
 * NB : ce 360° est ici en mode AUTO-ÉVALUATION uniquement (mono-candidat). La
 * comparaison multi-évaluateurs (regard des autres, écarts, angles morts) du
 * plugin WordPress n'est pas portée — voir README.
 */
import { computed } from 'vue'
import { Link }     from '@inertiajs/vue3'
import CandidateLayout from '@/Layouts/CandidateLayout.vue'

const props = defineProps({
    attempt: Object,
    result:  Object,
})

const scoring      = computed(() => props.result?.scoring ?? {})
const dims         = computed(() => scoring.value.dimensions   ?? {})
const normScores   = computed(() => scoring.value.norm_scores  ?? {})
const meta         = computed(() => scoring.value.meta         ?? {})
const global_score = computed(() => scoring.value.global_score ?? null)
const strengths    = computed(() => scoring.value.strengths    ?? [])
const improvements = computed(() => scoring.value.improvements ?? [])

const labelOf = (dimKey) => meta.value[dimKey]?.label ?? dimKey

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
</script>

<template>
    <CandidateLayout>
        <Head :title="`Vos résultats — Praxis 360`" />

        <div style="max-width:780px;margin:0 auto">

            <!-- En-tête -->
            <div style="text-align:center;margin-bottom:2.5rem">
                <span class="pt-badge" style="margin-bottom:.75rem">Praxis 360 — Soft skills</span>
                <h1 style="font-size:28px;margin-top:6px">Votre profil soft skills</h1>
                <p style="font-size:14px;color:var(--pt-text-muted);margin-top:6px;line-height:1.5">
                    Auto-évaluation sur 6 dimensions, analysée par notre IA.
                </p>
            </div>

            <!-- Score global -->
            <div v-if="global_score !== null" class="pt-card"
                style="padding:1.5rem;margin-bottom:1rem;display:flex;align-items:center;gap:1.5rem">
                <div style="text-align:center;flex-shrink:0">
                    <div style="font-family:'Playfair Display',serif;font-size:52px;font-weight:600;color:var(--pt-gold);line-height:1">
                        {{ global_score }}
                    </div>
                    <div style="font-size:11px;color:var(--pt-text-light);text-transform:uppercase;letter-spacing:.06em;margin-top:2px">
                        /100
                    </div>
                </div>
                <div>
                    <p style="font-size:15px;font-weight:500;color:var(--pt-text)">Indice global soft skills</p>
                    <p style="font-size:13px;color:var(--pt-text-muted);margin-top:4px;line-height:1.5">
                        Moyenne de vos 6 dimensions. Ce n'est pas une note : c'est une photographie de vos comportements perçus, utile pour cibler 2 à 3 axes de progrès.
                    </p>
                </div>
            </div>

            <!-- Synthèse IA -->
            <div v-if="result?.ai_synthesis" class="pt-card" style="padding:1.5rem;margin-bottom:1rem">
                <h2 style="font-size:16px;font-weight:500;margin-bottom:1rem">Votre synthèse</h2>
                <p style="font-size:15px;line-height:1.75;color:var(--pt-text);white-space:pre-line">
                    {{ result.ai_synthesis }}
                </p>
            </div>

            <!-- En attente IA -->
            <div v-else class="pt-card" style="padding:3rem;text-align:center;margin-bottom:1rem">
                <div style="width:36px;height:36px;border-radius:50%;border:3px solid var(--pt-cream-dark);border-top-color:var(--pt-gold);animation:spin 1s linear infinite;margin:0 auto"></div>
                <p style="margin-top:1rem;color:var(--pt-text-muted)">Analyse en cours… (1 à 2 minutes)</p>
            </div>

            <!-- Dimensions avec étalonnage -->
            <div class="pt-card" style="padding:1.5rem;margin-bottom:1rem">
                <div style="display:flex;align-items:baseline;justify-content:space-between;margin-bottom:1.25rem">
                    <h2 style="font-size:16px;font-weight:500">Vos 6 dimensions</h2>
                    <span v-if="Object.values(normScores).some(n => n?.label)"
                        style="font-size:11px;color:var(--pt-text-light);font-style:italic">
                        Comparé à une population de référence
                    </span>
                </div>

                <div style="display:flex;flex-direction:column;gap:1rem">
                    <div v-for="(score, dimKey) in dims" :key="dimKey">
                        <div style="display:flex;align-items:center;gap:12px">
                            <span style="font-size:13px;font-weight:500;min-width:180px">
                                {{ labelOf(dimKey) }}
                            </span>

                            <div class="pt-progress-track" style="flex:1">
                                <div class="pt-progress-fill" :style="{ width: barWidth(dimKey) + '%' }"></div>
                            </div>

                            <!-- Étalonnage : dots + label -->
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

                            <!-- Fallback : score normalisé -->
                            <div v-else style="font-size:12px;color:var(--pt-text-light);min-width:36px;text-align:right">
                                {{ score }}%
                            </div>
                        </div>

                        <p v-if="meta[dimKey]?.description"
                            style="font-size:12px;color:var(--pt-text-light);margin-top:5px;padding-left:192px;line-height:1.4">
                            {{ meta[dimKey].description }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Forces & axes de progrès -->
            <div v-if="strengths.length || improvements.length"
                style="display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:1rem;margin-bottom:1rem">
                <div v-if="strengths.length" class="pt-card" style="padding:1.5rem">
                    <h2 style="font-size:16px;font-weight:500;margin-bottom:1rem">Vos forces</h2>
                    <div style="display:flex;flex-direction:column;gap:.5rem">
                        <span v-for="k in strengths" :key="k"
                            style="font-size:13px;font-weight:500;padding:6px 12px;border-radius:99px;background:var(--pt-gold-pale);color:var(--pt-gold-hover);border:.5px solid var(--pt-gold-border)">
                            {{ labelOf(k) }}
                        </span>
                    </div>
                </div>
                <div v-if="improvements.length" class="pt-card" style="padding:1.5rem">
                    <h2 style="font-size:16px;font-weight:500;margin-bottom:1rem">Axes de progrès</h2>
                    <div style="display:flex;flex-direction:column;gap:.5rem">
                        <span v-for="k in improvements" :key="k"
                            style="font-size:13px;font-weight:500;padding:6px 12px;border-radius:99px;border:.5px solid var(--pt-border);color:var(--pt-text-muted)">
                            {{ labelOf(k) }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Métiers suggérés -->
            <div v-if="result?.suggested_jobs?.length" class="pt-card" style="padding:1.5rem;margin-bottom:1rem">
                <h2 style="font-size:16px;font-weight:500;margin-bottom:1.25rem">
                    {{ result.suggested_jobs.length }} pistes à explorer
                </h2>
                <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:.75rem">
                    <div v-for="(job, i) in result.suggested_jobs" :key="i"
                        style="border:.5px solid var(--pt-border);border-radius:10px;padding:1rem;transition:border-color .15s;cursor:default"
                        @mouseenter="$event.currentTarget.style.borderColor='var(--pt-gold)'"
                        @mouseleave="$event.currentTarget.style.borderColor='var(--pt-border)'">
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
                <a :href="route('results.pdf', attempt.id)" class="pt-btn-ghost">
                    Télécharger en PDF
                </a>
                <Link :href="route('tests.index')" class="pt-btn-ghost">
                    Voir les autres tests
                </Link>
                <Link :href="route('history')" class="pt-btn-primary">
                    Mon historique →
                </Link>
            </div>

        </div>
    </CandidateLayout>
</template>

<style scoped>
@keyframes spin { to { transform: rotate(360deg) } }
</style>
