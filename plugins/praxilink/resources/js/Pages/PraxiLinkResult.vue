<template>
  <CandidateLayout :title="pageTitle">
    <div class="pt-result-wrapper">

      <!-- ══════════════════════════════════════════════
           EN-TÊTE RÉSULTAT
      ══════════════════════════════════════════════ -->
      <header class="pt-result-header">
        <div class="pt-result-header__icon" aria-hidden="true">💬</div>
        <h1 class="pt-result-header__title">Vos résultats de communication</h1>
        <p class="pt-result-header__subtitle">
          {{ attempt?.test?.name ?? 'PraxiLink — Communication assertive' }}
        </p>
      </header>

      <!-- ══════════════════════════════════════════════
           SCORE GLOBAL
      ══════════════════════════════════════════════ -->
      <section class="pt-card pt-global-score" aria-labelledby="global-score-heading">
        <h2 id="global-score-heading" class="pt-section-title">Score de communication global</h2>

        <div class="pt-global-score__gauge-row">
          <div
            class="pt-global-score__ring"
            role="img"
            :aria-label="`Score global : ${globalScore} sur 100`"
          >
            <svg viewBox="0 0 120 120" class="pt-gauge-svg" aria-hidden="true">
              <circle
                class="pt-gauge-svg__track"
                cx="60" cy="60" r="52"
                fill="none"
                stroke="var(--pt-cream)"
                stroke-width="10"
              />
              <circle
                class="pt-gauge-svg__fill"
                cx="60" cy="60" r="52"
                fill="none"
                :stroke="gaugeColor"
                stroke-width="10"
                stroke-linecap="round"
                :stroke-dasharray="`${gaugeProgress} 327`"
                transform="rotate(-90 60 60)"
                style="transition: stroke-dasharray 0.8s ease;"
              />
            </svg>
            <div class="pt-gauge-svg__label">
              <span class="pt-gauge-svg__score">{{ globalScore }}</span>
              <span class="pt-gauge-svg__max">/100</span>
            </div>
          </div>

          <div class="pt-global-score__info">
            <p class="pt-global-score__interpretation">{{ interpretation }}</p>
            <div class="pt-badge" :style="{ backgroundColor: badgeColor }">
              {{ dominantStyleLabel }}
            </div>
          </div>
        </div>
      </section>

      <!-- ══════════════════════════════════════════════
           PROFIL COMMUNICANT — 5 DIMENSIONS
      ══════════════════════════════════════════════ -->
      <section class="pt-card pt-dimensions" aria-labelledby="dimensions-heading">
        <h2 id="dimensions-heading" class="pt-section-title">Votre profil communicant</h2>

        <div class="pt-dimensions__grid">
          <div
            v-for="dim in dimensionList"
            :key="dim.key"
            class="pt-dim-row"
          >
            <div class="pt-dim-row__header">
              <span class="pt-dim-row__icon" aria-hidden="true">{{ dim.icon }}</span>
              <span class="pt-dim-row__name">{{ dim.label }}</span>
              <span
                class="pt-dim-row__score"
                :aria-label="`${dim.label} : ${dim.score} sur 100`"
              >{{ dim.score }}<span class="pt-dim-row__score-max">/100</span></span>
            </div>

            <div
              class="pt-dim-row__bar-track"
              role="progressbar"
              :aria-valuenow="dim.score"
              aria-valuemin="0"
              aria-valuemax="100"
            >
              <div
                class="pt-dim-row__bar-fill"
                :style="{
                  width: `${dim.score}%`,
                  backgroundColor: dim.color,
                  transition: 'width 0.7s ease',
                }"
              />
            </div>

            <p class="pt-dim-row__description">{{ dim.description }}</p>
          </div>
        </div>
      </section>

      <!-- ══════════════════════════════════════════════
           STYLE COMMUNICANT DOMINANT
      ══════════════════════════════════════════════ -->
      <section class="pt-card pt-style-card" aria-labelledby="style-heading">
        <h2 id="style-heading" class="pt-section-title">Votre style communicant dominant</h2>

        <div class="pt-style-card__body">
          <div class="pt-style-card__avatar" aria-hidden="true">
            {{ dominantStyleData.emoji }}
          </div>
          <div class="pt-style-card__content">
            <h3 class="pt-style-card__name">{{ dominantStyleLabel }}</h3>
            <p class="pt-style-card__desc">{{ dominantStyleData.description }}</p>

            <div class="pt-style-card__strengths" v-if="scoreStrengths.length">
              <span class="pt-style-card__tag-label">Points forts :</span>
              <span
                v-for="s in scoreStrengths"
                :key="s"
                class="pt-badge pt-badge--strength"
              >{{ dimensionLabels[s] ?? s }}</span>
            </div>

            <div class="pt-style-card__growth" v-if="scoreGrowthAreas.length">
              <span class="pt-style-card__tag-label">Axes de développement :</span>
              <span
                v-for="g in scoreGrowthAreas"
                :key="g"
                class="pt-badge pt-badge--growth"
              >{{ dimensionLabels[g] ?? g }}</span>
            </div>
          </div>
        </div>
      </section>

      <!-- ══════════════════════════════════════════════
           EXERCICE DU JOUR
      ══════════════════════════════════════════════ -->
      <section class="pt-card pt-exercise-card" aria-labelledby="exercise-heading">
        <h2 id="exercise-heading" class="pt-section-title">
          <span aria-hidden="true">📅 </span>Votre scénario du jour
        </h2>

        <div v-if="exerciseOfTheDay" class="pt-exercise-card__body">
          <div class="pt-exercise-card__meta">
            <span class="pt-badge">{{ exerciseCategoryLabel }}</span>
            <span class="pt-exercise-card__duration">
              <span aria-hidden="true">⏱ </span>{{ exerciseOfTheDay.duration_minutes }} min
            </span>
            <span class="pt-exercise-card__difficulty" :aria-label="`Difficulté ${exerciseOfTheDay.difficulty} sur 3`">
              <span
                v-for="n in 3"
                :key="n"
                class="pt-difficulty-dot"
                :class="{ 'pt-difficulty-dot--active': n <= exerciseOfTheDay.difficulty }"
                aria-hidden="true"
              />
            </span>
          </div>

          <h3 class="pt-exercise-card__title">{{ exerciseOfTheDay.title }}</h3>
          <p class="pt-exercise-card__basis">{{ exerciseOfTheDay.scientific_basis }}</p>

          <div class="pt-exercise-card__scenario">
            <p class="pt-exercise-card__scenario-label">Mise en situation :</p>
            <blockquote class="pt-exercise-card__quote">
              {{ exerciseOfTheDay.instructions?.scenario }}
            </blockquote>
          </div>

          <p class="pt-exercise-card__question">
            <strong>Question :</strong> {{ exerciseOfTheDay.instructions?.question }}
          </p>

          <button class="pt-btn-primary pt-exercise-card__cta" @click="startExercise">
            Pratiquer cet exercice
          </button>
        </div>

        <div v-else class="pt-exercise-card__empty">
          <p>Aucun exercice disponible pour aujourd\'hui.</p>
        </div>
      </section>

      <!-- ══════════════════════════════════════════════
           PARCOURS 60 JOURS
      ══════════════════════════════════════════════ -->
      <section class="pt-card pt-journey" aria-labelledby="journey-heading">
        <h2 id="journey-heading" class="pt-section-title">
          <span aria-hidden="true">🗓 </span>Mon parcours 60 jours
        </h2>

        <!-- En-tête : stats rapides -->
        <div class="pt-journey__stats">
          <div class="pt-journey__stat">
            <span class="pt-journey__stat-value">{{ journeyCurrentDay }}</span>
            <span class="pt-journey__stat-label">Jour actuel</span>
          </div>
          <div class="pt-journey__stat">
            <span class="pt-journey__stat-value">{{ journeyStreak }}</span>
            <span class="pt-journey__stat-label">Jours de suite</span>
          </div>
          <div class="pt-journey__stat">
            <span class="pt-journey__stat-value">{{ journeyCompletion }}%</span>
            <span class="pt-journey__stat-label">Complété</span>
          </div>
          <div class="pt-journey__stat">
            <span
              class="pt-journey__stat-value pt-journey__stat-value--phase"
              :style="{ color: currentPhaseColor }"
            >{{ currentPhaseLabel }}</span>
            <span class="pt-journey__stat-label">Phase en cours</span>
          </div>
        </div>

        <!-- Barre de progression globale -->
        <div class="pt-journey__progress-bar-wrap" aria-label="Progression globale">
          <div class="pt-journey__progress-bar-track">
            <div
              class="pt-journey__progress-bar-fill"
              :style="{
                width: `${journeyCompletion}%`,
                backgroundColor: currentPhaseColor,
                transition: 'width 0.8s ease',
              }"
            />
          </div>
          <span class="pt-journey__progress-label">{{ journeyCompletedCount }}/60 jours</span>
        </div>

        <!-- Grille 10×6 = 60 cases -->
        <div
          class="pt-journey__grid"
          role="list"
          aria-label="Grille des 60 jours du parcours"
        >
          <div
            v-for="d in 60"
            :key="d"
            class="pt-journey__cell"
            :class="{
              'pt-journey__cell--done':    isDayDone(d),
              'pt-journey__cell--today':   d === journeyCurrentDay,
              'pt-journey__cell--future':  d > journeyCurrentDay && !isDayDone(d),
            }"
            :style="isDayDone(d) ? { backgroundColor: dayPhaseColor(d) } : {}"
            role="listitem"
            :aria-label="dayCellLabel(d)"
            :title="dayCellTitle(d)"
          >
            <span v-if="d === journeyCurrentDay" class="pt-journey__cell-pulse" aria-hidden="true" />
            <span class="pt-journey__cell-num" aria-hidden="true">{{ d }}</span>
          </div>
        </div>

        <!-- Légende phases -->
        <div class="pt-journey__legend" aria-label="Légende des phases">
          <div
            v-for="(ph, key) in journeyPhases"
            :key="key"
            class="pt-journey__legend-item"
          >
            <span
              class="pt-journey__legend-dot"
              :style="{ backgroundColor: ph.color }"
              aria-hidden="true"
            />
            <span class="pt-journey__legend-text">
              {{ ph.label }} <span class="pt-journey__legend-days">(J{{ ph.days[0] }}-{{ ph.days[1] }})</span>
            </span>
          </div>
        </div>

        <!-- Exercice du jour — parcours -->
        <div v-if="todayJourney" class="pt-journey__today">
          <div class="pt-journey__today-header">
            <span
              class="pt-journey__today-phase"
              :style="{ backgroundColor: currentPhaseColor }"
            >{{ currentPhaseLabel }}</span>
            <span class="pt-journey__today-duration" aria-label="Durée estimée">
              <span aria-hidden="true">⏱ </span>{{ todayJourney.duration_minutes }} min
            </span>
          </div>

          <h3 class="pt-journey__today-title">
            Jour {{ journeyCurrentDay }} — {{ todayJourney.title }}
          </h3>

          <div class="pt-journey__today-intention">
            <span class="pt-journey__today-intention-label" aria-label="Intention du jour">
              Intention
            </span>
            <p class="pt-journey__today-intention-text">{{ todayJourney.intention }}</p>
          </div>

          <div class="pt-journey__today-anchor">
            <span class="pt-journey__anchor-icon" aria-hidden="true">⚓</span>
            <span class="pt-journey__anchor-text">
              <strong>Ancre :</strong> {{ todayJourney.anchor }}
            </span>
          </div>

          <div class="pt-journey__today-habit">
            <span class="pt-journey__habit-icon" aria-hidden="true">🔁</span>
            <span class="pt-journey__habit-text">
              <strong>Micro-habitude :</strong> {{ todayJourney.micro_habit }}
            </span>
          </div>

          <div class="pt-journey__today-science" v-if="todayJourney.tip_science">
            <span class="pt-journey__science-icon" aria-hidden="true">🔬</span>
            <span class="pt-journey__science-text">{{ todayJourney.tip_science }}</span>
          </div>

          <button
            class="pt-btn-primary pt-journey__cta"
            @click="startJourneyExercise"
            :aria-label="`Commencer l'exercice du jour ${journeyCurrentDay} — ${todayJourney.duration_minutes} minutes`"
          >
            Commencer ({{ todayJourney.duration_minutes }} min)
          </button>
        </div>

        <!-- État vide -->
        <div v-else class="pt-journey__empty">
          <p>Parcours terminé ou non disponible. Bravo !</p>
        </div>
      </section>

      <!-- ══════════════════════════════════════════════
           ACTIONS
      ══════════════════════════════════════════════ -->
      <div class="pt-result-actions">
        <button class="pt-btn-primary" @click="retakeTest">
          Refaire le test
        </button>
        <button class="pt-btn-secondary" @click="goToDashboard">
          Retour au tableau de bord
        </button>
      </div>

    </div>
  </CandidateLayout>
</template>

<script setup>
import { computed } from 'vue'
import { router } from '@inertiajs/vue3'
import CandidateLayout from '@/Layouts/CandidateLayout.vue'

// ─────────────────────────────────────────────
// Props
// ─────────────────────────────────────────────
const props = defineProps({
  attempt: {
    type: Object,
    default: () => ({}),
  },
  results: {
    type: Object,
    default: () => ({}),
  },
  exerciseOfTheDay: {
    type: Object,
    default: null,
  },
  // Données du parcours 60 jours
  journeyCurrentDay: {
    type: Number,
    default: 1,
  },
  journeyStreak: {
    type: Number,
    default: 0,
  },
  journeyCompletion: {
    type: Number,
    default: 0,
  },
  journeyCompletedCount: {
    type: Number,
    default: 0,
  },
  // Map des jours complétés : { 1: { completed_at, felt_score }, … }
  journeyCompletedDays: {
    type: Object,
    default: () => ({}),
  },
  // Entrée Journey du jour courant
  todayJourney: {
    type: Object,
    default: null,
  },
})

// ─────────────────────────────────────────────
// Score global
// ─────────────────────────────────────────────
const globalScore = computed(() =>
  Math.round(props.results?.global_score ?? 0)
)

const gaugeProgress = computed(() => {
  // Circonférence = 2π × 52 ≈ 327
  return Math.round((globalScore.value / 100) * 327)
})

const gaugeColor = computed(() => {
  const s = globalScore.value
  if (s >= 85) return 'var(--pt-gold)'
  if (s >= 70) return '#4caf50'
  if (s >= 55) return '#ff9800'
  if (s >= 40) return '#f44336'
  return 'var(--pt-text-muted)'
})

const badgeColor = computed(() => {
  const s = globalScore.value
  if (s >= 70) return 'var(--pt-gold)'
  if (s >= 55) return '#ff9800'
  return '#f44336'
})

const interpretation = computed(
  () => props.results?.meta?.interpretation ?? ''
)

// ─────────────────────────────────────────────
// Style communicant
// ─────────────────────────────────────────────
const dominantStyleKey = computed(
  () => props.results?.meta?.dominant_style_key ?? 'communicant_equilibre'
)

const dominantStyleLabel = computed(
  () => props.results?.meta?.dominant_style ?? 'Communicant Équilibré'
)

const dominantStyleProfiles = {
  facilitateur_bienveillant: {
    emoji: '🤝',
    description:
      'Vous excellez dans la création d\'un espace de parole sécurisé. Votre écoute profonde et votre reformulation empathique mettent les gens à l\'aise et favorisent les échanges authentiques. Vous pouvez parfois avoir du mal à affirmer vos propres besoins — l\'assertivité sera votre prochain levier de croissance.',
  },
  affirmateur_direct: {
    emoji: '🎯',
    description:
      'Vous savez exprimer vos idées et vos limites avec clarté. Votre communication directe est appréciée pour sa lisibilité et inspire confiance. Veillez à maintenir l\'espace d\'expression de l\'autre et à enrichir votre registre empathique pour un impact encore plus fort.',
  },
  diplomate_strategique: {
    emoji: '⚖️',
    description:
      'Vous naviguez avec aisance dans les désaccords et savez créer des solutions acceptables pour toutes les parties. Votre intelligence des situations conflictuelles est un atout rare. Cultivez aussi la dimension émotionnelle de vos échanges pour des relations plus profondes.',
  },
  diplomate_empathique: {
    emoji: '💛',
    description:
      'Vous avez une sensibilité fine aux états émotionnels des autres et un talent naturel pour la validation et le soutien. Vous créez des relations de confiance durables. Développez votre assertivité pour ne pas vous effacer au détriment de vos propres besoins.',
  },
  coach_developpeur: {
    emoji: '🌱',
    description:
      'Vous maîtrisez l\'art du feedback constructif et savez formuler des retours qui font grandir sans blesser. Vos collaborateurs se sentent valorisés et guidés. Renforcez votre capacité à recevoir le feedback avec la même ouverture que vous en donnez.',
  },
  communicant_equilibre: {
    emoji: '🔄',
    description:
      'Vous avez développé un profil communicant équilibré, avec des compétences solides dans toutes les dimensions. Cette polyvalence est un atout majeur dans des contextes variés. Identifiez la dimension qui vous tient le plus à cœur pour l\'approfondir davantage.',
  },
}

const dominantStyleData = computed(
  () =>
    dominantStyleProfiles[dominantStyleKey.value] ??
    dominantStyleProfiles.communicant_equilibre
)

// ─────────────────────────────────────────────
// Dimensions
// ─────────────────────────────────────────────
const dimensionLabels = {
  ecoute_active:          'Écoute active',
  expression_assertive:   'Expression assertive',
  gestion_conflits:       'Gestion des conflits',
  empathie_relationnelle: 'Empathie relationnelle',
  feedback_constructif:   'Feedback constructif',
}

const dimensionIcons = {
  ecoute_active:          '👂',
  expression_assertive:   '💬',
  gestion_conflits:       '⚖️',
  empathie_relationnelle: '❤️',
  feedback_constructif:   '🔁',
}

const dimensionDescriptions = {
  ecoute_active:
    'Qualité de présence, reformulation et questions ouvertes qui permettent à l\'autre de se sentir pleinement entendu.',
  expression_assertive:
    'Capacité à exprimer ses pensées, besoins et limites clairement, sans agressivité ni effacement.',
  gestion_conflits:
    'Aptitude à transformer les désaccords en opportunités de dialogue et à trouver des solutions mutuellement satisfaisantes.',
  empathie_relationnelle:
    'Sensibilité aux états émotionnels de l\'autre, validation et adaptation culturelle du style de communication.',
  feedback_constructif:
    'Maîtrise de la donnée et de la réception de retours, qu\'ils soient positifs ou négatifs.',
}

const dimensionColors = {
  ecoute_active:          'var(--pt-gold)',
  expression_assertive:   '#4fc3f7',
  gestion_conflits:       '#81c784',
  empathie_relationnelle: '#f48fb1',
  feedback_constructif:   '#ffb74d',
}

const normScores = computed(() => props.results?.norm_scores ?? {})

const dimensionList = computed(() =>
  Object.keys(dimensionLabels).map((key) => ({
    key,
    label:       dimensionLabels[key],
    icon:        dimensionIcons[key],
    description: dimensionDescriptions[key],
    color:       dimensionColors[key],
    score:       Math.round(normScores.value[key] ?? 0),
  }))
)

const scoreStrengths = computed(
  () => props.results?.meta?.strengths ?? []
)

const scoreGrowthAreas = computed(
  () => props.results?.meta?.growth_areas ?? []
)

// ─────────────────────────────────────────────
// Exercice du jour
// ─────────────────────────────────────────────
const categoryLabels = {
  ecoute:      'Écoute active',
  cnv:         'CNV',
  conflit:     'Gestion des conflits',
  feedback:    'Feedback',
  assertivite: 'Assertivité',
}

const exerciseCategoryLabel = computed(
  () =>
    categoryLabels[props.exerciseOfTheDay?.category] ??
    props.exerciseOfTheDay?.category ??
    ''
)

const pageTitle = computed(
  () => `Résultats PraxiLink — ${props.attempt?.test?.name ?? 'Communication assertive'}`
)

// ─────────────────────────────────────────────
// Actions
// ─────────────────────────────────────────────
function startExercise() {
  if (!props.exerciseOfTheDay) return
  router.visit(
    route('tests.index'),
    { preserveScroll: false }
  )
}

function retakeTest() {
  const testId = props.attempt?.test_id ?? props.attempt?.test?.id
  if (!testId) return
  router.visit(route('tests.show', { test: testId }))
}

function goToDashboard() {
  router.visit(route('tests.index'))
}

// ─────────────────────────────────────────────
// Parcours 60 jours
// ─────────────────────────────────────────────

/**
 * Définition statique des phases (doit rester synchronisée avec Journey::phases() PHP).
 */
const journeyPhases = {
  decouverte:   { label: 'Découverte',   days: [1, 15],  color: 'var(--pt-gold)' },
  installation: { label: 'Installation', days: [16, 30], color: '#4fc3f7' },
  renforcement: { label: 'Renforcement', days: [31, 45], color: '#81c784' },
  maitrise:     { label: 'Maîtrise',     days: [46, 60], color: '#f48fb1' },
}

/**
 * Retourne la phase pour un numéro de jour donné.
 */
function phaseForDay(day) {
  if (day <= 15)  return 'decouverte'
  if (day <= 30)  return 'installation'
  if (day <= 45)  return 'renforcement'
  return 'maitrise'
}

/**
 * Couleur de phase pour un jour donné.
 */
function dayPhaseColor(day) {
  const phase = phaseForDay(day)
  return journeyPhases[phase]?.color ?? 'var(--pt-gold)'
}

/**
 * Phase courante (basée sur journeyCurrentDay).
 */
const currentPhaseKey = computed(() => phaseForDay(props.journeyCurrentDay))

const currentPhaseLabel = computed(
  () => journeyPhases[currentPhaseKey.value]?.label ?? 'Découverte'
)

const currentPhaseColor = computed(
  () => journeyPhases[currentPhaseKey.value]?.color ?? 'var(--pt-gold)'
)

/**
 * Indique si un jour est complété.
 */
function isDayDone(day) {
  return day in props.journeyCompletedDays
}

/**
 * Label ARIA pour une case de la grille.
 */
function dayCellLabel(day) {
  if (isDayDone(day)) return `Jour ${day} — complété`
  if (day === props.journeyCurrentDay) return `Jour ${day} — aujourd'hui`
  return `Jour ${day} — à venir`
}

/**
 * Titre tooltip pour une case de la grille.
 */
function dayCellTitle(day) {
  if (day === props.journeyCurrentDay) return `Aujourd'hui — Jour ${day}`
  if (isDayDone(day)) {
    const felt = props.journeyCompletedDays[day]?.felt_score
    return felt ? `Jour ${day} ✓ (ressenti ${felt}/5)` : `Jour ${day} ✓`
  }
  return `Jour ${day}`
}

/**
 * Navigation vers l'exercice du parcours du jour.
 */
function startJourneyExercise() {
  if (!props.todayJourney?.exercise_ref) return
  router.visit(
    route('tests.index'),
    { preserveScroll: false }
  )
}
</script>

<style scoped>
/* ══════════════════════════════════════════════
   WRAPPER & LAYOUT
══════════════════════════════════════════════ */
.pt-result-wrapper {
  max-width: 780px;
  margin: 0 auto;
  padding: 2rem 1rem 4rem;
  display: flex;
  flex-direction: column;
  gap: 1.75rem;
}

/* ══════════════════════════════════════════════
   EN-TÊTE
══════════════════════════════════════════════ */
.pt-result-header {
  text-align: center;
  padding: 2rem 1rem 1rem;
}

.pt-result-header__icon {
  font-size: 3rem;
  margin-bottom: 0.5rem;
  display: block;
}

.pt-result-header__title {
  font-size: 1.6rem;
  font-weight: 700;
  color: var(--pt-navy);
  margin: 0 0 0.25rem;
}

.pt-result-header__subtitle {
  color: var(--pt-text-muted);
  font-size: 0.95rem;
  margin: 0;
}

/* ══════════════════════════════════════════════
   SECTION TITLE
══════════════════════════════════════════════ */
.pt-section-title {
  font-size: 1.1rem;
  font-weight: 700;
  color: var(--pt-navy);
  margin: 0 0 1.25rem;
  padding-bottom: 0.5rem;
  border-bottom: 2px solid var(--pt-cream);
}

/* ══════════════════════════════════════════════
   SCORE GLOBAL
══════════════════════════════════════════════ */
.pt-global-score__gauge-row {
  display: flex;
  align-items: center;
  gap: 2rem;
  flex-wrap: wrap;
}

.pt-global-score__ring {
  position: relative;
  width: 130px;
  height: 130px;
  flex-shrink: 0;
}

.pt-gauge-svg {
  width: 100%;
  height: 100%;
}

.pt-gauge-svg__label {
  position: absolute;
  inset: 0;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
}

.pt-gauge-svg__score {
  font-size: 1.9rem;
  font-weight: 800;
  color: var(--pt-navy);
  line-height: 1;
}

.pt-gauge-svg__max {
  font-size: 0.75rem;
  color: var(--pt-text-muted);
}

.pt-global-score__info {
  flex: 1;
  min-width: 180px;
}

.pt-global-score__interpretation {
  color: var(--pt-navy);
  font-size: 0.95rem;
  line-height: 1.6;
  margin: 0 0 0.75rem;
}

/* ══════════════════════════════════════════════
   DIMENSIONS
══════════════════════════════════════════════ */
.pt-dimensions__grid {
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
}

.pt-dim-row__header {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  margin-bottom: 0.4rem;
}

.pt-dim-row__icon {
  font-size: 1.1rem;
}

.pt-dim-row__name {
  flex: 1;
  font-weight: 600;
  font-size: 0.92rem;
  color: var(--pt-navy);
}

.pt-dim-row__score {
  font-weight: 700;
  font-size: 1rem;
  color: var(--pt-navy);
}

.pt-dim-row__score-max {
  font-size: 0.75rem;
  color: var(--pt-text-muted);
  font-weight: 400;
}

.pt-dim-row__bar-track {
  height: 8px;
  border-radius: 4px;
  background: var(--pt-cream);
  overflow: hidden;
  margin-bottom: 0.4rem;
}

.pt-dim-row__bar-fill {
  height: 100%;
  border-radius: 4px;
}

.pt-dim-row__description {
  font-size: 0.82rem;
  color: var(--pt-text-muted);
  margin: 0;
  line-height: 1.5;
}

/* ══════════════════════════════════════════════
   STYLE COMMUNICANT
══════════════════════════════════════════════ */
.pt-style-card__body {
  display: flex;
  gap: 1.25rem;
  align-items: flex-start;
  flex-wrap: wrap;
}

.pt-style-card__avatar {
  font-size: 2.8rem;
  flex-shrink: 0;
  line-height: 1;
}

.pt-style-card__content {
  flex: 1;
  min-width: 200px;
}

.pt-style-card__name {
  font-size: 1.15rem;
  font-weight: 700;
  color: var(--pt-navy);
  margin: 0 0 0.5rem;
}

.pt-style-card__desc {
  font-size: 0.9rem;
  color: var(--pt-navy);
  line-height: 1.6;
  margin: 0 0 0.75rem;
}

.pt-style-card__strengths,
.pt-style-card__growth {
  display: flex;
  flex-wrap: wrap;
  gap: 0.4rem;
  align-items: center;
  margin-bottom: 0.5rem;
}

.pt-style-card__tag-label {
  font-size: 0.8rem;
  font-weight: 600;
  color: var(--pt-text-muted);
  margin-right: 0.25rem;
}

.pt-badge--strength {
  background: rgba(var(--pt-gold-rgb, 212, 175, 55), 0.15);
  color: var(--pt-navy);
  border: 1px solid var(--pt-gold);
}

.pt-badge--growth {
  background: rgba(244, 67, 54, 0.08);
  color: var(--pt-navy);
  border: 1px solid #f44336;
}

/* ══════════════════════════════════════════════
   EXERCICE DU JOUR
══════════════════════════════════════════════ */
.pt-exercise-card__meta {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  flex-wrap: wrap;
  margin-bottom: 0.75rem;
}

.pt-exercise-card__duration {
  font-size: 0.85rem;
  color: var(--pt-text-muted);
}

.pt-exercise-card__difficulty {
  display: flex;
  gap: 3px;
}

.pt-difficulty-dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: var(--pt-cream);
  display: inline-block;
}

.pt-difficulty-dot--active {
  background: var(--pt-gold);
}

.pt-exercise-card__title {
  font-size: 1.1rem;
  font-weight: 700;
  color: var(--pt-navy);
  margin: 0 0 0.5rem;
}

.pt-exercise-card__basis {
  font-size: 0.82rem;
  color: var(--pt-text-muted);
  font-style: italic;
  margin: 0 0 1rem;
  line-height: 1.5;
}

.pt-exercise-card__scenario {
  background: var(--pt-cream);
  border-radius: 0.5rem;
  padding: 1rem 1.25rem;
  margin-bottom: 1rem;
}

.pt-exercise-card__scenario-label {
  font-size: 0.8rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.04em;
  color: var(--pt-text-muted);
  margin: 0 0 0.5rem;
}

.pt-exercise-card__quote {
  margin: 0;
  font-size: 0.92rem;
  color: var(--pt-navy);
  line-height: 1.65;
  border-left: 3px solid var(--pt-gold);
  padding-left: 1rem;
}

.pt-exercise-card__question {
  font-size: 0.92rem;
  color: var(--pt-navy);
  line-height: 1.6;
  margin: 0 0 1.25rem;
}

.pt-exercise-card__cta {
  width: 100%;
}

.pt-exercise-card__empty {
  color: var(--pt-text-muted);
  font-style: italic;
}

/* ══════════════════════════════════════════════
   ACTIONS
══════════════════════════════════════════════ */
.pt-result-actions {
  display: flex;
  gap: 1rem;
  flex-wrap: wrap;
  justify-content: center;
  padding-top: 0.5rem;
}

.pt-btn-secondary {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 0.65rem 1.5rem;
  border-radius: 0.375rem;
  font-weight: 600;
  font-size: 0.9rem;
  cursor: pointer;
  border: 2px solid var(--pt-navy);
  background: transparent;
  color: var(--pt-navy);
  transition: background 0.2s, color 0.2s;
}

.pt-btn-secondary:hover {
  background: var(--pt-navy);
  color: var(--pt-cream);
}

/* ══════════════════════════════════════════════
   PARCOURS 60 JOURS
══════════════════════════════════════════════ */

/* Stats rapides */
.pt-journey__stats {
  display: flex;
  gap: 1rem;
  flex-wrap: wrap;
  margin-bottom: 1.25rem;
}

.pt-journey__stat {
  flex: 1;
  min-width: 80px;
  display: flex;
  flex-direction: column;
  align-items: center;
  background: var(--pt-cream);
  border-radius: 0.5rem;
  padding: 0.75rem 0.5rem;
  text-align: center;
}

.pt-journey__stat-value {
  font-size: 1.5rem;
  font-weight: 800;
  color: var(--pt-navy);
  line-height: 1;
  margin-bottom: 0.25rem;
}

.pt-journey__stat-value--phase {
  font-size: 0.85rem;
  font-weight: 700;
  line-height: 1.3;
}

.pt-journey__stat-label {
  font-size: 0.72rem;
  color: var(--pt-text-muted);
  text-transform: uppercase;
  letter-spacing: 0.04em;
}

/* Barre de progression globale */
.pt-journey__progress-bar-wrap {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  margin-bottom: 1.25rem;
}

.pt-journey__progress-bar-track {
  flex: 1;
  height: 10px;
  border-radius: 5px;
  background: var(--pt-cream);
  overflow: hidden;
}

.pt-journey__progress-bar-fill {
  height: 100%;
  border-radius: 5px;
}

.pt-journey__progress-label {
  font-size: 0.8rem;
  color: var(--pt-text-muted);
  white-space: nowrap;
  font-weight: 600;
}

/* Grille 10×6 */
.pt-journey__grid {
  display: grid;
  grid-template-columns: repeat(10, 1fr);
  gap: 4px;
  margin-bottom: 1rem;
}

.pt-journey__cell {
  position: relative;
  aspect-ratio: 1;
  border-radius: 4px;
  background: var(--pt-cream);
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: default;
  transition: transform 0.15s ease, box-shadow 0.15s ease;
  overflow: hidden;
}

.pt-journey__cell:hover {
  transform: scale(1.15);
  z-index: 2;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
}

/* Jour complété */
.pt-journey__cell--done {
  opacity: 0.92;
}

.pt-journey__cell--done .pt-journey__cell-num {
  color: #fff;
  font-weight: 700;
}

/* Jour courant — pulsant */
.pt-journey__cell--today {
  border: 2px solid var(--pt-navy);
  background: transparent;
}

.pt-journey__cell--today .pt-journey__cell-num {
  color: var(--pt-navy);
  font-weight: 800;
  position: relative;
  z-index: 2;
}

.pt-journey__cell-pulse {
  position: absolute;
  inset: 0;
  border-radius: 3px;
  background: var(--pt-gold);
  opacity: 0.18;
  animation: pt-pulse 1.8s ease-in-out infinite;
}

@keyframes pt-pulse {
  0%, 100% { opacity: 0.12; }
  50%       { opacity: 0.32; }
}

/* Jour futur */
.pt-journey__cell--future {
  opacity: 0.45;
}

.pt-journey__cell-num {
  font-size: 0.6rem;
  color: var(--pt-text-muted);
  line-height: 1;
  user-select: none;
}

/* Légende */
.pt-journey__legend {
  display: flex;
  flex-wrap: wrap;
  gap: 0.75rem;
  margin-bottom: 1.5rem;
}

.pt-journey__legend-item {
  display: flex;
  align-items: center;
  gap: 0.35rem;
}

.pt-journey__legend-dot {
  width: 10px;
  height: 10px;
  border-radius: 50%;
  flex-shrink: 0;
}

.pt-journey__legend-text {
  font-size: 0.78rem;
  color: var(--pt-navy);
  font-weight: 600;
}

.pt-journey__legend-days {
  font-weight: 400;
  color: var(--pt-text-muted);
}

/* Exercice du jour — parcours */
.pt-journey__today {
  background: var(--pt-cream);
  border-radius: 0.75rem;
  padding: 1.25rem;
  display: flex;
  flex-direction: column;
  gap: 0.9rem;
}

.pt-journey__today-header {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  flex-wrap: wrap;
}

.pt-journey__today-phase {
  font-size: 0.75rem;
  font-weight: 700;
  color: #fff;
  padding: 0.2rem 0.6rem;
  border-radius: 2rem;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.pt-journey__today-duration {
  font-size: 0.85rem;
  color: var(--pt-text-muted);
}

.pt-journey__today-title {
  font-size: 1.1rem;
  font-weight: 700;
  color: var(--pt-navy);
  margin: 0;
  line-height: 1.4;
}

.pt-journey__today-intention {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.pt-journey__today-intention-label {
  font-size: 0.7rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.06em;
  color: var(--pt-text-muted);
}

.pt-journey__today-intention-text {
  font-size: 0.95rem;
  color: var(--pt-navy);
  font-style: italic;
  margin: 0;
  line-height: 1.5;
  border-left: 3px solid var(--pt-gold);
  padding-left: 0.75rem;
}

.pt-journey__today-anchor,
.pt-journey__today-habit,
.pt-journey__today-science {
  display: flex;
  align-items: flex-start;
  gap: 0.5rem;
  font-size: 0.87rem;
  color: var(--pt-navy);
  line-height: 1.5;
}

.pt-journey__anchor-icon,
.pt-journey__habit-icon,
.pt-journey__science-icon {
  flex-shrink: 0;
  font-size: 1rem;
  margin-top: 0.05rem;
}

.pt-journey__science-text {
  font-size: 0.8rem;
  color: var(--pt-text-muted);
  font-style: italic;
}

.pt-journey__cta {
  margin-top: 0.25rem;
  align-self: stretch;
}

.pt-journey__empty {
  color: var(--pt-text-muted);
  font-style: italic;
  text-align: center;
  padding: 1rem;
}

/* ══════════════════════════════════════════════
   RESPONSIVE
══════════════════════════════════════════════ */
@media (max-width: 600px) {
  .pt-global-score__gauge-row {
    flex-direction: column;
    align-items: center;
    text-align: center;
  }

  .pt-style-card__body {
    flex-direction: column;
    align-items: center;
    text-align: center;
  }

  .pt-result-actions {
    flex-direction: column;
  }

  .pt-result-actions button {
    width: 100%;
  }

  .pt-journey__grid {
    grid-template-columns: repeat(10, 1fr);
    gap: 3px;
  }

  .pt-journey__cell-num {
    font-size: 0.5rem;
  }

  .pt-journey__stats {
    gap: 0.5rem;
  }

  .pt-journey__stat {
    padding: 0.5rem 0.35rem;
  }

  .pt-journey__stat-value {
    font-size: 1.2rem;
  }
}
</style>
