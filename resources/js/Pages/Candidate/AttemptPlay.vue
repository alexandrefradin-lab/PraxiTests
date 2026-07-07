<script setup>
import { computed, ref } from 'vue'
import { router, Head, Link } from '@inertiajs/vue3'
import { useParcours } from '@/composables/useParcours'

const { L, isCorporate, testLabel } = useParcours()

const props = defineProps({
    attempt: Object,
    progress: Object,
    gamification: Object,
    narrative: Object,
})

const allQuestions = computed(() =>
    props.attempt.test.sections.flatMap(s => s.questions.map(q => ({ ...q, section_title: s.title })))
)

// Réponses déjà enregistrées côté serveur, indexées par question_id.
// Permet de restaurer la valeur quand on revient en arrière pour corriger.
const savedAnswers = ref(
    Object.fromEntries((props.attempt.answers ?? []).map(a => [a.question_id, a.value]))
)

const totalQuestions = computed(() => allQuestions.value.length)
const startIndex = props.attempt.answers.length
const currentIndex = ref(Math.min(startIndex, Math.max(0, allQuestions.value.length - 1)))
const currentQuestion = computed(() => allQuestions.value[currentIndex.value])

// Progression basée sur le nombre de réponses enregistrées (stable même
// quand on revient en arrière pour relire/corriger).
const answeredCount = computed(() => Object.keys(savedAnswers.value).length)
const percent = computed(() => totalQuestions.value > 0 ? (answeredCount.value / totalQuestions.value) * 100 : 0)

const value = ref(null)
const startedAt = ref(Date.now())
const isSubmitting = ref(false)

// Sens de l'animation du paquet de cartes : 'deck-fwd' en avançant, 'deck-back' en relecture.
const transitionName = ref('deck-fwd')

// Aperçu des 2 cartes suivantes → effet de pile derrière la carte active.
const ghostQuestions = computed(() => {
    const arr = []
    for (let d = 1; d <= 2; d++) {
        const q = allQuestions.value[currentIndex.value + d]
        if (q) arr.push(q)
    }
    return arr
})

const hasValue = (v) => !(v === null || v === undefined || v === '' || (Array.isArray(v) && !v.length))

// Charge dans `value` la réponse déjà enregistrée pour la question courante (ou null).
const loadValue = () => {
    const saved = savedAnswers.value[currentQuestion.value?.id]
    value.value = saved !== undefined ? saved : null
    startedAt.value = Date.now()
}
loadValue()

const goTo = (index) => {
    if (index < 0 || index >= totalQuestions.value) return
    transitionName.value = index >= currentIndex.value ? 'deck-fwd' : 'deck-back'
    currentIndex.value = index
    loadValue()
}

const goBack = () => goTo(currentIndex.value - 1)

// Bouton "avance" discret, utile uniquement en relecture : on ne le montre
// que si la question courante a déjà une réponse enregistrée.
const canGoForward = computed(() =>
    currentIndex.value + 1 < totalQuestions.value &&
    savedAnswers.value[currentQuestion.value?.id] !== undefined
)
const goForward = () => { if (canGoForward.value) goTo(currentIndex.value + 1) }

// Type de question nécessitant un bouton de validation explicite (impossible
// d'avancer sur un simple clic). 'single'/'scale' auto-avancent, 'exercise' a
// ses propres boutons ; tout le reste (multi, text, ranking, ET tout type non
// géré rendu via le repli textarea) passe par le bouton Valider → jamais de
// cul-de-sac.
const SELF_ADVANCING_TYPES = ['single', 'scale', 'likert', 'exercise']
const needsConfirmButton = computed(() => {
    const t = currentQuestion.value?.type
    return !t || !SELF_ADVANCING_TYPES.includes(t)
})

const recordAndAdvance = () => {
    if (!currentQuestion.value) return
    if (isSubmitting.value || !hasValue(value.value)) return
    isSubmitting.value = true
    const time = Math.round((Date.now() - startedAt.value) / 1000)
    const qid = currentQuestion.value.id
    const val = value.value
    router.post(route('attempt.answer', props.attempt.id), {
        question_id: qid,
        value: val,
        time_spent: time,
    }, {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
            isSubmitting.value = false
            savedAnswers.value = { ...savedAnswers.value, [qid]: val }
            if (currentIndex.value + 1 >= totalQuestions.value) {
                router.post(route('attempt.complete', props.attempt.id))
            } else {
                goTo(currentIndex.value + 1)
            }
        },
        onError: () => {
            isSubmitting.value = false
        },
    })
}

// Choix simple / échelle : sélectionner enregistre et avance directement.
const selectAndAdvance = (optValue) => {
    if (isSubmitting.value) return
    value.value = optValue
    recordAndAdvance()
}

const isLastQuestion = computed(() => currentIndex.value + 1 >= totalQuestions.value)
const canSubmit = computed(() => !isSubmitting.value && hasValue(value.value))

const toggleMultiple = (optValue) => {
    if (!Array.isArray(value.value)) value.value = []
    const idx = value.value.indexOf(optValue)
    if (idx === -1) {
        value.value = [...value.value, optValue]
    } else {
        value.value = value.value.filter(v => v !== optValue)
    }
}

const isMultiSelected = (optValue) => Array.isArray(value.value) && value.value.includes(optValue)

// Exercice guidé : les métadonnées (instructions / base scientifique) peuvent
// vivre soit dans `meta` (seeder questionnaire), soit dans `options` (données de
// type 'exercise' historiques). On lit les deux pour rester compatible.
const exerciseMeta = computed(() => {
    const q = currentQuestion.value || {}
    const m = q.meta && typeof q.meta === 'object' ? q.meta : {}
    const o = q.options && !Array.isArray(q.options) && typeof q.options === 'object' ? q.options : {}
    return { ...o, ...m }
})
const exerciseInstructions = computed(() => {
    const ins = exerciseMeta.value.instructions
    return Array.isArray(ins) ? ins : (ins ? [ins] : [])
})
const exerciseBasis = computed(() => exerciseMeta.value.scientific_basis || '')
</script>

<template>
    <div class="ac-shell">
        <Head :title="testLabel(attempt.test)" />

        <!-- HEADER MINIMAL -->
        <header class="ac-header">
            <div class="ac-header-inner">
                <!-- Retour armurerie + logo + nom test -->
                <div class="ac-header-left">
                    <Link :href="route('tests.index')" class="ac-back-link">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M19 12H5M5 12l7-7M5 12l7 7" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        {{ isCorporate ? 'Évaluations' : 'Armurerie' }}
                    </Link>
                    <span class="ac-header-sep" aria-hidden="true"></span>
                    <svg class="ac-logo" width="26" height="26" viewBox="0 0 38 38" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <circle cx="19" cy="19" r="17.5" stroke="#A67520" stroke-width="1"/>
                        <circle cx="19" cy="19" r="13" stroke="#A67520" stroke-width="0.5" opacity="0.5"/>
                        <polygon points="19,6 20.4,18 19,21 17.6,18" fill="#A67520"/>
                        <polygon points="19,32 20.4,20 19,17 17.6,20" fill="#A67520" opacity="0.35"/>
                        <circle cx="19" cy="19" r="2" fill="#A67520"/>
                        <circle cx="19" cy="19" r="1" fill="#F0E8D4"/>
                    </svg>
                    <span class="ac-test-name">{{ testLabel(attempt.test) }}</span>
                </div>

                <!-- Numéro question centré -->
                <div class="ac-header-center">
                    <span class="ac-q-counter">Q {{ currentIndex + 1 }} / {{ totalQuestions }}</span>
                </div>

                <!-- Badge gamification -->
                <div class="ac-header-right">
                    <span v-if="gamification" class="ac-xp-badge">
                        Niv.{{ gamification.level }} · {{ gamification.xp }} {{ L.xpName }}
                    </span>
                </div>
            </div>
        </header>

        <!-- BARRE XP -->
        <div class="ac-xp-track">
            <div class="ac-xp-fill" :style="{ width: percent + '%' }"></div>
        </div>

        <!-- CONTENU PRINCIPAL -->
        <template v-if="totalQuestions > 0">
            <main class="ac-main">
                <div class="ac-question-wrap">

                    <!-- PAQUET DE CARTES : pile en arrière-plan + carte active -->
                    <div class="ac-deck">
                        <div
                            v-for="(gq, gi) in ghostQuestions.slice().reverse()"
                            :key="'ghost-' + gq.id"
                            class="ac-ghost"
                            :class="'ac-ghost--' + (ghostQuestions.length - gi)"
                            aria-hidden="true"
                        >
                            <span class="ac-ghost-badge">{{ gq.section_title }}</span>
                        </div>

                        <Transition :name="transitionName">
                            <div class="ac-card" :key="currentIndex">

                    <!-- Badge section -->
                    <div class="ac-section-badge">{{ currentQuestion.section_title }}</div>

                    <!-- Question -->
                    <h2 class="ac-question-text">{{ currentQuestion.prompt }}</h2>

                    <!-- Helper -->
                    <p v-if="currentQuestion.helper" class="ac-helper">{{ currentQuestion.helper }}</p>

                    <!-- OPTIONS -->
                    <div class="ac-options">

                        <!-- SINGLE CHOICE -->
                        <template v-if="currentQuestion.type === 'single'">
                          <div role="radiogroup" :aria-label="currentQuestion.prompt" class="ac-option-group">
                            <div
                                v-for="opt in currentQuestion.options"
                                :key="opt.value"
                                class="ac-option-card"
                                :class="{
                                    'ac-option-card--selected':   value === opt.value,
                                    'ac-option-card--processing': isSubmitting && value === opt.value,
                                }"
                                role="radio"
                                :aria-checked="value === opt.value"
                                :tabindex="isSubmitting ? -1 : 0"
                                @click="selectAndAdvance(opt.value)"
                                @keydown.enter.prevent="selectAndAdvance(opt.value)"
                                @keydown.space.prevent="selectAndAdvance(opt.value)"
                            >
                                <span class="ac-radio-icon">
                                    <svg v-if="value === opt.value" width="18" height="18" viewBox="0 0 18 18" fill="none">
                                        <circle cx="9" cy="9" r="8" stroke="var(--color-primary)" stroke-width="1.5"/>
                                        <circle cx="9" cy="9" r="4.5" fill="var(--color-primary)"/>
                                    </svg>
                                    <svg v-else width="18" height="18" viewBox="0 0 18 18" fill="none">
                                        <circle cx="9" cy="9" r="8" stroke="var(--glass-border-solid)" stroke-width="1.5"/>
                                    </svg>
                                </span>
                                <span class="ac-option-label">{{ opt.label }}</span>
                            </div>
                          </div>
                        </template>

                        <!-- EXERCICE GUIDÉ (parcours mini-app : praxispeak, etc.) -->
                        <template v-else-if="currentQuestion.type === 'exercise'">
                            <div class="ac-exercise">
                                <ol v-if="exerciseInstructions.length" class="ac-exercise-steps">
                                    <li v-for="(step, i) in exerciseInstructions" :key="i">{{ step }}</li>
                                </ol>

                                <p v-if="exerciseBasis" class="ac-exercise-basis">
                                    <span class="ac-exercise-basis-kicker">Pourquoi ça marche</span>
                                    {{ exerciseBasis }}
                                </p>

                                <div class="ac-exercise-actions">
                                    <button
                                        type="button"
                                        class="ac-btn-primary"
                                        :class="{ 'ac-btn-primary--disabled': isSubmitting }"
                                        :disabled="isSubmitting"
                                        @click="selectAndAdvance(1)"
                                    >
                                        {{ isSubmitting ? '…' : (isLastQuestion ? "J'ai fait cet exercice — Terminer →" : "J'ai fait cet exercice →") }}
                                    </button>
                                    <button
                                        type="button"
                                        class="ac-btn-ghost"
                                        :disabled="isSubmitting"
                                        @click="selectAndAdvance(0)"
                                    >
                                        Passer pour l'instant
                                    </button>
                                </div>
                            </div>
                        </template>

                        <!-- SCALE / LIKERT (alias) -->
                        <template v-else-if="currentQuestion.type === 'scale' || currentQuestion.type === 'likert'">
                            <div class="ac-scale-wrap"
                                 role="radiogroup"
                                 :aria-label="currentQuestion.prompt">
                                <span class="ac-scale-label">{{ currentQuestion.options?.min_label || 'Pas du tout' }}</span>
                                <div class="ac-scale-buttons">
                                    <button
                                        v-for="n in (currentQuestion.options?.max || 5)"
                                        :key="n"
                                        type="button"
                                        class="ac-scale-btn"
                                        :class="{
                                            'ac-scale-btn--active':      value === n,
                                            'ac-scale-btn--processing':  isSubmitting && value === n,
                                        }"
                                        role="radio"
                                        :aria-checked="value === n"
                                        :aria-label="String(n)"
                                        :disabled="isSubmitting"
                                        @click="selectAndAdvance(n)"
                                    >
                                        {{ n }}
                                    </button>
                                </div>
                                <span class="ac-scale-label">{{ currentQuestion.options?.max_label || 'Tout à fait' }}</span>
                            </div>
                        </template>

                        <!-- MULTIPLE CHOICE -->
                        <template v-else-if="currentQuestion.type === 'multi' || currentQuestion.type === 'multiple'">
                          <div role="group" :aria-label="currentQuestion.prompt" class="ac-option-group">
                            <div
                                v-for="opt in currentQuestion.options"
                                :key="opt.value"
                                class="ac-option-card"
                                :class="{ 'ac-option-card--selected': isMultiSelected(opt.value) }"
                                role="checkbox"
                                :aria-checked="isMultiSelected(opt.value)"
                                tabindex="0"
                                @click="toggleMultiple(opt.value)"
                                @keydown.enter.prevent="toggleMultiple(opt.value)"
                                @keydown.space.prevent="toggleMultiple(opt.value)"
                            >
                                <span class="ac-checkbox-icon">
                                    <svg v-if="isMultiSelected(opt.value)" width="18" height="18" viewBox="0 0 18 18" fill="none">
                                        <rect x="1" y="1" width="16" height="16" rx="4" fill="var(--color-primary)" stroke="var(--color-primary)" stroke-width="1.5"/>
                                        <path d="M5 9l3 3 5-5" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                    <svg v-else width="18" height="18" viewBox="0 0 18 18" fill="none">
                                        <rect x="1" y="1" width="16" height="16" rx="4" stroke="var(--glass-border-solid)" stroke-width="1.5"/>
                                    </svg>
                                </span>
                                <span class="ac-option-label">{{ opt.label }}</span>
                            </div>
                          </div>
                        </template>

                        <!-- TEXT LIBRE -->
                        <template v-else-if="currentQuestion.type === 'text'">
                            <label :for="'answer-' + currentQuestion.id" class="sr-only">
                                {{ currentQuestion.label || currentQuestion.prompt || 'Votre réponse' }}
                            </label>
                            <textarea
                                :id="'answer-' + currentQuestion.id"
                                v-model="value"
                                rows="4"
                                class="ac-textarea"
                                placeholder="Développe ta pensée…"
                                :aria-describedby="currentQuestion.helper ? 'help-' + currentQuestion.id : undefined"
                            ></textarea>
                        </template>

                        <!-- Repli : type de question non géré (jamais d'écran vide / cul-de-sac).
                             On laisse répondre en texte libre pour ne pas bloquer la passation. -->
                        <template v-else>
                            <p class="ac-fallback-note">
                                Cette question utilise un format non pris en charge. Réponds librement ci-dessous.
                            </p>
                            <label :for="'answer-fallback-' + currentQuestion.id" class="sr-only">
                                {{ currentQuestion.label || currentQuestion.prompt || 'Votre réponse' }}
                            </label>
                            <textarea
                                :id="'answer-fallback-' + currentQuestion.id"
                                v-model="value"
                                rows="4"
                                class="ac-textarea"
                                placeholder="Ta réponse…"
                            ></textarea>
                        </template>

                    </div>

                    <!-- BOUTON VALIDER :
                         – multi/texte       : toujours nécessaire (pas d'auto-avance possible)
                         – single/scale      : fallback si l'auto-avance n'a pas pu s'exécuter
                           (serveur lent ou erreur réseau). Conditions : une valeur est sélectionnée,
                           on n'est pas en train de soumettre, et la réponse n'est pas encore dans
                           savedAnswers → l'utilisateur n'est jamais bloqué sans bouton d'action.  -->
                    <div v-if="needsConfirmButton || (hasValue(value) && !isSubmitting && savedAnswers[currentQuestion?.id] === undefined)" class="ac-submit-wrap">
                        <button
                            class="ac-btn-primary"
                            :class="{ 'ac-btn-primary--disabled': !canSubmit }"
                            :disabled="!canSubmit"
                            @click="recordAndAdvance"
                        >
                            {{ isSubmitting ? '…' : (isLastQuestion ? 'Terminer l\'Épreuve →' : 'Valider et continuer →') }}
                        </button>
                    </div>

                            </div>
                        </Transition>
                    </div>
                    <!-- /PAQUET DE CARTES -->

                    <!-- Navigation : retour pour corriger / avancer en relecture -->
                    <div class="ac-nav">
                        <button
                            v-if="currentIndex > 0"
                            type="button"
                            class="ac-nav-btn"
                            :disabled="isSubmitting"
                            @click="goBack"
                        >
                            ← Précédent
                        </button>
                        <span v-else></span>

                        <button
                            v-if="canGoForward"
                            type="button"
                            class="ac-nav-btn"
                            :disabled="isSubmitting"
                            @click="goForward"
                        >
                            Suivant →
                        </button>
                    </div>

                    <!-- CARTE APERÇU DÉBLOQUÉ (mini-insight provisoire) -->
                    <div v-if="progress?.insight" class="ac-insight">
                        <div class="ac-insight-head">
                            <svg class="ac-insight-icon" width="16" height="16" viewBox="0 0 24 24" fill="none">
                                <path d="M9 21h6m-5 0v-3m4 3v-3M12 2a7 7 0 0 0-4 12.7c.6.5 1 1.2 1 2v.3h6v-.3c0-.8.4-1.5 1-2A7 7 0 0 0 12 2z"
                                    stroke="var(--color-primary)" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            <span class="ac-insight-kicker">Aperçu débloqué</span>
                            <span v-if="progress.insight.score != null" class="ac-insight-score">
                                {{ Math.round(progress.insight.score) }}<span class="ac-insight-score-max">/100</span>
                            </span>
                        </div>
                        <p class="ac-insight-headline">{{ progress.insight.headline }}</p>
                        <p class="ac-insight-body">{{ progress.insight.body }}</p>
                        <span class="ac-insight-tag">Provisoire — s'affine à chaque réponse</span>
                    </div>

                    <!-- BANDEAU NARRATIVE (tant que l'aperçu n'est pas débloqué) -->
                    <div v-else-if="progress?.narrative" class="ac-narrative">
                        <svg class="ac-narrative-icon" width="14" height="14" viewBox="0 0 24 24" fill="none">
                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 15v-4H7l5-8v4h4l-5 8z" fill="var(--color-primary)"/>
                        </svg>
                        <span class="ac-narrative-text">{{ progress.narrative }}</span>
                    </div>

                </div>
            </main>
        </template>

        <!-- ÉTAT VIDE -->
        <div v-else class="ac-empty">
            <p class="ac-empty-title">Ce test ne contient aucune question.</p>
            <p class="ac-empty-sub">Contacte l'administrateur pour vérifier la configuration de l'Épreuve.</p>
        </div>

    </div>
</template>

<style scoped>
/* ── Accessibilité ───────────────────────────── */
.sr-only {
    position: absolute;
    width: 1px;
    height: 1px;
    padding: 0;
    margin: -1px;
    overflow: hidden;
    clip: rect(0, 0, 0, 0);
    border: 0;
}

/* ── TOKENS AC ────────────────────────────────── */
.ac-shell {
    --bg-base:           #F0E8D4;
    --bg-surface:        #E5DAC2;
    --bg-elevated:       #D8CEB5;
    --color-primary:     #A67520;
    --color-primary-dark:#7D5510;
    --color-secondary:   #7B1515;
    --color-accent:      #1C1408;
    --color-success:     #3A6B48;
    --color-danger:      #B03020;
    --color-signal:      #0A7FA0;
    --text-primary:      #2A1E08;
    --text-secondary:    #6B5A3E;
    --glass-bg:          rgba(240,232,212,0.85);
    --glass-border:      rgba(166,117,32,0.25);
    --glass-border-solid:#A67520;
    --shadow-card:       0 2px 12px rgba(42,30,8,0.1);

    min-height: 100vh;
    background-color: var(--bg-base);
    background-image:
        radial-gradient(ellipse at 20% 0%, rgba(166,117,32,0.06) 0%, transparent 60%),
        radial-gradient(ellipse at 80% 100%, rgba(123,21,21,0.04) 0%, transparent 60%);
    font-family: 'Inter', sans-serif;
    color: var(--text-primary);
}

/* ── HEADER ──────────────────────────────────── */
.ac-header {
    position: sticky;
    top: 0;
    z-index: 50;
    height: 58px;
    background: var(--glass-bg);
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
    border-bottom: 1px solid var(--glass-border);
    box-shadow: 0 1px 8px rgba(42,30,8,0.08);
}

.ac-header-inner {
    max-width: 760px;
    margin: 0 auto;
    height: 100%;
    padding: 0 1.25rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
}

.ac-header-left {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    flex: 1;
    min-width: 0;
}

.ac-back-link {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    font-family: 'Space Grotesk', sans-serif;
    font-size: 12px;
    font-weight: 600;
    color: var(--color-primary);
    text-decoration: none;
    padding: 4px 10px;
    border-radius: 6px;
    border: 1px solid rgba(166,117,32,0.35);
    background: rgba(166,117,32,0.06);
    flex-shrink: 0;
    transition: background 0.15s, border-color 0.15s;
}
.ac-back-link:hover {
    background: rgba(166,117,32,0.14);
    border-color: var(--color-primary);
}

.ac-header-sep {
    display: block;
    width: 1px;
    height: 16px;
    background: var(--glass-border);
    flex-shrink: 0;
}

.ac-logo {
    display: inline-flex;
    width: 26px;
    height: 26px;
    flex-shrink: 0;
}

.ac-test-name {
    font-family: 'Space Grotesk', sans-serif;
    font-size: 13px;
    color: var(--text-secondary);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 200px;
}

.ac-header-center {
    flex-shrink: 0;
}

.ac-q-counter {
    font-family: 'Space Mono', monospace;
    font-size: 12px;
    color: var(--text-secondary);
    letter-spacing: 0.02em;
}

.ac-header-right {
    flex: 1;
    display: flex;
    justify-content: flex-end;
}

.ac-xp-badge {
    font-family: 'Space Mono', monospace;
    font-size: 11px;
    color: var(--color-primary);
    background: rgba(166,117,32,0.1);
    border: 1px solid var(--glass-border);
    padding: 3px 8px;
    border-radius: 20px;
    white-space: nowrap;
}

/* ── BARRE XP ─────────────────────────────────── */
.ac-xp-track {
    width: 100%;
    height: 6px;
    background: var(--bg-elevated);
    border-radius: 0;
    overflow: hidden;
}

.ac-xp-fill {
    height: 100%;
    background: var(--color-primary);
    transition: width 0.6s ease;
    border-radius: 0;
}

/* ── MAIN ─────────────────────────────────────── */
.ac-main {
    display: flex;
    justify-content: center;
    padding: 3rem 1.25rem 4rem;
}

.ac-question-wrap {
    width: 100%;
    max-width: 660px;
}

/* ── PAQUET DE CARTES ────────────────────────── */
.ac-deck {
    position: relative;
    min-height: 380px;
    margin-bottom: 2.25rem;
}

.ac-card {
    position: relative;
    z-index: 3;
    background: var(--bg-surface);
    border: 1px solid var(--glass-border-solid);
    border-radius: 16px;
    box-shadow: var(--shadow-card);
    padding: 1.75rem 1.75rem 2rem;
}

/* Cartes fantômes : la pile qui dépasse derrière la carte active */
.ac-ghost {
    position: absolute;
    left: 0;
    right: 0;
    top: 0;
    height: 100%;
    background: var(--bg-surface);
    border: 1px solid var(--glass-border);
    border-radius: 16px;
    box-shadow: var(--shadow-card);
    padding: 1.25rem 1.5rem;
    overflow: hidden;
}

.ac-ghost--1 { transform: translateY(14px) scale(0.965); opacity: 0.85; z-index: 2; }
.ac-ghost--2 { transform: translateY(28px) scale(0.93);  opacity: 0.6;  z-index: 1; }

.ac-ghost-badge {
    font-family: 'Space Mono', monospace;
    font-size: 10px;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: var(--text-secondary);
    opacity: 0.7;
}

/* Animation : la carte s'envole, la suivante remonte de la pile */
.deck-fwd-enter-active, .deck-fwd-leave-active,
.deck-back-enter-active, .deck-back-leave-active {
    transition: transform 0.42s cubic-bezier(0.34, 1.1, 0.64, 1), opacity 0.42s ease;
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    z-index: 3;
}

.deck-fwd-leave-to   { transform: translateX(-118%) rotate(-7deg); opacity: 0; }
.deck-fwd-enter-from { transform: translateY(26px) scale(0.93);    opacity: 0; }
.deck-back-leave-to  { transform: translateX(118%)  rotate(7deg);  opacity: 0; }
.deck-back-enter-from{ transform: translateY(26px) scale(0.93);    opacity: 0; }

@media (prefers-reduced-motion: reduce) {
    .deck-fwd-enter-active, .deck-fwd-leave-active,
    .deck-back-enter-active, .deck-back-leave-active {
        transition: opacity 0.2s ease;
    }
    .deck-fwd-leave-to, .deck-back-leave-to,
    .deck-fwd-enter-from, .deck-back-enter-from { transform: none; }
}

/* ── NAVIGATION (retour / avance) ────────────── */
.ac-nav {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 1.25rem;
}

.ac-nav-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    background: transparent;
    border: 1px solid var(--glass-border);
    border-radius: 8px;
    padding: 0.4rem 0.85rem;
    font-family: 'Space Grotesk', sans-serif;
    font-size: 13px;
    color: var(--text-secondary);
    cursor: pointer;
    transition: border-color 0.15s ease, color 0.15s ease, background 0.15s ease;
}

.ac-nav-btn:hover:not(:disabled) {
    border-color: var(--color-primary);
    color: var(--text-primary);
    background: var(--bg-surface);
}

.ac-nav-btn:disabled {
    opacity: 0.4;
    cursor: not-allowed;
}

/* Badge section */
.ac-section-badge {
    display: inline-block;
    font-family: 'Space Mono', monospace;
    font-size: 10px;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: var(--text-secondary);
    background: var(--bg-surface);
    border: 1px solid var(--glass-border);
    padding: 4px 10px;
    border-radius: 4px;
}

/* Question */
.ac-question-text {
    font-family: 'Space Grotesk', sans-serif;
    font-size: 1.75rem;
    font-weight: 600;
    color: var(--text-primary);
    line-height: 1.25;
    margin-top: 1rem;
    margin-bottom: 0;
}

/* Helper */
.ac-helper {
    font-family: 'Inter', sans-serif;
    font-size: 14px;
    color: var(--text-secondary);
    font-style: italic;
    margin-top: 0.5rem;
}

/* ── OPTIONS ─────────────────────────────────── */
.ac-options {
    margin-top: 2rem;
    display: flex;
    flex-direction: column;
    gap: 0.625rem;
}

/* Conteneur ARIA (radiogroup / group) — conserve l'espacement des cartes */
.ac-option-group {
    display: flex;
    flex-direction: column;
    gap: 0.625rem;
}

/* Single / Multiple cards */
.ac-option-card {
    display: flex;
    align-items: center;
    gap: 0.875rem;
    padding: 1rem 1.25rem;
    border: 1px solid var(--glass-border);
    border-radius: 10px;
    cursor: pointer;
    background: var(--bg-surface);
    transition: border-color 0.15s ease, background 0.15s ease, box-shadow 0.15s ease;
    user-select: none;
}

.ac-option-card:hover {
    border-color: var(--color-primary);
    box-shadow: 0 0 0 1px rgba(166,117,32,0.15);
}

/* Focus clavier visible (a11y — audit UX-1/UX-3) */
.ac-option-card:focus-visible,
.ac-scale-btn:focus-visible,
.ac-nav-btn:focus-visible,
.ac-btn-primary:focus-visible,
.ac-btn-ghost:focus-visible {
    outline: 2px solid var(--color-primary);
    outline-offset: 2px;
}

.ac-option-card--selected {
    border: 2px solid var(--color-primary);
    background: var(--bg-elevated);
}

/* Carte en cours de soumission : curseur d'attente + animation subtile */
.ac-option-card--processing {
    cursor: wait;
    animation: ac-processing-pulse 0.9s ease-in-out infinite;
}

@keyframes ac-processing-pulse {
    0%, 100% { opacity: 1; }
    50%       { opacity: 0.6; }
}

.ac-radio-icon,
.ac-checkbox-icon {
    flex-shrink: 0;
    display: flex;
    align-items: center;
}

.ac-option-label {
    font-family: 'Inter', sans-serif;
    font-size: 14px;
    color: var(--text-primary);
    line-height: 1.45;
}

/* ── EXERCICE GUIDÉ ──────────────────────────── */
.ac-exercise {
    display: flex;
    flex-direction: column;
    gap: 1.25rem;
}

.ac-exercise-steps {
    margin: 0;
    padding-left: 1.25rem;
    display: flex;
    flex-direction: column;
    gap: 0.6rem;
}

.ac-exercise-steps li {
    font-family: 'Inter', sans-serif;
    font-size: 15px;
    line-height: 1.55;
    color: var(--text-primary);
    padding-left: 0.35rem;
}

.ac-exercise-steps li::marker {
    color: var(--color-primary);
    font-family: 'Space Mono', monospace;
    font-weight: 700;
}

.ac-exercise-basis {
    font-family: 'Inter', sans-serif;
    font-size: 13px;
    line-height: 1.55;
    color: var(--text-secondary);
    background: rgba(166,117,32,0.07);
    border-left: 2px solid var(--color-primary);
    border-radius: 0 8px 8px 0;
    padding: 0.75rem 1rem;
    margin: 0;
}

.ac-exercise-basis-kicker {
    display: block;
    font-family: 'Space Mono', monospace;
    font-size: 10px;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: var(--color-primary);
    margin-bottom: 0.35rem;
}

.ac-exercise-actions {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.75rem;
    margin-top: 0.5rem;
}

.ac-btn-ghost {
    background: transparent;
    border: none;
    font-family: 'Space Grotesk', sans-serif;
    font-size: 13px;
    color: var(--text-secondary);
    cursor: pointer;
    padding: 0.4rem 0.85rem;
    transition: color 0.15s ease;
}

.ac-btn-ghost:hover:not(:disabled) {
    color: var(--text-primary);
}

.ac-btn-ghost:disabled {
    opacity: 0.4;
    cursor: not-allowed;
}

/* ── SCALE ───────────────────────────────────── */
.ac-scale-wrap {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    flex-wrap: wrap;
}

.ac-scale-label {
    font-family: 'Inter', sans-serif;
    font-size: 12px;
    color: var(--text-secondary);
    white-space: nowrap;
}

.ac-scale-buttons {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.ac-scale-btn {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    border: 1px solid var(--glass-border);
    background: var(--bg-surface);
    font-family: 'Space Mono', monospace;
    font-size: 14px;
    color: var(--text-primary);
    cursor: pointer;
    transition: border-color 0.15s ease, background 0.15s ease, color 0.15s ease;
    display: flex;
    align-items: center;
    justify-content: center;
}

.ac-scale-btn:hover {
    border-color: var(--color-primary);
}

.ac-scale-btn--active {
    background: var(--color-primary);
    color: #fff;
    border-color: var(--color-primary);
}

.ac-scale-btn--processing {
    cursor: wait;
    animation: ac-processing-pulse 0.9s ease-in-out infinite;
}

/* ── TEXTAREA ─────────────────────────────────── */
.ac-fallback-note {
    font-size: 0.85rem;
    color: var(--text-secondary);
    margin-bottom: 0.75rem;
    font-style: italic;
}
.ac-textarea {
    width: 100%;
    padding: 0.875rem 1rem;
    background: var(--bg-surface);
    border: 1px solid var(--glass-border);
    border-radius: 10px;
    font-family: 'Inter', sans-serif;
    font-size: 14px;
    color: var(--text-primary);
    resize: vertical;
    outline: none;
    transition: border-color 0.15s ease;
}

.ac-textarea::placeholder {
    color: var(--text-secondary);
    opacity: 0.7;
}

.ac-textarea:focus {
    border-color: var(--color-primary);
    box-shadow: 0 0 0 2px rgba(166,117,32,0.12);
}

/* ── BOUTON VALIDER ──────────────────────────── */
.ac-submit-wrap {
    margin-top: 2.5rem;
    display: flex;
    justify-content: center;
}

.ac-btn-primary {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 100%;
    max-width: 320px;
    padding: 0.875rem 2rem;
    background: var(--color-accent);
    color: var(--bg-base);
    font-family: 'Space Grotesk', sans-serif;
    font-size: 15px;
    font-weight: 600;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    letter-spacing: 0.01em;
    transition: background 0.2s ease, opacity 0.2s ease, transform 0.1s ease;
}

.ac-btn-primary:hover:not(.ac-btn-primary--disabled) {
    background: var(--color-primary-dark);
    transform: translateY(-1px);
}

.ac-btn-primary:active:not(.ac-btn-primary--disabled) {
    transform: translateY(0);
}

.ac-btn-primary--disabled {
    opacity: 0.4;
    cursor: not-allowed;
}

/* ── NARRATIVE ───────────────────────────────── */
.ac-narrative {
    display: flex;
    align-items: flex-start;
    gap: 0.5rem;
    margin-top: 1.75rem;
    padding: 0.75rem 1rem;
    background: rgba(166,117,32,0.07);
    border-left: 2px solid var(--color-primary);
    border-radius: 0 6px 6px 0;
}

.ac-narrative-icon {
    flex-shrink: 0;
    margin-top: 1px;
}

.ac-narrative-text {
    font-family: 'Inter', sans-serif;
    font-size: 13px;
    color: var(--text-secondary);
    font-style: italic;
    line-height: 1.5;
}

/* ── CARTE APERÇU (mini-insight) ─────────────── */
.ac-insight {
    margin-top: 1.75rem;
    padding: 1rem 1.15rem;
    background: var(--bg-elevated);
    border: 1px solid var(--glass-border-solid);
    border-radius: 12px;
    box-shadow: var(--shadow-card);
    animation: ac-insight-reveal 0.45s ease both;
}

@keyframes ac-insight-reveal {
    from { opacity: 0; transform: translateY(6px); }
    to   { opacity: 1; transform: translateY(0); }
}

.ac-insight-head {
    display: flex;
    align-items: center;
    gap: 0.45rem;
}

.ac-insight-icon {
    flex-shrink: 0;
}

.ac-insight-kicker {
    font-family: 'Space Mono', monospace;
    font-size: 10px;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: var(--color-primary);
}

.ac-insight-score {
    margin-left: auto;
    font-family: 'Space Mono', monospace;
    font-size: 10px;
    color: var(--text-secondary);
    background: var(--bg-surface);
    border: 1px solid var(--glass-border);
    padding: 3px 8px;
    border-radius: 20px;
}

/* ── ÉTAT VIDE ───────────────────────────────── */
.ac-empty {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 5rem 1.25rem;
    text-align: center;
}

.ac-empty-title {
    font-family: 'Space Grotesk', sans-serif;
    font-size: 1.125rem;
    font-weight: 600;
    color: var(--text-primary);
    margin-bottom: 0.5rem;
}

.ac-empty-sub {
    font-family: 'Inter', sans-serif;
    font-size: 14px;
    color: var(--text-secondary);
}
</style>
