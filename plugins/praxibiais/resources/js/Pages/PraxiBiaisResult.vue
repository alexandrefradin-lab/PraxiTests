<script setup>
/**
 * Page résultats — PraxiBiais (biais cognitifs professionnels)
 * Redesign 2026-06-29 : structure narrative, impact en premier.
 *
 * RÈGLE : n'utiliser que les classes pt-* et les variables var(--pt-*).
 * Ne jamais hardcoder de couleurs Tailwind (bg-indigo-600, etc.).
 */
import { computed, ref } from 'vue'
import CandidateLayout from '@/Layouts/CandidateLayout.vue'
import SynthesisCard from '@/Components/SynthesisCard.vue'
import RestitutionHeader from '@/Components/RestitutionHeader.vue'
import ResultPdfButton from '@/Components/ResultPdfButton.vue'
import RadarChart from '@/Components/RadarChart.vue'
import ResultPanel from '@/Components/ResultPanel.vue'

const props = defineProps({
    attempt: Object,
    result:  Object,
})

const scoring = computed(() => props.result?.scoring ?? {})
const scores  = computed(() => scoring.value.scores ?? {})
const top3    = computed(() => scoring.value.top3 ?? [])
const profile = computed(() => scoring.value.profile ?? {})

// top3 PHP ne contient pas `details` — on les récupère depuis scores (enrichi)
const top3WithDetails = computed(() =>
    (top3.value ?? []).map(b => scores.value[b.slug] ?? b)
)

// Tous les biais triés par score décroissant
const allBiais = computed(() =>
    Object.values(scores.value).sort((a, b) => b.score - a.score)
)

// ── Radar des 10 biais (score 0–100) ────────────────────────────────────────
// Libellés courts pour la lisibilité des axes de la toile.
const RADAR_LABEL = {
    statu_quo:             'Statu quo',
    aversion_perte:        'Aversion perte',
    cout_irrecuperable:    'Coût irrécup.',
    conformite_familiale:  'Conformité',
    biais_autorite:        'Autorité',
    identite_metier:       'Identité métier',
    biais_confirmation:    'Confirmation',
    disponibilite:         'Disponibilité',
    surconfiance:          'Surconfiance',
    sous_estimation:       'Sous-estim.',
}
// Ordre fixe (catalogue) pour une toile stable d'une restitution à l'autre.
const RADAR_ORDER = [
    'statu_quo', 'aversion_perte', 'cout_irrecuperable', 'conformite_familiale',
    'biais_autorite', 'identite_metier', 'biais_confirmation', 'disponibilite',
    'surconfiance', 'sous_estimation',
]
const radarAxes = computed(() =>
    RADAR_ORDER
        .filter((slug) => scores.value[slug])
        .map((slug) => ({
            label: RADAR_LABEL[slug] ?? scores.value[slug].label,
            value: Number(scores.value[slug].score ?? 0),
        }))
)

// Les 7 biais après le top 3 (pour la section "explorer")
const rest = computed(() => allBiais.value.slice(3))

const expanded = ref(null)
const toggle   = (key) => { expanded.value = expanded.value === key ? null : key }

// ── Couleurs par niveau ────────────────────────────────────────────────────────
const levelColor = (slug) => ({
    critical: '#dc2626',
    high:     '#ea580c',
    moderate: 'var(--pt-gold)',
    low:      '#64748b',
}[slug] ?? '#64748b')

const levelBg = (slug) => ({
    critical: 'rgba(220,38,38,.12)',
    high:     'rgba(234,88,12,.12)',
    moderate: 'rgba(212,160,23,.15)',
    low:      'rgba(100,116,139,.1)',
}[slug] ?? 'rgba(100,116,139,.1)')

const barColor = (score) => {
    if (score >= 80) return '#dc2626'
    if (score >= 65) return '#ea580c'
    if (score >= 35) return 'var(--pt-gold)'
    return '#64748b'
}

// Signal strength : 1–5 segments colorés
const signalLevel = (score) => Math.ceil((score / 100) * 5)

// Emoji profil
const profileEmoji = computed(() => ({
    prisonnier_passe:      '🏰',
    suradaptation_sociale: '🪞',
    faux_rationnel:        '🔬',
    oscillant:             '⚖️',
    mixte:                 '🧩',
}[profile.value.slug] ?? '🧠'))
</script>

<template>
    <CandidateLayout>
        <Head title="Tes résultats — Le Cartographe Mental" />

        <div style="max-width:800px;margin:0 auto">

            <!-- ── OUVERTURE CHOC ───────────────────────────────────────────── -->
            <RestitutionHeader
                kicker="Le Cartographe Mental"
                title="Votre cartographie cognitive"
                subtitle="Voici la cartographie de vos freins cognitifs — les mécanismes silencieux qui influencent vos décisions professionnelles à votre insu."
            />

            <div style="text-align:center;padding:0 1rem 1.75rem">
                <h2 style="font-size:24px;font-weight:700;color:var(--pt-text);line-height:1.4">
                    En 40 questions, vous venez d'identifier<br>
                    <span style="color:var(--pt-gold)">pourquoi vous n'avancez pas aussi vite<br>
                    que vous le pourriez.</span>
                </h2>
            </div>

            <!-- ── PROFIL DOMINANT (révélation) ───────────────────────────── -->
            <div class="pt-card ac-card-ornate biais-dark" style="padding:2rem;margin-bottom:2rem;position:relative;overflow:hidden">
                <!-- bandeau gradient en haut -->
                <div style="position:absolute;top:0;left:0;right:0;height:4px;
                            background:linear-gradient(90deg,var(--pt-gold),#ea580c)"></div>

                <div style="display:flex;align-items:flex-start;gap:1.25rem">
                    <!-- Avatar profil -->
                    <div style="width:64px;height:64px;border-radius:50%;
                                background:var(--pt-surface-2);flex-shrink:0;
                                display:flex;align-items:center;justify-content:center;
                                font-size:2rem;border:2px solid var(--pt-gold)">
                        {{ profileEmoji }}
                    </div>

                    <div style="flex:1">
                        <p style="font-size:11px;text-transform:uppercase;letter-spacing:.1em;
                                  color:var(--pt-gold);font-weight:600;margin-bottom:6px">
                            Votre profil cognitif dominant
                        </p>
                        <h2 style="font-size:22px;font-weight:700;color:var(--pt-text);
                                   line-height:1.2;margin-bottom:.875rem">
                            {{ profile.label }}
                        </h2>
                        <p style="font-size:14px;color:var(--pt-text-muted);line-height:1.7;margin:0">
                            {{ profile.desc }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- ── TOILE DES 10 BIAIS (constellation) ───────────────────────── -->
            <ResultPanel label="Votre toile cognitive" class="mb-8" style="margin-bottom:2rem">
                <div class="flex justify-center">
                    <RadarChart :axes="radarAxes" dark />
                </div>
                <p class="ac-dark-def" style="text-align:center;margin-top:1rem">
                    Chaque axe situe l'intensité d'un biais (0 à 100) : plus le tracé s'étend, plus ce mécanisme pèse dans vos décisions.
                </p>
            </ResultPanel>

            <!-- ── TOP 3 FREINS (narratif + coût visible) ─────────────────── -->
            <h2 style="font-size:12px;font-weight:700;text-transform:uppercase;
                       letter-spacing:.1em;color:var(--pt-text-light);margin-bottom:1.25rem">
                Vos 3 freins décisionnels dominants
            </h2>

            <div style="display:flex;flex-direction:column;gap:1rem;margin-bottom:2.5rem">
                <div
                    v-for="(biais, i) in top3WithDetails"
                    :key="biais.slug"
                    class="pt-card biais-dark"
                    style="padding:1.5rem;cursor:pointer;
                           border-left:4px solid transparent;transition:border-color .2s"
                    :style="{ borderLeftColor: levelColor(biais.level?.slug) }"
                    @click="toggle(biais.slug)"
                >
                    <!-- En-tête : rang + label + level -->
                    <div style="display:flex;align-items:flex-start;gap:1rem;margin-bottom:1rem">
                        <div style="width:36px;height:36px;border-radius:50%;flex-shrink:0;
                                    display:flex;align-items:center;justify-content:center;
                                    font-size:14px;font-weight:800"
                             :style="{
                                 background: i === 0 ? 'var(--pt-gold)' : levelBg(biais.level?.slug),
                                 color: i === 0 ? '#fff' : levelColor(biais.level?.slug),
                             }">
                            {{ i + 1 }}
                        </div>
                        <div style="flex:1">
                            <div style="display:flex;align-items:center;gap:.5rem;
                                        flex-wrap:wrap;margin-bottom:6px">
                                <span style="font-size:16px;font-weight:700;color:var(--pt-text)">
                                    {{ biais.label }}
                                </span>
                                <span style="font-size:11px;padding:2px 8px;
                                             border-radius:999px;font-weight:600"
                                      :style="{ background: levelBg(biais.level?.slug),
                                                color: levelColor(biais.level?.slug) }">
                                    {{ biais.level?.label }}
                                </span>
                            </div>
                            <!-- Teaser : la phrase miroir -->
                            <p style="font-size:15px;color:var(--pt-text-muted);
                                      font-style:italic;line-height:1.5;margin:0">
                                "{{ biais.teaser }}"
                            </p>
                        </div>
                    </div>

                    <!-- Signal strength (5 segments) -->
                    <div style="display:flex;align-items:center;gap:.5rem;margin-bottom:1rem">
                        <span style="font-size:11px;text-transform:uppercase;letter-spacing:.06em;
                                     color:var(--pt-text-light);white-space:nowrap">
                            Intensité
                        </span>
                        <div style="display:flex;gap:3px">
                            <div v-for="n in 5" :key="n"
                                 style="width:22px;height:7px;border-radius:3px;transition:background .3s"
                                 :style="{
                                     background: n <= signalLevel(biais.score)
                                         ? levelColor(biais.level?.slug)
                                         : 'var(--pt-surface-2)'
                                 }">
                            </div>
                        </div>
                        <span style="font-size:12px;font-weight:700"
                              :style="{ color: levelColor(biais.level?.slug) }">
                            {{ biais.score }}<span style="font-weight:400;font-size:11px;color:var(--pt-text-light)">/100</span>
                        </span>
                    </div>

                    <!-- Coût invisible (toujours visible, c'est l'impact) -->
                    <div v-if="biais.details?.cout"
                         style="border-radius:8px;padding:.875rem 1rem;margin-bottom:.875rem"
                         :style="{ background: levelBg(biais.level?.slug) }">
                        <p style="font-size:11px;text-transform:uppercase;letter-spacing:.06em;
                                  color:var(--pt-text-light);margin-bottom:5px">
                            ⚠ Le coût invisible
                        </p>
                        <p style="font-size:13px;color:var(--pt-text);
                                  line-height:1.65;margin:0;font-weight:500">
                            {{ biais.details.cout }}
                        </p>
                    </div>

                    <!-- Dépliable : mécanisme + manifestation + piste d'action -->
                    <transition name="fade">
                        <div v-if="expanded === biais.slug && biais.details"
                             style="padding-top:1rem;border-top:1px solid var(--pt-border)">
                            <div style="display:grid;gap:.875rem">
                                <div>
                                    <p style="font-size:11px;text-transform:uppercase;letter-spacing:.06em;
                                              color:var(--pt-text-light);margin-bottom:4px">
                                        Ce qui se passe dans votre cerveau
                                    </p>
                                    <p style="font-size:13px;color:var(--pt-text-muted);line-height:1.65">
                                        {{ biais.details.definition }}
                                    </p>
                                </div>
                                <div>
                                    <p style="font-size:11px;text-transform:uppercase;letter-spacing:.06em;
                                              color:var(--pt-text-light);margin-bottom:4px">
                                        Comment ça se manifeste
                                    </p>
                                    <p style="font-size:13px;color:var(--pt-text-muted);line-height:1.65">
                                        {{ biais.details.manifestation }}
                                    </p>
                                </div>
                                <div style="background:rgba(212,160,23,.1);border-radius:8px;
                                            padding:.875rem 1rem;border-left:3px solid var(--pt-gold)">
                                    <p style="font-size:11px;text-transform:uppercase;letter-spacing:.06em;
                                              color:var(--pt-gold);margin-bottom:4px">
                                        ✦ Ce que vous pouvez faire maintenant
                                    </p>
                                    <p style="font-size:13px;color:var(--pt-text);line-height:1.65;margin:0">
                                        {{ biais.details.piste }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </transition>

                    <p style="font-size:12px;color:var(--pt-text-light);
                              margin-top:.75rem;text-align:right;margin-bottom:0">
                        {{ expanded === biais.slug ? '▲ Masquer' : '▼ Comprendre ce biais' }}
                    </p>
                </div>
            </div>

            <!-- ── CARTOGRAPHIE COMPLÈTE ────────────────────────────────────── -->
            <h2 style="font-size:12px;font-weight:700;text-transform:uppercase;
                       letter-spacing:.1em;color:var(--pt-text-light);margin-bottom:1.25rem">
                Cartographie de vos 10 biais
            </h2>

            <div class="pt-card biais-dark" style="padding:1.5rem;margin-bottom:2rem">
                <div style="display:flex;flex-direction:column;gap:1rem">
                    <div v-for="biais in allBiais" :key="'map-' + biais.slug">
                        <div style="display:flex;align-items:center;
                                    justify-content:space-between;margin-bottom:7px">
                            <span style="font-size:13px;font-weight:600;color:var(--pt-text)">
                                {{ biais.label }}
                            </span>
                            <div style="display:flex;align-items:center;gap:.5rem">
                                <span style="font-size:11px;padding:2px 7px;border-radius:999px"
                                      :style="{ background: levelBg(biais.level?.slug),
                                                color: levelColor(biais.level?.slug) }">
                                    {{ biais.level?.label }}
                                </span>
                                <span style="font-size:13px;font-weight:700;min-width:28px;text-align:right"
                                      :style="{ color: levelColor(biais.level?.slug) }">
                                    {{ biais.score }}
                                </span>
                            </div>
                        </div>
                        <div style="height:10px;background:var(--pt-surface-2);
                                    border-radius:5px;overflow:hidden">
                            <div style="height:100%;border-radius:5px;
                                        transition:width .8s cubic-bezier(.4,0,.2,1)"
                                 :style="{ width: biais.score + '%',
                                           background: barColor(biais.score) }">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Légende -->
                <div style="margin-top:1.25rem;padding-top:1rem;
                            border-top:1px solid var(--pt-border);
                            display:flex;flex-wrap:wrap;gap:.5rem">
                    <span v-for="(label, slug) in {
                              critical: '≥80 · Blocage majeur',
                              high:     '≥65 · Frein significatif',
                              moderate: '≥35 · Tendance active',
                              low:      '≤35 · Angle mort',
                          }"
                          :key="slug"
                          style="font-size:11px;padding:3px 9px;border-radius:999px"
                          :style="{ background: levelBg(slug), color: levelColor(slug) }">
                        {{ label }}
                    </span>
                </div>
            </div>

            <!-- ── EXPLORER LES AUTRES BIAIS (7 restants, dépliables) ─────── -->
            <h2 style="font-size:12px;font-weight:700;text-transform:uppercase;
                       letter-spacing:.1em;color:var(--pt-text-light);margin-bottom:.875rem">
                Explorer les autres biais
            </h2>

            <div style="display:flex;flex-direction:column;gap:.5rem;margin-bottom:2.5rem">
                <div v-for="biais in rest"
                     :key="'det-' + biais.slug"
                     class="pt-card biais-dark"
                     style="padding:1rem 1.25rem;cursor:pointer"
                     @click="toggle('det-' + biais.slug)">

                    <div style="display:flex;align-items:center;justify-content:space-between">
                        <div style="display:flex;align-items:center;gap:.75rem">
                            <span style="font-size:14px;font-weight:600;color:var(--pt-text)">
                                {{ biais.label }}
                            </span>
                            <span style="font-size:11px;padding:2px 7px;border-radius:999px"
                                  :style="{ background: levelBg(biais.level?.slug),
                                            color: levelColor(biais.level?.slug) }">
                                {{ biais.score }}/100
                            </span>
                        </div>
                        <span style="font-size:12px;color:var(--pt-text-light)">
                            {{ expanded === 'det-' + biais.slug ? '▲' : '▼' }}
                        </span>
                    </div>

                    <p v-if="expanded !== 'det-' + biais.slug"
                       style="font-size:12px;color:var(--pt-text-light);
                              margin:.375rem 0 0;font-style:italic">
                        {{ biais.teaser }}
                    </p>

                    <transition name="fade">
                        <div v-if="expanded === 'det-' + biais.slug && biais.details"
                             style="margin-top:1rem;padding-top:1rem;
                                    border-top:1px solid var(--pt-border)">
                            <div style="display:grid;gap:.875rem">
                                <div>
                                    <p style="font-size:11px;text-transform:uppercase;letter-spacing:.06em;
                                              color:var(--pt-text-light);margin-bottom:3px">
                                        Mécanisme
                                    </p>
                                    <p style="font-size:13px;color:var(--pt-text-muted);line-height:1.65">
                                        {{ biais.details.definition }}
                                    </p>
                                </div>
                                <div>
                                    <p style="font-size:11px;text-transform:uppercase;letter-spacing:.06em;
                                              color:var(--pt-text-light);margin-bottom:3px">
                                        Coût invisible
                                    </p>
                                    <p style="font-size:13px;color:var(--pt-text-muted);line-height:1.65">
                                        {{ biais.details.cout }}
                                    </p>
                                </div>
                                <div style="background:rgba(212,160,23,.1);border-radius:8px;
                                            padding:.75rem 1rem">
                                    <p style="font-size:11px;text-transform:uppercase;letter-spacing:.06em;
                                              color:var(--pt-gold);margin-bottom:3px">
                                        ✦ Piste
                                    </p>
                                    <p style="font-size:13px;color:var(--pt-text);line-height:1.65;margin:0">
                                        {{ biais.details.piste }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </transition>
                </div>
            </div>

            <!-- ── SYNTHÈSE IA ──────────────────────────────────────────────── -->
            <SynthesisCard v-if="result?.ai_synthesis" :source="result.ai_synthesis" title="Ta synthèse" />
            <div v-else class="pt-card ac-card-dark" style="padding:3rem;text-align:center;margin-bottom:1rem">
                <div style="width:36px;height:36px;border-radius:50%;border:3px solid var(--pt-cream-dark);border-top-color:var(--pt-gold);animation:spin 1s linear infinite;margin:0 auto"></div>
                <p style="margin-top:1rem;color:var(--pt-text-muted)">Analyse en cours… (1 à 2 minutes)</p>
            </div>

            <ResultPdfButton :attempt-id="attempt.id" />

        </div>
    </CandidateLayout>
</template>

<style scoped>
@keyframes spin { to { transform: rotate(360deg) } }
.fade-enter-active,
.fade-leave-active { transition: opacity .2s, transform .2s; }
.fade-enter-from,
.fade-leave-to     { opacity: 0; transform: translateY(-4px); }

/* ── Cartes en mode sombre « constellation » (bascule les tokens --pt-* pour
   tout le contenu inline, sans réécrire chaque style) ── */
.biais-dark {
    background: radial-gradient(ellipse at 50% 0%, #241a0e 0%, var(--color-accent) 62%, #120c04 100%) !important;
    border: 1px solid var(--color-primary-dark) !important;
    box-shadow: 0 10px 26px rgba(42,30,8,0.28), inset 0 1px 0 rgba(166,117,32,0.20) !important;
    /* Redéfinition locale des tokens : tout le texte inline devient clair */
    --pt-text:       #F4ECD8;
    --pt-text-muted: rgba(240,232,212,0.72);
    --pt-text-light: rgba(240,232,212,0.55);
    --pt-surface:    rgba(0,0,0,0.20);
    --pt-surface-2:  rgba(240,232,212,0.14);
    --pt-border:     rgba(230,190,90,0.22);
}
</style>
