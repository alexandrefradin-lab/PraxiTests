<script setup>
/**
 * Page résultats — PraxiCog (aptitude au raisonnement).
 *
 * RÈGLE : uniquement des classes pt-* et des variables var(--pt-*).
 * Positionnement : profil indicatif + niveaux, JAMAIS de score de QI.
 */
import { computed } from 'vue'
import { Head } from '@inertiajs/vue3'
import CandidateLayout from '@/Layouts/CandidateLayout.vue'
import RestitutionHeader from '@/Components/RestitutionHeader.vue'
import ResultPanel from '@/Components/ResultPanel.vue'
import RadarChart from '@/Components/RadarChart.vue'
import SynthesisCard from '@/Components/SynthesisCard.vue'
import ResultPdfButton from '@/Components/ResultPdfButton.vue'

const props = defineProps({
    attempt: Object,
    result:  Object,
})

const scoring    = computed(() => props.result?.scoring ?? {})
const perDim     = computed(() => scoring.value.per_dimension ?? {})
const norms      = computed(() => scoring.value.norm_scores ?? {})
const globalPct  = computed(() => scoring.value.global_score ?? 0)
const globalNorm = computed(() => scoring.value.global_norm ?? {})
const speed      = computed(() => scoring.value.speed ?? {})
const disclaimer = computed(() => scoring.value.disclaimer ?? '')

// Ordre fixe des domaines pour une lecture stable.
const ORDER = ['logique', 'verbal', 'numerique', 'spatial']
const SHORT = { logique: 'Logique', verbal: 'Verbal', numerique: 'Numérique', spatial: 'Spatial' }

const domains = computed(() =>
    ORDER
        .filter((k) => perDim.value[k])
        .map((k) => ({
            key:      k,
            label:    perDim.value[k].label,
            percent:  perDim.value[k].percent,
            correct:  perDim.value[k].correct,
            total:    perDim.value[k].total,
            color:    perDim.value[k].color,
            level:    norms.value[k]?.label ?? null,
            levelDesc:norms.value[k]?.description ?? null,
            dots:     norms.value[k]?.dots ?? dotsFromPct(perDim.value[k].percent),
        }))
)

// Repli si aucune norme n'a pu enrichir le score (dots dérivés du % réussite).
function dotsFromPct(p) {
    if (p >= 85) return 5
    if (p >= 65) return 4
    if (p >= 35) return 3
    if (p >= 15) return 2
    return 1
}

const radarAxes = computed(() =>
    domains.value.map((d) => ({ label: SHORT[d.key] ?? d.label, value: Number(d.percent) }))
)

// Domaine le plus fort / le plus faible (points d'appui & de progrès).
const sorted   = computed(() => [...domains.value].sort((a, b) => b.percent - a.percent))
const strongest = computed(() => sorted.value[0] ?? null)
const weakest   = computed(() => sorted.value[sorted.value.length - 1] ?? null)

const globalLabel = computed(() => globalNorm.value?.label ?? null)
</script>

<template>
    <CandidateLayout>
        <Head title="Tes résultats — PraxiCog" />

        <div style="max-width:800px;margin:0 auto">

            <RestitutionHeader
                kicker="PraxiCog · Aptitude au raisonnement"
                title="Votre profil de raisonnement"
                subtitle="Voici comment vous mobilisez quatre grandes familles de raisonnement. C'est une photographie indicative de vos aptitudes, pas un verdict."
            />

            <!-- ── DISCLAIMER (obligatoire, non négociable) ─────────────────── -->
            <div class="pt-card" style="padding:1rem 1.25rem;margin-bottom:1.75rem;
                        border-left:3px solid var(--pt-gold);display:flex;gap:.75rem;align-items:flex-start">
                <span style="font-size:18px;line-height:1">ℹ️</span>
                <p style="font-size:13px;color:var(--pt-text-muted);line-height:1.6;margin:0">
                    {{ disclaimer }}
                </p>
            </div>

            <!-- ── SCORE GLOBAL (niveau indicatif) ──────────────────────────── -->
            <div class="pt-card" style="padding:1.75rem;margin-bottom:2rem;text-align:center">
                <p style="font-size:11px;text-transform:uppercase;letter-spacing:.1em;
                          color:var(--pt-gold);font-weight:600;margin-bottom:.75rem">
                    Vue d'ensemble
                </p>
                <div style="font-size:44px;font-weight:800;color:var(--pt-text);line-height:1">
                    {{ globalPct }}<span style="font-size:20px;font-weight:600;color:var(--pt-text-light)">% de réussite</span>
                </div>
                <p v-if="globalLabel" style="margin-top:.75rem;font-size:15px;color:var(--pt-text)">
                    Niveau global :
                    <span style="font-weight:700;color:var(--pt-gold)">{{ globalLabel }}</span>
                </p>
                <p style="margin-top:.5rem;font-size:13px;color:var(--pt-text-light)">
                    {{ scoring.correct_count }} / {{ scoring.item_count }} bonnes réponses
                    <template v-if="speed.label"> · {{ speed.label }}</template>
                </p>
            </div>

            <!-- ── TOILE DES 4 DOMAINES ─────────────────────────────────────── -->
            <ResultPanel label="Votre profil en un coup d'œil" :dark="false" style="margin-bottom:2rem">
                <div style="display:flex;justify-content:center">
                    <RadarChart :axes="radarAxes" />
                </div>
                <p style="text-align:center;margin-top:1rem;font-size:13px;color:var(--pt-text-muted)">
                    Chaque axe indique votre taux de réussite (0 à 100 %) sur ce type de raisonnement.
                </p>
            </ResultPanel>

            <!-- ── POINTS D'APPUI / DE PROGRÈS ──────────────────────────────── -->
            <div v-if="strongest && weakest && strongest.key !== weakest.key"
                 style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:2rem">
                <div class="pt-card" style="padding:1.25rem;border-top:3px solid var(--pt-gold)">
                    <p style="font-size:11px;text-transform:uppercase;letter-spacing:.08em;
                              color:var(--pt-text-light);margin-bottom:.4rem">Point d'appui</p>
                    <p style="font-size:16px;font-weight:700;color:var(--pt-text);margin:0">{{ strongest.label }}</p>
                    <p style="font-size:13px;color:var(--pt-text-muted);margin-top:.35rem">
                        {{ strongest.percent }} % de réussite — votre terrain le plus solide.
                    </p>
                </div>
                <div class="pt-card" style="padding:1.25rem;border-top:3px solid var(--pt-text-light)">
                    <p style="font-size:11px;text-transform:uppercase;letter-spacing:.08em;
                              color:var(--pt-text-light);margin-bottom:.4rem">Axe de progrès</p>
                    <p style="font-size:16px;font-weight:700;color:var(--pt-text);margin:0">{{ weakest.label }}</p>
                    <p style="font-size:13px;color:var(--pt-text-muted);margin-top:.35rem">
                        {{ weakest.percent }} % de réussite — le domaine avec le plus de marge.
                    </p>
                </div>
            </div>

            <!-- ── DÉTAIL PAR DOMAINE ───────────────────────────────────────── -->
            <h2 style="font-size:12px;font-weight:700;text-transform:uppercase;
                       letter-spacing:.1em;color:var(--pt-text-light);margin-bottom:1.25rem">
                Le détail, domaine par domaine
            </h2>

            <div style="display:flex;flex-direction:column;gap:1rem;margin-bottom:2.5rem">
                <div v-for="d in domains" :key="d.key" class="pt-card" style="padding:1.5rem">
                    <div style="display:flex;align-items:center;justify-content:space-between;
                                gap:1rem;margin-bottom:.75rem">
                        <div style="display:flex;align-items:center;gap:.6rem">
                            <span style="width:12px;height:12px;border-radius:3px;display:inline-block"
                                  :style="{ background: d.color }"></span>
                            <span style="font-size:16px;font-weight:700;color:var(--pt-text)">{{ d.label }}</span>
                        </div>
                        <span style="font-size:15px;font-weight:800"
                              :style="{ color: d.color }">
                            {{ d.percent }}<span style="font-size:11px;font-weight:400;color:var(--pt-text-light)">%</span>
                        </span>
                    </div>

                    <!-- barre de réussite -->
                    <div style="height:10px;background:var(--pt-surface-2);border-radius:5px;overflow:hidden">
                        <div style="height:100%;border-radius:5px;transition:width .8s cubic-bezier(.4,0,.2,1)"
                             :style="{ width: d.percent + '%', background: d.color }"></div>
                    </div>

                    <!-- niveau indicatif (dots + label) -->
                    <div style="display:flex;align-items:center;gap:.75rem;margin-top:1rem">
                        <div style="display:flex;gap:4px">
                            <span v-for="n in 5" :key="n"
                                  style="width:9px;height:9px;border-radius:50%"
                                  :style="{ background: n <= d.dots ? d.color : 'var(--pt-surface-2)' }"></span>
                        </div>
                        <span v-if="d.level" style="font-size:13px;font-weight:600;color:var(--pt-text)">
                            {{ d.level }}
                        </span>
                        <span style="font-size:12px;color:var(--pt-text-light);margin-left:auto">
                            {{ d.correct }}/{{ d.total }}
                        </span>
                    </div>

                    <p v-if="d.levelDesc" style="font-size:13px;color:var(--pt-text-muted);
                              line-height:1.6;margin:.75rem 0 0">
                        {{ d.levelDesc }}
                    </p>
                </div>
            </div>

            <!-- ── SYNTHÈSE IA ──────────────────────────────────────────────── -->
            <SynthesisCard v-if="result?.ai_synthesis" :source="result.ai_synthesis" title="Votre synthèse" />
            <div v-else class="pt-card" style="padding:3rem;text-align:center;margin-bottom:1rem">
                <div style="width:36px;height:36px;border-radius:50%;border:3px solid var(--pt-cream-dark);
                            border-top-color:var(--pt-gold);animation:spin 1s linear infinite;margin:0 auto"></div>
                <p style="margin-top:1rem;color:var(--pt-text-muted)">Analyse en cours… (1 à 2 minutes)</p>
            </div>

            <ResultPdfButton :attempt-id="attempt.id" />

        </div>
    </CandidateLayout>
</template>

<style scoped>
@keyframes spin { to { transform: rotate(360deg) } }

@media (max-width: 640px) {
    div[style*="grid-template-columns:1fr 1fr"] {
        grid-template-columns: 1fr !important;
    }
}
</style>
