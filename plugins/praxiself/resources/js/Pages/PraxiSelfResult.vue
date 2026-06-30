<script setup>
import { computed } from 'vue'
import CandidateLayout from '@/Layouts/CandidateLayout.vue'
import ScoreGauge from '@/Components/ScoreGauge.vue'
import RadarChart from '@/Components/RadarChart.vue'
import SynthesisCard from '@/Components/SynthesisCard.vue'
import RestitutionHeader from '@/Components/RestitutionHeader.vue'
import ResultPanel from '@/Components/ResultPanel.vue'

const props = defineProps({
    attempt:        Object,
    result:         Object,
    journeyDays:    { type: Array,  default: () => [] },   // jours complétés [1,2,3,…]
    journeyStreak:  { type: Number, default: 0 },
    journeyToday:   { type: Object, default: null },        // entrée Journey du jour courant
    journeyPhase:   { type: String, default: 'decouverte' },
    journeyPhaseMeta: { type: Object, default: () => ({}) },
})

const scoring = computed(() => props.result?.scoring ?? {})
const globalScore       = computed(() => scoring.value.global_score ?? 0)
const dimensions        = computed(() => scoring.value.dimensions ?? {})
const dimensionsMeta    = computed(() => scoring.value.dimensions_meta ?? {})
const profile           = computed(() => scoring.value.profile ?? {})
const weakestDimension  = computed(() => scoring.value.weakest_dimension ?? '')
const recommended       = computed(() => scoring.value.recommended_exercises ?? [])

/** Barre de progression : largeur en % clampée à [0,100] */
const barWidth = (score) => Math.min(100, Math.max(0, score)) + '%'

/** Couleur CSS selon la valeur du score */
const scoreColor = (score) => {
    if (score >= 75) return 'var(--pt-navy)'
    if (score >= 50) return 'var(--pt-gold)'
    return '#ef4444'
}

/** Libellé de difficulté */
const difficultyLabel = (level) => {
    const map = { 1: 'Débutant', 2: 'Intermédiaire', 3: 'Avancé' }
    return map[level] ?? ''
}

/** Classe badge de difficulté */
const difficultyClass = (level) => {
    const map = { 1: 'badge-easy', 2: 'badge-mid', 3: 'badge-hard' }
    return map[level] ?? ''
}

/** Abréger les instructions longues pour la carte exercice */
const excerpt = (text, maxLen = 160) => {
    if (!text) return ''
    const plain = text.replace(/\n/g, ' ').trim()
    return plain.length <= maxLen ? plain : plain.slice(0, maxLen) + '…'
}

/** Axes du radar — dimensions déjà normalisées sur 0–100 (cf. moteur de scoring) */
const radarAxes = computed(() =>
    Object.entries(dimensions.value).map(([key, score]) => ({
        label: dimensionsMeta.value[key]?.label ?? key,
        value: Math.round(Number(score) || 0),
        color: dimensionsMeta.value[key]?.color,
    }))
)

const profileLevel  = computed(() => profile.value.level ?? 3)
const progressStars = computed(() => Array.from({ length: 5 }, (_, i) => i < profileLevel.value))

// ─── Parcours 60 jours ───────────────────────────────────────────────────────

/** Ensemble des jours complétés pour O(1) lookup */
const completedSet = computed(() => new Set(props.journeyDays))

/** Jour courant du parcours (1–60) */
const currentDay = computed(() => {
    if (!props.journeyToday) return 1
    return props.journeyToday.day ?? 1
})

/** Couleur d'une phase */
const phaseColor = (phase) => {
    const map = {
        decouverte:   'var(--pt-navy)',
        installation: 'var(--pt-gold)',
        renforcement: '#2563eb',
        maitrise:     '#7c3aed',
    }
    return map[phase] ?? 'var(--pt-navy)'
}

/** Libellé d'une phase */
const phaseLabel = (phase) => {
    const map = {
        decouverte:   'Découverte',
        installation: 'Installation',
        renforcement: 'Renforcement',
        maitrise:     'Maîtrise',
    }
    return map[phase] ?? phase
}

/** Phase d'un numéro de jour (1-60) */
const dayPhase = (day) => {
    if (day <= 15) return 'decouverte'
    if (day <= 30) return 'installation'
    if (day <= 45) return 'renforcement'
    return 'maitrise'
}

/** Progression de la phase courante en % */
const phaseProgress = computed(() => {
    const total = props.journeyDays.length
    if (total === 0) return 0
    const phase = props.journeyPhase
    const ranges = {
        decouverte:   [1, 15],
        installation: [16, 30],
        renforcement: [31, 45],
        maitrise:     [46, 60],
    }
    const [start, end] = ranges[phase] ?? [1, 15]
    const doneInPhase = props.journeyDays.filter(d => d >= start && d <= end).length
    return Math.round((doneInPhase / (end - start + 1)) * 100)
})

/** Progression globale en % */
const globalProgress = computed(() => Math.round((props.journeyDays.length / 60) * 100))
</script>

<template>
    <CandidateLayout>
        <Head title="Tes résultats — PraxiSelf" />

        <div class="max-w-4xl mx-auto px-4 pb-16">

            <!-- ══════════════════════════════════════════════
                 SECTION 1 — EN-TÊTE & SCORE GLOBAL
            ══════════════════════════════════════════════ -->
            <RestitutionHeader
                kicker="PraxiSelf · Affirmation de soi"
                title="Ton profil d'assertivité"
                subtitle="Basé sur 5 dimensions — Communication Non-Violente · Bandura · Alberti & Emmons"
            />

            <header class="text-center mb-12">
                <!-- Score global circulaire (SVG) -->
                <div class="flex flex-col items-center mt-8">
                    <ScoreGauge :score="globalScore" :color="scoreColor(globalScore)" :size="140" />

                    <!-- Niveau en étoiles -->
                    <div class="flex gap-1 mt-3">
                        <span v-for="(filled, i) in progressStars" :key="i"
                              class="text-xl"
                              :style="{ opacity: filled ? 1 : 0.25 }">
                            ★
                        </span>
                    </div>

                    <!-- Label du profil -->
                    <div v-if="profile.label" class="mt-4">
                        <span class="text-2xl mr-2">{{ profile.emoji }}</span>
                        <span class="text-xl font-semibold" style="color: var(--pt-navy)">
                            {{ profile.label }}
                        </span>
                        <p class="mt-2 text-sm max-w-lg mx-auto" style="color: var(--pt-text-muted)">
                            {{ profile.summary }}
                        </p>
                    </div>
                </div>
            </header>

            <!-- ══════════════════════════════════════════════
                 SYNTHÈSE IA (si disponible)
            ══════════════════════════════════════════════ -->
            <SynthesisCard :source="attempt.result?.ai_synthesis" title="Synthèse personnalisée" />

            <!-- ══════════════════════════════════════════════
                 PROFIL EN UN COUP D'ŒIL — TOILE D'ARAIGNÉE
            ══════════════════════════════════════════════ -->
            <ResultPanel v-if="radarAxes.length >= 3" label="Ton profil en un coup d'œil" class="mb-8">
                <div class="flex justify-center">
                    <RadarChart :axes="radarAxes" dark />
                </div>
            </ResultPanel>

            <!-- ══════════════════════════════════════════════
                 SECTION 2 — RADAR DES 5 DIMENSIONS (barres)
            ══════════════════════════════════════════════ -->
            <section class="pt-card p-8 mb-8">
                <h2 class="text-xl font-semibold mb-6" style="color: var(--pt-navy)">
                    Profil radar — 5 dimensions
                </h2>

                <div class="space-y-6">
                    <div v-for="(score, key) in dimensions" :key="key">
                        <div class="flex items-center justify-between mb-1 gap-4">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2">
                                    <span class="text-base">{{ dimensionsMeta[key]?.icon ?? '•' }}</span>
                                    <span class="font-semibold text-sm truncate"
                                          :style="{ color: dimensionsMeta[key]?.color ?? 'var(--pt-navy)' }">
                                        {{ dimensionsMeta[key]?.label ?? key }}
                                    </span>
                                    <span v-if="key === weakestDimension"
                                          class="text-xs px-2 py-0.5 rounded-full"
                                          style="background:rgba(239,68,68,0.1);color:#ef4444;white-space:nowrap">
                                        À renforcer
                                    </span>
                                </div>
                                <p class="text-xs mt-0.5 truncate"
                                   style="color: var(--pt-text-muted)">
                                    {{ dimensionsMeta[key]?.desc }}
                                </p>
                            </div>
                            <span class="text-sm font-bold flex-shrink-0 w-12 text-right"
                                  :style="{ color: scoreColor(score) }">
                                {{ score }}%
                            </span>
                        </div>

                        <!-- Barre de progression -->
                        <div class="h-2.5 rounded-full overflow-hidden"
                             style="background: var(--pt-cream)">
                            <div class="h-full rounded-full transition-all duration-700"
                                 :style="{
                                     width: barWidth(score),
                                     backgroundColor: dimensionsMeta[key]?.color ?? 'var(--pt-navy)'
                                 }">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Légende des zones -->
                <div class="flex flex-wrap gap-4 mt-6 pt-4"
                     style="border-top: 1px solid var(--pt-cream)">
                    <div v-for="item in [
                        { color: 'var(--pt-navy)', label: '≥ 75 — Solide' },
                        { color: 'var(--pt-gold)',  label: '50–74 — En progression' },
                        { color: '#ef4444',          label: '< 50 — À travailler' }
                    ]" :key="item.label" class="flex items-center gap-1.5 text-xs"
                        style="color: var(--pt-text-muted)">
                        <span class="inline-block w-3 h-3 rounded-full flex-shrink-0"
                              :style="{ backgroundColor: item.color }"></span>
                        {{ item.label }}
                    </div>
                </div>
            </section>

            <!-- ══════════════════════════════════════════════
                 SECTION 3 — EXERCICES RECOMMANDÉS
            ══════════════════════════════════════════════ -->
            <section v-if="recommended.length" class="mb-8">
                <div class="flex items-end justify-between mb-4">
                    <div>
                        <h2 class="text-xl font-semibold" style="color: var(--pt-navy)">
                            Exercices recommandés pour toi
                        </h2>
                        <p v-if="dimensionsMeta[weakestDimension]" class="text-sm mt-0.5"
                           style="color: var(--pt-text-muted)">
                            Focalisés sur :
                            <strong>{{ dimensionsMeta[weakestDimension]?.label }}</strong>
                        </p>
                    </div>
                    <span class="text-xs px-3 py-1 rounded-full"
                          style="background: var(--pt-cream); color: var(--pt-navy); white-space:nowrap">
                        {{ recommended.length }} exercice{{ recommended.length > 1 ? 's' : '' }}
                    </span>
                </div>

                <div class="grid md:grid-cols-3 gap-5">
                    <article v-for="ex in recommended" :key="ex.id"
                             class="pt-card p-5 flex flex-col gap-3 hover:shadow-md transition-shadow">

                        <!-- Catégorie + difficulté -->
                        <div class="flex items-center justify-between">
                            <span class="text-xs uppercase tracking-wider font-medium"
                                  style="color: var(--pt-text-muted)">
                                {{ ex.category }}
                            </span>
                            <span :class="['text-xs font-medium px-2 py-0.5 rounded-full', difficultyClass(ex.difficulty)]"
                                  :style="{
                                      background: ex.difficulty === 1 ? 'rgba(22,163,74,0.1)' : ex.difficulty === 2 ? 'rgba(234,88,12,0.1)' : 'rgba(124,58,237,0.1)',
                                      color: ex.difficulty === 1 ? '#16a34a' : ex.difficulty === 2 ? '#ea580c' : '#7c3aed'
                                  }">
                                {{ difficultyLabel(ex.difficulty) }}
                            </span>
                        </div>

                        <!-- Titre -->
                        <h3 class="font-semibold text-sm leading-snug"
                            style="color: var(--pt-navy)">
                            {{ ex.title }}
                        </h3>

                        <!-- Extrait instructions -->
                        <p class="text-xs leading-relaxed flex-1"
                           style="color: var(--pt-text-muted)">
                            {{ excerpt(ex.instructions) }}
                        </p>

                        <!-- Durée + base scientifique -->
                        <div class="mt-auto pt-3 space-y-1"
                             style="border-top: 1px solid var(--pt-cream)">
                            <div class="flex items-center gap-1.5 text-xs"
                                 style="color: var(--pt-text-muted)">
                                <span>⏱</span>
                                <span>{{ ex.duration_minutes }} min</span>
                            </div>
                            <div class="flex items-start gap-1.5 text-xs"
                                 style="color: var(--pt-text-muted)">
                                <span class="flex-shrink-0">📚</span>
                                <span class="italic">{{ ex.scientific_basis }}</span>
                            </div>
                        </div>
                    </article>
                </div>
            </section>

            <!-- ══════════════════════════════════════════════
                 SECTION 4 — MESSAGE DE PROGRESSION
            ══════════════════════════════════════════════ -->
            <section class="pt-card p-8 mb-8 text-center"
                     style="background: linear-gradient(135deg, var(--pt-navy) 0%, #1e3a5f 100%);">
                <div class="text-4xl mb-4">{{ profile.emoji ?? '🌱' }}</div>
                <h2 class="text-xl font-semibold text-white mb-3">
                    Ton chemin vers l'affirmation de soi
                </h2>
                <p class="text-sm max-w-lg mx-auto mb-6 leading-relaxed"
                   style="color: rgba(255,255,255,0.8)">
                    L'assertivité n'est pas un trait de personnalité figé — c'est une compétence qui se
                    développe avec la pratique. Chaque exercice réalisé renforce de vraies connexions
                    neurales. La régularité prime sur l'intensité.
                </p>
                <div class="grid grid-cols-3 gap-4 text-white text-center">
                    <div class="rounded-xl p-4" style="background: rgba(255,255,255,0.1)">
                        <div class="text-2xl font-bold" style="color: var(--pt-gold)">7j</div>
                        <div class="text-xs mt-1" style="color: rgba(255,255,255,0.7)">pour ancrer une habitude</div>
                    </div>
                    <div class="rounded-xl p-4" style="background: rgba(255,255,255,0.1)">
                        <div class="text-2xl font-bold" style="color: var(--pt-gold)">5 min</div>
                        <div class="text-xs mt-1" style="color: rgba(255,255,255,0.7)">par exercice suffisent</div>
                    </div>
                    <div class="rounded-xl p-4" style="background: rgba(255,255,255,0.1)">
                        <div class="text-2xl font-bold" style="color: var(--pt-gold)">21j</div>
                        <div class="text-xs mt-1" style="color: rgba(255,255,255,0.7)">pour un changement durable</div>
                    </div>
                </div>
            </section>

            <!-- ══════════════════════════════════════════════
                 SECTION 5 — PARCOURS 60 JOURS
            ══════════════════════════════════════════════ -->
            <section class="pt-card p-8 mb-8">

                <!-- En-tête de section -->
                <div class="flex flex-wrap items-start justify-between gap-4 mb-6">
                    <div>
                        <h2 class="text-xl font-semibold" style="color: var(--pt-navy)">
                            Mon parcours 60 jours
                        </h2>
                        <p class="text-xs mt-1" style="color: var(--pt-text-muted)">
                            Phillippa Lally (UCL) · BJ Fogg · James Clear — 1 exercice · 5–10 min · chaque jour
                        </p>
                    </div>

                    <!-- Streak -->
                    <div class="flex items-center gap-2 px-4 py-2 rounded-xl"
                         style="background: var(--pt-cream)">
                        <span class="text-xl">🔥</span>
                        <div>
                            <div class="text-lg font-bold leading-none" style="color: var(--pt-navy)">
                                {{ journeyStreak }}
                            </div>
                            <div class="text-xs" style="color: var(--pt-text-muted)">jour{{ journeyStreak !== 1 ? 's' : '' }} de suite</div>
                        </div>
                    </div>
                </div>

                <!-- Phase courante + barre de progression -->
                <div class="mb-6 p-4 rounded-xl" style="background: var(--pt-cream)">
                    <div class="flex items-center justify-between mb-2">
                        <div class="flex items-center gap-2">
                            <span class="inline-block w-3 h-3 rounded-full flex-shrink-0"
                                  :style="{ backgroundColor: phaseColor(journeyPhase) }"></span>
                            <span class="text-sm font-semibold" :style="{ color: phaseColor(journeyPhase) }">
                                Phase — {{ phaseLabel(journeyPhase) }}
                            </span>
                        </div>
                        <span class="text-xs font-medium" style="color: var(--pt-text-muted)">
                            {{ journeyDays.length }} / 60 jours
                        </span>
                    </div>

                    <!-- Barre phase -->
                    <div class="h-2 rounded-full overflow-hidden mb-1" style="background: rgba(0,0,0,0.08)">
                        <div class="h-full rounded-full transition-all duration-700"
                             :style="{
                                 width: phaseProgress + '%',
                                 backgroundColor: phaseColor(journeyPhase)
                             }">
                        </div>
                    </div>
                    <div class="flex justify-between text-xs" style="color: var(--pt-text-muted)">
                        <span>Phase : {{ phaseProgress }}%</span>
                        <span>Global : {{ globalProgress }}%</span>
                    </div>
                </div>

                <!-- Grille 60 cases — 10 colonnes × 6 lignes -->
                <div style="display:grid; grid-template-columns: repeat(10, 1fr); gap: 6px; margin-bottom: 1.5rem;">
                    <div
                        v-for="day in 60"
                        :key="day"
                        :title="`Jour ${day}`"
                        style="aspect-ratio:1; border-radius: 6px; display:flex; align-items:center; justify-content:center; font-size:0.6rem; font-weight:600; transition: transform 0.15s ease, box-shadow 0.15s ease; cursor:default;"
                        :style="{
                            backgroundColor: completedSet.has(day)
                                ? phaseColor(dayPhase(day))
                                : day === currentDay
                                    ? 'transparent'
                                    : 'var(--pt-cream)',
                            color: completedSet.has(day)
                                ? 'white'
                                : day === currentDay
                                    ? phaseColor(dayPhase(day))
                                    : 'var(--pt-text-muted)',
                            border: day === currentDay
                                ? '2px solid ' + phaseColor(dayPhase(day))
                                : '2px solid transparent',
                            boxShadow: day === currentDay
                                ? '0 0 0 3px ' + phaseColor(dayPhase(day)) + '33'
                                : 'none',
                            transform: day === currentDay ? 'scale(1.12)' : 'scale(1)',
                        }"
                    >
                        {{ completedSet.has(day) ? '✓' : day }}
                    </div>
                </div>

                <!-- Légende grille -->
                <div class="flex flex-wrap gap-4 mb-6 text-xs" style="color: var(--pt-text-muted)">
                    <div v-for="p in ['decouverte','installation','renforcement','maitrise']" :key="p"
                         class="flex items-center gap-1.5">
                        <span class="inline-block w-3 h-3 rounded flex-shrink-0"
                              :style="{ backgroundColor: phaseColor(p) }"></span>
                        {{ phaseLabel(p) }}
                    </div>
                    <div class="flex items-center gap-1.5">
                        <span class="inline-block w-3 h-3 rounded flex-shrink-0"
                              style="background:var(--pt-cream)"></span>
                        Non fait
                    </div>
                    <div class="flex items-center gap-1.5">
                        <span class="inline-block w-3 h-3 rounded flex-shrink-0"
                              style="background:transparent;border:2px solid var(--pt-navy)"></span>
                        Aujourd'hui
                    </div>
                </div>

                <!-- Exercice du jour -->
                <div v-if="journeyToday" class="rounded-xl p-5"
                     :style="{
                         background: phaseColor(journeyToday.phase) + '10',
                         border: '1px solid ' + phaseColor(journeyToday.phase) + '30',
                     }">

                    <div class="flex items-start justify-between gap-4 mb-3">
                        <div>
                            <div class="flex items-center gap-2 mb-1">
                                <span class="text-xs font-semibold px-2 py-0.5 rounded-full text-white"
                                      :style="{ backgroundColor: phaseColor(journeyToday.phase) }">
                                    Jour {{ journeyToday.day }}
                                </span>
                                <span class="text-xs" style="color: var(--pt-text-muted)">
                                    Semaine {{ journeyToday.week }} · {{ journeyToday.weekly_theme }}
                                </span>
                            </div>
                            <h3 class="text-base font-semibold" style="color: var(--pt-navy)">
                                {{ journeyToday.title }}
                            </h3>
                        </div>
                        <div class="text-right flex-shrink-0">
                            <div class="text-xl font-bold" :style="{ color: phaseColor(journeyToday.phase) }">
                                {{ journeyToday.duration_minutes }} min
                            </div>
                        </div>
                    </div>

                    <!-- Intention du jour -->
                    <div class="flex items-start gap-2 mb-3">
                        <span class="flex-shrink-0 text-base">💡</span>
                        <p class="text-sm italic" style="color: var(--pt-navy)">
                            {{ journeyToday.intention }}
                        </p>
                    </div>

                    <!-- Anchor + micro-habit -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-3 text-xs" style="color: var(--pt-text-muted)">
                        <div class="flex items-start gap-1.5">
                            <span class="flex-shrink-0">⚓</span>
                            <span><strong>Ancre :</strong> {{ journeyToday.anchor }}</span>
                        </div>
                        <div class="flex items-start gap-1.5">
                            <span class="flex-shrink-0">🎯</span>
                            <span><strong>Action :</strong> {{ journeyToday.micro_habit }}</span>
                        </div>
                    </div>

                    <!-- Récompense -->
                    <div class="flex items-start gap-1.5 mb-4 text-xs" style="color: var(--pt-text-muted)">
                        <span class="flex-shrink-0">🏅</span>
                        <span><strong>Récompense :</strong> {{ journeyToday.reward }}</span>
                    </div>

                    <!-- Science du jour -->
                    <div class="flex items-start gap-1.5 mb-5 text-xs italic"
                         style="color: var(--pt-text-muted); border-left: 2px solid var(--pt-cream); padding-left: 0.75rem;">
                        <span>{{ journeyToday.tip_science }}</span>
                    </div>

                    <!-- Bouton Commencer -->
                    <div class="flex justify-end">
                        <a :href="route('tests.index')"
                           class="pt-btn-primary"
                           :style="{ backgroundColor: phaseColor(journeyToday.phase) }">
                            Commencer l'exercice →
                        </a>
                    </div>
                </div>

                <!-- État vide : parcours non démarré -->
                <div v-else class="text-center py-6" style="color: var(--pt-text-muted)">
                    <div class="text-3xl mb-3">🌱</div>
                    <p class="text-sm font-medium mb-1" style="color: var(--pt-navy)">Votre parcours n'a pas encore commencé.</p>
                    <p class="text-xs">Complétez votre premier exercice pour démarrer les 60 jours.</p>
                    <a :href="route('tests.index')"
                       class="pt-btn-primary mt-4 inline-block">
                        Démarrer le Jour 1
                    </a>
                </div>

            </section>

            <!-- ══════════════════════════════════════════════
                 ACTIONS
            ══════════════════════════════════════════════ -->
            <div class="flex flex-col sm:flex-row items-center justify-center gap-4 mt-8">
                <a :href="route('results.pdf', attempt.id)" class="pt-btn-primary">
                    Télécharger mes résultats PDF
                </a>
                <a :href="route('tests.index')"
                   class="text-sm font-medium"
                   style="color: var(--pt-navy)">
                    ← Retour au tableau de bord
                </a>
            </div>

        </div>
    </CandidateLayout>
</template>
