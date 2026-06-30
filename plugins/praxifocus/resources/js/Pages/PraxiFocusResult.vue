<script setup>
/**
 * Page résultats — PraxiFocus (repérage TDAH, ASRS-v1.1)
 *
 * RÈGLE : n'utiliser que les classes pt-* et les variables var(--pt-*).
 * Ne jamais hardcoder de couleurs Tailwind (bg-indigo-600, etc.).
 *
 * ⚠️ Cette page affiche en permanence un avertissement : outil de repérage,
 * pas de diagnostic, à vérifier avec un professionnel de santé.
 */
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import CandidateLayout from '@/Layouts/CandidateLayout.vue'
import ScoreGauge from '@/Components/ScoreGauge.vue'
import SynthesisCard from '@/Components/SynthesisCard.vue'
import Disclaimer from '@/Components/Disclaimer.vue'
import RestitutionHeader from '@/Components/RestitutionHeader.vue'
import ResultPdfButton from '@/Components/ResultPdfButton.vue'

const props = defineProps({
    attempt:      Object,
    result:       Object,
})

const scoring      = computed(() => props.result?.scoring ?? {})
const dims         = computed(() => scoring.value.dimensions ?? {})
const bands        = computed(() => scoring.value.dimension_bands ?? {})
const meta         = computed(() => scoring.value.meta ?? {})
const globalScore  = computed(() => scoring.value.global_score ?? 0)
const screener     = computed(() => scoring.value.screener ?? null)
const partB        = computed(() => scoring.value.part_b_burden ?? null)
const disclaimer   = computed(() => scoring.value.disclaimer ?? '')

const bandColor = (color) => ({
    gold:  'var(--pt-gold)',
    navy:  'var(--pt-navy)',
    slate: '#94A3B8',
}[color] ?? '#94A3B8')

const barWidth = (dimKey) => Math.min(100, Math.max(0, dims.value[dimKey] ?? 0))

// Couleur de la jauge globale : neutre (or), jamais « bon / mauvais ».
const gaugeColor = computed(() => 'var(--pt-gold)')
</script>

<template>
    <CandidateLayout>
        <Head title="Tes résultats — PraxiFocus" />

        <div style="max-width:780px;margin:0 auto">

            <!-- ⚠️ Avertissement médical (toujours visible, en tête) -->
            <Disclaimer>
                <strong>Ceci n'est pas un diagnostic.</strong> Cet outil repère des symptômes
                à partir de l'échelle ASRS-v1.1 de l'OMS. Il ne remplace pas l'avis d'un
                professionnel de santé. Un résultat élevé n'établit pas un TDAH —
                <strong>vérifie toujours tes résultats avec un médecin, un psychiatre ou un neuropsychologue.</strong>
            </Disclaimer>

            <!-- En-tête -->
            <RestitutionHeader
                kicker="La Boussole de l'Attention"
                title="Tes repères d'attention"
                subtitle="Repérage basé sur l'échelle ASRS-v1.1 (18 items) — outil de dépistage, non un diagnostic."
            />

            <!-- Résultat du screener (Partie A) -->
            <div v-if="screener" class="pt-card ac-card-ornate"
                style="padding:1.5rem;margin-bottom:1rem;display:flex;align-items:center;gap:1.5rem;flex-wrap:wrap">
                <div style="flex-shrink:0">
                    <ScoreGauge :score="screener.score" :max="screener.max" :color="gaugeColor" :size="140" :show-max="true" />
                </div>
                <div style="flex:1;min-width:240px">
                    <span style="font-size:11px;text-transform:uppercase;letter-spacing:.05em;color:var(--pt-text-light)">
                        Screener validé — Partie A
                    </span>
                    <p style="font-size:17px;font-weight:600;color:var(--pt-text);margin-top:4px"
                        :style="{ color: screener.positive ? 'var(--pt-gold-hover)' : 'var(--pt-navy)' }">
                        {{ screener.label }}
                    </p>
                    <p style="font-size:13px;color:var(--pt-text-muted);margin-top:6px;line-height:1.6">
                        {{ screener.summary }}
                    </p>
                </div>
            </div>

            <!-- Synthèse IA -->
            <SynthesisCard v-if="result?.ai_synthesis" :source="result.ai_synthesis" title="Ta synthèse" />
            <div v-else class="pt-card" style="padding:3rem;text-align:center;margin-bottom:1rem">
                <div style="width:36px;height:36px;border-radius:50%;border:3px solid var(--pt-cream-dark);border-top-color:var(--pt-gold);animation:spin 1s linear infinite;margin:0 auto"></div>
                <p style="margin-top:1rem;color:var(--pt-text-muted)">Analyse en cours… (1 à 2 minutes)</p>
            </div>

            <!-- Profil par dimension -->
            <div class="pt-card" style="padding:1.5rem;margin-bottom:1rem">
                <div style="display:flex;align-items:baseline;justify-content:space-between;margin-bottom:1.25rem">
                    <h2 style="font-size:16px;font-weight:500">Ton profil de symptômes</h2>
                    <span style="font-size:11px;color:var(--pt-text-light);font-style:italic">
                        Fréquence rapportée, pas un score clinique
                    </span>
                </div>

                <div style="display:flex;flex-direction:column;gap:1.25rem">
                    <div v-for="(score, dimKey) in dims" :key="dimKey">
                        <div style="display:flex;align-items:center;gap:12px">
                            <span style="font-size:13px;font-weight:500;min-width:170px">
                                {{ meta[dimKey]?.label ?? dimKey }}
                            </span>
                            <svg viewBox="0 0 300 28" preserveAspectRatio="none" style="flex:1;height:28px" role="img"
                                :aria-label="`${meta[dimKey]?.label ?? dimKey} : fréquence rapportée ${Math.round(score)} sur 100`">
                                <defs>
                                    <clipPath :id="'foc-' + dimKey"><rect x="0" y="9" width="300" height="10" rx="5" /></clipPath>
                                </defs>
                                <g :clip-path="`url(#foc-${dimKey})`">
                                    <rect x="0"   y="9" width="102" height="10" fill="rgba(148,163,184,0.16)" />
                                    <rect x="102" y="9" width="99"  height="10" fill="rgba(166,117,32,0.16)" />
                                    <rect x="201" y="9" width="99"  height="10" fill="rgba(166,117,32,0.30)" />
                                </g>
                                <line x1="102" y1="6" x2="102" y2="22" stroke="var(--pt-cream)" stroke-width="1.5" />
                                <line x1="201" y1="6" x2="201" y2="22" stroke="var(--pt-cream)" stroke-width="1.5" />
                                <line :x1="Math.round(score) * 3" y1="3" :x2="Math.round(score) * 3" y2="25"
                                    style="stroke:var(--pt-navy)" stroke-width="2.5" stroke-linecap="round" />
                                <circle :cx="Math.round(score) * 3" cy="3" r="4" style="fill:var(--pt-navy)" stroke="var(--pt-cream)" stroke-width="1.5" />
                            </svg>
                            <span v-if="bands[dimKey]" style="font-size:12px;font-weight:500;flex-shrink:0;min-width:150px"
                                :style="{ color: bandColor(bands[dimKey].color) }">
                                {{ bands[dimKey].label }}
                            </span>
                        </div>
                        <p v-if="meta[dimKey]?.description"
                            style="font-size:12px;color:var(--pt-text-light);margin-top:5px;padding-left:182px;line-height:1.4">
                            {{ meta[dimKey].description }}
                        </p>
                    </div>
                </div>

                <div style="display:flex;align-items:center;gap:18px;margin-top:1.25rem;padding-left:182px;flex-wrap:wrap">
                    <span style="display:flex;align-items:center;gap:6px;font-size:11px;color:var(--pt-text-light)">
                        <span style="width:14px;height:8px;border-radius:2px;background:rgba(148,163,184,0.16)"></span>Rarement
                    </span>
                    <span style="display:flex;align-items:center;gap:6px;font-size:11px;color:var(--pt-text-light)">
                        <span style="width:14px;height:8px;border-radius:2px;background:rgba(166,117,32,0.16)"></span>Parfois
                    </span>
                    <span style="display:flex;align-items:center;gap:6px;font-size:11px;color:var(--pt-text-light)">
                        <span style="width:14px;height:8px;border-radius:2px;background:rgba(166,117,32,0.30)"></span>Souvent
                    </span>
                </div>

                <p v-if="partB" style="font-size:12px;color:var(--pt-text-muted);margin-top:1.25rem;line-height:1.5">
                    Symptômes complémentaires rapportés à fréquence élevée :
                    <strong>{{ partB.count }}/{{ partB.max }}</strong> (Partie B de l'ASRS, indicative).
                </p>
            </div>

            <!-- Que faire maintenant -->
            <div class="pt-card" style="padding:1.5rem;margin-bottom:1rem;border-left:4px solid var(--pt-navy)">
                <h2 style="font-size:16px;font-weight:500;margin-bottom:.75rem">Et maintenant ?</h2>
                <p style="font-size:14px;line-height:1.7;color:var(--pt-text);margin:0">
                    Ce repérage est un point de départ, pas une conclusion. Si tu te reconnais
                    dans ces difficultés et qu'elles gênent ton quotidien (travail, études,
                    relations), la prochaine étape est d'en parler à un professionnel de santé.
                    Lui seul peut, après un entretien approfondi, confirmer ou écarter un TDAH
                    et te proposer un accompagnement adapté.
                </p>
            </div>

            <!-- Avertissement médical (rappel en pied) -->
            <div style="font-size:11px;color:var(--pt-text-light);line-height:1.6;margin:1.5rem 0;padding:0 .5rem;text-align:center">
                {{ disclaimer }}
            </div>

            <!-- Actions -->
            <div style="display:flex;gap:.75rem;justify-content:center;margin-top:1.5rem;flex-wrap:wrap">

                <ResultPdfButton :attempt-id="attempt.id" />
                <Link :href="route('tests.index')" class="pt-btn-ghost">Voir les autres tests</Link>
                <Link :href="route('history')" class="pt-btn-primary">Mon historique →</Link>
            </div>

        </div>
    </CandidateLayout>
</template>

<style scoped>
@keyframes spin { to { transform: rotate(360deg) } }
</style>
