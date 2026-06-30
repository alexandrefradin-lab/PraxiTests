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
import { Link, Head } from '@inertiajs/vue3'
import CandidateLayout from '@/Layouts/CandidateLayout.vue'
import RadarChart      from '@/Components/RadarChart.vue'
import SynthesisCard from '@/Components/SynthesisCard.vue'
import JobCard       from '@/Components/JobCard.vue'
import RestitutionHeader from '@/Components/RestitutionHeader.vue'
import ResultPanel from '@/Components/ResultPanel.vue'

const props = defineProps({
    attempt:      Object,
    result:       Object,
    panel360:     Object,   // { manage_url, started, aggregate } — feedback 360° multi-évaluateurs
})

const agg        = computed(() => props.panel360?.aggregate ?? null)
const has360     = computed(() => !!agg.value?.available)
const gapColor   = (g) => g <= -10 ? '#7B1515' : (g >= 10 ? '#3A6B48' : 'var(--pt-text-light)')

const scoring      = computed(() => props.result?.scoring ?? {})
const dims         = computed(() => scoring.value.dimensions   ?? {})
const normScores   = computed(() => scoring.value.norm_scores  ?? {})
const meta         = computed(() => scoring.value.meta         ?? {})
const global_score = computed(() => scoring.value.global_score ?? null)
const strengths    = computed(() => scoring.value.strengths    ?? [])
const improvements = computed(() => scoring.value.improvements ?? [])

const labelOf = (dimKey) => meta.value[dimKey]?.label ?? dimKey

// Axes de la toile d'araignée — dimensions normalisées 0..100, ordre du meta conservé
const radarAxes = computed(() =>
    Object.entries(dims.value).map(([key, value]) => {
        const axis = { label: labelOf(key), value: value ?? 0 }
        if (meta.value[key]?.color) axis.color = meta.value[key].color
        return axis
    })
)

const dotColor = (color) => ({
    gold:  'var(--pt-gold)',
    navy:  'var(--pt-navy)',
    slate: '#94A3B8',
    amber: '#F59E0B',
    muted: 'var(--pt-cream-dark)',
}[color] ?? '#94A3B8')

const barWidth = (dimKey) => {
    const norm = normScores.value[dimKey]
    if (norm?.percentile) return Math.min(100, Math.max(0, Math.round((norm.percentile / 99) * 100)))
    return Math.min(100, Math.max(0, Math.round(dims.value[dimKey] ?? 0)))
}
</script>

<template>
    <CandidateLayout>
        <Head :title="`Vos résultats — Praxis 360`" />

        <div style="max-width:780px;margin:0 auto">

            <!-- En-tête -->
            <RestitutionHeader
                kicker="Praxis 360 — Soft skills"
                title="Votre profil soft skills"
                subtitle="Auto-évaluation sur 6 dimensions, analysée par notre IA."
            />

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
            <SynthesisCard v-if="result?.ai_synthesis" :source="result.ai_synthesis" title="Votre synthèse" />

            <!-- En attente IA -->
            <div v-else class="pt-card" style="padding:3rem;text-align:center;margin-bottom:1rem">
                <div style="width:36px;height:36px;border-radius:50%;border:3px solid var(--pt-cream-dark);border-top-color:var(--pt-gold);animation:spin 1s linear infinite;margin:0 auto"></div>
                <p style="margin-top:1rem;color:var(--pt-text-muted)">Analyse en cours… (1 à 2 minutes)</p>
            </div>

            <!-- Profil en un coup d'œil — toile d'araignée -->
            <ResultPanel v-if="radarAxes.length >= 3" label="Ton profil en un coup d'œil" class="mb-8">
                <div style="display:flex;justify-content:center">
                    <RadarChart :axes="radarAxes" dark />
                </div>
            </ResultPanel>

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

            <!-- ════════ FEEDBACK 360° — regards croisés ════════ -->
            <div v-if="panel360" class="pt-card" style="padding:1.5rem;margin-bottom:1rem;border:1px solid var(--pt-gold-border)">
                <div style="display:flex;align-items:baseline;justify-content:space-between;margin-bottom:1rem;flex-wrap:wrap;gap:8px">
                    <h2 style="font-size:16px;font-weight:500">Le regard des autres — 360°</h2>
                    <a :href="panel360.manage_url" class="pt-btn-ghost text-sm">
                        {{ panel360.started ? 'Gérer mes évaluateurs' : 'Lancer mon 360°' }} →
                    </a>
                </div>

                <!-- Pas encore lancé -->
                <p v-if="!panel360.started" style="font-size:14px;color:var(--pt-text-muted);line-height:1.6">
                    Jusqu'ici, vous vous êtes auto-évalué. Le vrai pouvoir d'un 360°, c'est de confronter
                    cette perception au regard de votre <strong>manager</strong>, de vos <strong>pairs</strong> et de vos
                    <strong>collaborateurs</strong>. Invitez-les : leurs réponses sont anonymes.
                </p>

                <!-- Lancé mais seuil non atteint -->
                <p v-else-if="!has360" style="font-size:14px;color:var(--pt-text-muted);line-height:1.6">
                    {{ agg?.message || 'En attente de réponses des évaluateurs.' }}
                    <br><span style="font-size:12.5px;color:var(--pt-text-light)">
                        Réponses reçues : {{ agg?.counts?.total ?? 0 }} / {{ agg?.counts?.invited ?? 0 }} invité(s).
                    </span>
                </p>

                <!-- Résultats 360 disponibles -->
                <div v-else>
                    <p style="font-size:12.5px;color:var(--pt-text-light);margin-bottom:1rem">
                        Basé sur {{ agg.counts.total }} évaluateur(s). Comparaison de votre auto-perception (◆) à la moyenne des autres (▮).
                    </p>

                    <!-- Self vs autres par dimension -->
                    <div style="display:flex;flex-direction:column;gap:.9rem;margin-bottom:1.25rem">
                        <div v-for="(label, dimKey) in agg.meta" :key="dimKey">
                            <div style="display:flex;align-items:center;gap:12px">
                                <span style="font-size:13px;font-weight:500;min-width:180px">{{ label.label || dimKey }}</span>
                                <div class="pt-progress-track" style="flex:1;position:relative">
                                    <div class="pt-progress-fill" :style="{ width: (agg.others[dimKey] ?? 0) + '%' }"></div>
                                    <!-- marqueur auto-évaluation -->
                                    <div v-if="agg.self[dimKey] != null"
                                         :style="{ position:'absolute', top:'-3px', left: 'calc(' + agg.self[dimKey] + '% - 6px)', color:'var(--pt-navy)', fontSize:'12px' }">◆</div>
                                </div>
                                <span style="font-size:12px;min-width:120px;text-align:right"
                                      :style="{ color: gapColor((agg.others[dimKey] ?? 0) - (agg.self[dimKey] ?? 0)) }">
                                    autres {{ agg.others[dimKey] ?? '—' }} · soi {{ agg.self[dimKey] ?? '—' }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Angles morts -->
                    <div v-if="agg.blind_spots?.length" style="margin-bottom:1rem">
                        <h3 style="font-size:13px;font-weight:600;margin-bottom:.5rem">Angles morts &amp; forces cachées</h3>
                        <div v-for="bs in agg.blind_spots" :key="bs.dimension"
                             style="font-size:13px;line-height:1.5;padding:8px 12px;border-radius:8px;background:var(--pt-cream);margin-bottom:6px">
                            <strong>{{ bs.label }}</strong> —
                            <span v-if="bs.type === 'angle_mort'">vous vous percevez plus haut ({{ bs.self }}) que les autres ne vous voient ({{ bs.others }}).</span>
                            <span v-else>les autres vous voient plus haut ({{ bs.others }}) que vous ne vous percevez ({{ bs.self }}) : une force cachée.</span>
                        </div>
                    </div>

                    <!-- Groupes (≥ seuil) -->
                    <div v-if="agg.groups?.length" style="font-size:12px;color:var(--pt-text-light)">
                        Détail par groupe : <span v-for="(g, i) in agg.groups" :key="g.relation">{{ g.label }} ({{ g.count }}){{ i < agg.groups.length - 1 ? ' · ' : '' }}</span>
                    </div>

                    <!-- Verbatims -->
                    <div v-if="agg.verbatims" style="margin-top:1rem">
                        <template v-for="(list, key) in agg.verbatims" :key="key">
                            <div v-if="list.length" style="margin-bottom:.75rem">
                                <h4 style="font-size:12px;text-transform:uppercase;letter-spacing:.05em;color:var(--pt-text-light);margin-bottom:4px">
                                    {{ key === 'strength' ? 'Points forts' : key === 'growth' ? 'Axes de progrès' : 'Conseils' }}
                                </h4>
                                <p v-for="(v, i) in list" :key="i"
                                   style="font-size:13px;color:var(--pt-text-muted);line-height:1.5;border-left:2px solid var(--pt-gold-border);padding-left:10px;margin:4px 0">
                                    « {{ v }} »
                                </p>
                            </div>
                        </template>
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

@media (max-width: 640px) {
    .p360-hero {
        flex-direction: column;
        text-align: center;
        gap: 1rem !important;
        padding: 1.5rem !important;
    }
}

</style>
