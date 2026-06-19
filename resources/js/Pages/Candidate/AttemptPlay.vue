<script setup>
import { computed, ref } from 'vue'
import { router } from '@inertiajs/vue3'

const props = defineProps({
    attempt: Object,
    progress: Object,
    gamification: Object,
    narrative: Object,
})

const allQuestions = computed(() =>
    props.attempt.test.sections.flatMap(s => s.questions.map(q => ({ ...q, section_title: s.title })))
)

const startIndex = props.attempt.answers.length
const currentIndex = ref(Math.min(startIndex, Math.max(0, allQuestions.value.length - 1)))
const currentQuestion = computed(() => allQuestions.value[currentIndex.value])
const totalQuestions = computed(() => allQuestions.value.length)
const percent = computed(() => totalQuestions.value > 0 ? (currentIndex.value / totalQuestions.value) * 100 : 0)

const value = ref(null)
const startedAt = ref(Date.now())
const isSubmitting = ref(false)

const submit = () => {
    if (isSubmitting.value) return
    if (value.value === null || value.value === '' || (Array.isArray(value.value) && !value.value.length)) return
    isSubmitting.value = true
    const time = Math.round((Date.now() - startedAt.value) / 1000)
    router.post(route('attempt.answer', props.attempt.id), {
        question_id: currentQuestion.value.id,
        value: value.value,
        time_spent: time,
    }, {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
            isSubmitting.value = false
            if (currentIndex.value + 1 >= totalQuestions.value) {
                router.post(route('attempt.complete', props.attempt.id))
            } else {
                currentIndex.value += 1
                value.value = null
                startedAt.value = Date.now()
            }
        },
        onError: () => {
            isSubmitting.value = false
        },
    })
}

const isLastQuestion = computed(() => currentIndex.value + 1 >= totalQuestions.value)
const canSubmit = computed(() => {
    if (isSubmitting.value) return false
    if (value.value === null || value.value === '') return false
    if (Array.isArray(value.value) && !value.value.length) return false
    return true
})

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
</script>

<template>
    <div class="ac-shell">
        <Head :title="attempt.test.name" />

        <!-- HEADER MINIMAL -->
        <header class="ac-header">
            <div class="ac-header-inner">
                <!-- Logo + nom test -->
                <div class="ac-header-left">
                    <span class="ac-logo">P</span>
                    <span class="ac-test-name">{{ attempt.test.name }}</span>
                </div>

                <!-- Numéro question centré -->
                <div class="ac-header-center">
                    <span class="ac-q-counter">Q {{ currentIndex + 1 }} / {{ totalQuestions }}</span>
                </div>

                <!-- Badge gamification -->
                <div class="ac-header-right">
                    <span v-if="gamification" class="ac-xp-badge">
                        Niv.{{ gamification.level }} · {{ gamification.xp }} Éclats
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
                            <div
                                v-for="opt in currentQuestion.options"
                                :key="opt.value"
                                class="ac-option-card"
                                :class="{ 'ac-option-card--selected': value === opt.value }"
                                @click="value = opt.value"
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
                        </template>

                        <!-- SCALE -->
                        <template v-else-if="currentQuestion.type === 'scale'">
                            <div class="ac-scale-wrap">
                                <span class="ac-scale-label">{{ currentQuestion.options?.min_label || 'Pas du tout' }}</span>
                                <div class="ac-scale-buttons">
                                    <button
                                        v-for="n in (currentQuestion.options?.max || 5)"
                                        :key="n"
                                        type="button"
                                        class="ac-scale-btn"
                                        :class="{ 'ac-scale-btn--active': value === n }"
                                        @click="value = n"
                                    >
                                        {{ n }}
                                    </button>
                                </div>
                                <span class="ac-scale-label">{{ currentQuestion.options?.max_label || 'Tout à fait' }}</span>
                            </div>
                        </template>

                        <!-- MULTIPLE CHOICE -->
                        <template v-else-if="currentQuestion.type === 'multi' || currentQuestion.type === 'multiple'">
                            <div
                                v-for="opt in currentQuestion.options"
                                :key="opt.value"
                                class="ac-option-card"
                                :class="{ 'ac-option-card--selected': isMultiSelected(opt.value) }"
                                @click="toggleMultiple(opt.value)"
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
                        </template>

                        <!-- TEXT LIBRE -->
                        <template v-else-if="currentQuestion.type === 'text'">
                            <textarea
                                v-model="value"
                                rows="4"
                                class="ac-textarea"
                                placeholder="Développe ta pensée…"
                            ></textarea>
                        </template>

                    </div>

                    <!-- BOUTON VALIDER -->
                    <div class="ac-submit-wrap">
                        <button
                            class="ac-btn-primary"
                            :class="{ 'ac-btn-primary--disabled': !canSubmit }"
                            :disabled="!canSubmit"
                            @click="submit"
                        >
                            {{ isSubmitting ? '…' : (isLastQuestion ? 'Terminer l\'Épreuve →' : 'Question suivante →') }}
                        </button>
                    </div>

                    <!-- BANDEAU NARRATIVE -->
                    <div v-if="progress?.narrative" class="ac-narrative">
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
}

.ac-logo {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 26px;
    height: 26px;
    background: var(--color-primary);
    color: var(--bg-base);
    font-family: 'Space Grotesk', sans-serif;
    font-size: 13px;
    font-weight: 700;
    border-radius: 6px;
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

.ac-option-card--selected {
    border: 2px solid var(--color-primary);
    background: var(--bg-elevated);
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

/* ── TEXTAREA ─────────────────────────────────── */
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
