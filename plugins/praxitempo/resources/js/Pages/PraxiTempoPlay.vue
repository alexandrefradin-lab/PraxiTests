<script setup>
import { computed, nextTick, ref, watch } from 'vue'
import { router } from '@inertiajs/vue3'
import CandidateLayout from '@/Layouts/CandidateLayout.vue'

/**
 * Passation conversationnelle de PraxiTempo (gestion du temps).
 * Consomme exactement les mêmes props que Candidate/AttemptPlay et poste
 * sur les mêmes routes (attempt.answer / attempt.complete).
 */
const props = defineProps({
    attempt: Object,
    progress: Object,
    gamification: Object,
    narrative: Object,
})

// ── Aplatissement des questions + titre de section ───────────────────────
const allQuestions = computed(() =>
    props.attempt.test.sections.flatMap(s =>
        s.questions.map(q => ({ ...q, section_title: s.title }))
    )
)
const total = computed(() => allQuestions.value.length)

const savedAnswers = ref(
    Object.fromEntries((props.attempt.answers ?? []).map(a => [a.question_id, a.value]))
)
const answeredCount = computed(() => Object.keys(savedAnswers.value).length)

// On reprend là où la personne s'est arrêtée.
const currentIndex = ref(Math.min(answeredCount.value, Math.max(0, total.value - 1)))
const finished = ref(answeredCount.value >= total.value)

const isSubmitting = ref(false)
const isTyping = ref(false)
const startedAt = ref(Date.now())
const scroller = ref(null)

// ── Persona guide ────────────────────────────────────────────────────────
const guideName = 'Tempo'
const firstName = computed(() => {
    const n = props.attempt.user?.name ?? ''
    return n ? String(n).split(' ')[0] : null
})
const introText = computed(() =>
    props.narrative?.intro ||
    `Bonjour${firstName.value ? ' ' + firstName.value : ''} ! Je suis ${guideName}. On va explorer ensemble ta façon de gérer ton temps — pas de bonne ou de mauvaise réponse, réponds au plus juste. C'est parti, ça prend 4 minutes. ⏳`
)

// Échelle de Likert (valeur → libellé) pour les questions à 5 niveaux.
const LIKERT5 = ['Pas du tout', 'Plutôt pas', 'Parfois', 'Plutôt', 'Tout à fait']
const optionsFor = (q) => {
    const max = q?.options?.max ?? 5
    return Array.from({ length: max }, (_, i) => {
        const v = i + 1
        return { value: v, label: max === 5 ? LIKERT5[i] : String(v) }
    })
}

// ── Transcript : tours déjà répondus (pour l'affichage / la reprise) ──────
const history = computed(() => {
    const turns = []
    let lastSection = null
    for (let i = 0; i < currentIndex.value; i++) {
        const q = allQuestions.value[i]
        const v = savedAnswers.value[q.id]
        if (v === undefined) continue
        const sectionChanged = q.section_title !== lastSection
        lastSection = q.section_title
        turns.push({ q, value: v, sectionChanged })
    }
    return turns
})

const currentQuestion = computed(() => (finished.value ? null : allQuestions.value[currentIndex.value]))
const currentSectionIsNew = computed(() => {
    if (!currentQuestion.value) return false
    const prev = currentIndex.value > 0 ? allQuestions.value[currentIndex.value - 1].section_title : null
    return currentQuestion.value.section_title !== prev
})

const sectionTransition = (title) =>
    `Parlons maintenant de : ${title}.`

const labelForValue = (q, v) => {
    const opt = optionsFor(q).find(o => o.value === v)
    return opt ? opt.label : String(v)
}

const percent = computed(() =>
    total.value > 0 ? Math.round((answeredCount.value / total.value) * 100) : 0
)

const scrollToBottom = () => {
    nextTick(() => {
        if (scroller.value) scroller.value.scrollTop = scroller.value.scrollHeight
    })
}
watch([currentIndex, isTyping, finished], scrollToBottom)
scrollToBottom()
startedAt.value = Date.now()

// ── Sélection d'une réponse → enregistre puis avance ─────────────────────
const select = (val) => {
    if (isSubmitting.value || isTyping.value || !currentQuestion.value) return
    const q = currentQuestion.value
    isSubmitting.value = true
    const time = Math.round((Date.now() - startedAt.value) / 1000)

    router.post(route('attempt.answer', props.attempt.id), {
        question_id: q.id,
        value: val,
        time_spent: time,
    }, {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
            savedAnswers.value = { ...savedAnswers.value, [q.id]: val }
            isSubmitting.value = false

            if (currentIndex.value + 1 >= total.value) {
                finished.value = true
                isTyping.value = true
                setTimeout(() => {
                    router.post(route('attempt.complete', props.attempt.id))
                }, 900)
                return
            }

            // Petit effet « le guide réfléchit » avant la question suivante.
            isTyping.value = true
            setTimeout(() => {
                currentIndex.value += 1
                startedAt.value = Date.now()
                isTyping.value = false
            }, 480)
        },
        onError: () => { isSubmitting.value = false },
    })
}

const goBack = () => {
    if (isSubmitting.value || isTyping.value || currentIndex.value === 0) return
    finished.value = false
    currentIndex.value -= 1
    startedAt.value = Date.now()
}
</script>

<template>
    <CandidateLayout title="Maître du Temps">
        <div class="tempo">
            <!-- Barre de progression fixe -->
            <div class="tempo__progress">
                <div class="tempo__progress-meta">
                    <span class="tempo__progress-section">
                        {{ currentQuestion ? currentQuestion.section_title : 'Terminé' }}
                    </span>
                    <span class="tempo__progress-count">{{ answeredCount }} / {{ total }}</span>
                </div>
                <div class="tempo__progress-track">
                    <div class="tempo__progress-fill" :style="{ width: percent + '%' }"></div>
                </div>
            </div>

            <!-- Fil de conversation -->
            <div ref="scroller" class="tempo__chat">
                <!-- Intro -->
                <div class="tempo__row tempo__row--guide">
                    <div class="tempo__avatar" aria-hidden="true">🕰️</div>
                    <div class="tempo__bubble tempo__bubble--guide">{{ introText }}</div>
                </div>

                <!-- Tours déjà répondus -->
                <template v-for="(turn, i) in history" :key="'h' + i">
                    <div v-if="turn.sectionChanged" class="tempo__divider">
                        <span>{{ turn.q.section_title }}</span>
                    </div>
                    <div class="tempo__row tempo__row--guide">
                        <div class="tempo__avatar" aria-hidden="true">🕰️</div>
                        <div class="tempo__bubble tempo__bubble--guide">{{ turn.q.prompt }}</div>
                    </div>
                    <div class="tempo__row tempo__row--user">
                        <div class="tempo__bubble tempo__bubble--user">{{ labelForValue(turn.q, turn.value) }}</div>
                    </div>
                </template>

                <!-- Question courante -->
                <template v-if="currentQuestion">
                    <div v-if="currentSectionIsNew && currentIndex > 0" class="tempo__divider">
                        <span>{{ currentQuestion.section_title }}</span>
                    </div>
                    <div v-if="currentSectionIsNew" class="tempo__row tempo__row--guide">
                        <div class="tempo__avatar" aria-hidden="true">🕰️</div>
                        <div class="tempo__bubble tempo__bubble--guide tempo__bubble--soft">
                            {{ sectionTransition(currentQuestion.section_title) }}
                        </div>
                    </div>

                    <div v-if="!isTyping" class="tempo__row tempo__row--guide">
                        <div class="tempo__avatar" aria-hidden="true">🕰️</div>
                        <div class="tempo__bubble tempo__bubble--guide">{{ currentQuestion.prompt }}</div>
                    </div>
                </template>

                <!-- Indicateur de saisie -->
                <div v-if="isTyping" class="tempo__row tempo__row--guide">
                    <div class="tempo__avatar" aria-hidden="true">🕰️</div>
                    <div class="tempo__bubble tempo__bubble--guide tempo__typing">
                        <span></span><span></span><span></span>
                    </div>
                </div>

                <!-- Fin -->
                <div v-if="finished && !isTyping" class="tempo__row tempo__row--guide">
                    <div class="tempo__avatar" aria-hidden="true">🕰️</div>
                    <div class="tempo__bubble tempo__bubble--guide">
                        Merci ! J'analyse tes réponses et je te révèle ton profil… ✨
                    </div>
                </div>
            </div>

            <!-- Zone de réponse -->
            <div class="tempo__answer" v-if="currentQuestion && !isTyping && !finished">
                <div class="tempo__chips">
                    <button
                        v-for="opt in optionsFor(currentQuestion)"
                        :key="opt.value"
                        type="button"
                        class="tempo__chip"
                        :disabled="isSubmitting"
                        @click="select(opt.value)"
                    >
                        {{ opt.label }}
                    </button>
                </div>
                <button
                    v-if="currentIndex > 0"
                    type="button"
                    class="tempo__back"
                    :disabled="isSubmitting"
                    @click="goBack"
                >← revenir à la précédente</button>
            </div>
        </div>
    </CandidateLayout>
</template>

<style scoped>
.tempo {
    max-width: 720px;
    margin: 0 auto;
    display: flex;
    flex-direction: column;
    min-height: calc(100vh - 120px);
}

/* Progression */
.tempo__progress {
    position: sticky;
    top: 0;
    z-index: 5;
    padding: 14px 4px 12px;
    background: var(--pt-bg, #faf7f1);
}
.tempo__progress-meta {
    display: flex;
    justify-content: space-between;
    font-size: 0.8rem;
    color: var(--pt-ink-soft, #6b7280);
    margin-bottom: 6px;
}
.tempo__progress-section { font-weight: 600; color: var(--pt-ink, #1b2b3a); }
.tempo__progress-track {
    height: 8px;
    border-radius: 999px;
    background: var(--pt-cream, #ece6da);
    overflow: hidden;
}
.tempo__progress-fill {
    height: 100%;
    border-radius: 999px;
    background: var(--pt-gold, #b8913a);
    transition: width 0.5s ease;
}

/* Conversation */
.tempo__chat {
    flex: 1;
    overflow-y: auto;
    padding: 18px 4px 8px;
    display: flex;
    flex-direction: column;
    gap: 10px;
}
.tempo__row { display: flex; align-items: flex-end; gap: 8px; }
.tempo__row--guide { justify-content: flex-start; }
.tempo__row--user { justify-content: flex-end; }
.tempo__avatar {
    flex: 0 0 auto;
    width: 34px; height: 34px;
    border-radius: 50%;
    display: grid; place-items: center;
    background: var(--pt-cream, #ece6da);
    font-size: 1.1rem;
}
.tempo__bubble {
    max-width: 78%;
    padding: 11px 15px;
    border-radius: 18px;
    line-height: 1.45;
    font-size: 0.98rem;
    animation: tempo-pop 0.22s ease;
}
.tempo__bubble--guide {
    background: #fff;
    color: var(--pt-ink, #1b2b3a);
    border: 1px solid var(--pt-cream, #ece6da);
    border-bottom-left-radius: 6px;
}
.tempo__bubble--soft { font-style: italic; color: var(--pt-ink-soft, #6b7280); }
.tempo__bubble--user {
    background: var(--pt-gold, #b8913a);
    color: #fff;
    border-bottom-right-radius: 6px;
}
.tempo__divider {
    text-align: center;
    margin: 10px 0 2px;
}
.tempo__divider span {
    font-size: 0.72rem;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: var(--pt-ink-soft, #6b7280);
    background: var(--pt-cream, #ece6da);
    padding: 3px 12px;
    border-radius: 999px;
}

/* Indicateur de saisie */
.tempo__typing { display: inline-flex; gap: 4px; align-items: center; }
.tempo__typing span {
    width: 7px; height: 7px; border-radius: 50%;
    background: var(--pt-ink-soft, #9aa3af);
    animation: tempo-blink 1.2s infinite ease-in-out;
}
.tempo__typing span:nth-child(2) { animation-delay: 0.2s; }
.tempo__typing span:nth-child(3) { animation-delay: 0.4s; }

/* Réponses */
.tempo__answer {
    position: sticky;
    bottom: 0;
    padding: 12px 4px 18px;
    background: linear-gradient(to top, var(--pt-bg, #faf7f1) 70%, transparent);
}
.tempo__chips {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    justify-content: center;
}
.tempo__chip {
    flex: 1 1 auto;
    min-width: 120px;
    max-width: 220px;
    padding: 12px 14px;
    border-radius: 14px;
    border: 1.5px solid var(--pt-cream, #ddd5c6);
    background: #fff;
    color: var(--pt-ink, #1b2b3a);
    font-size: 0.95rem;
    font-weight: 500;
    cursor: pointer;
    transition: transform 0.08s ease, border-color 0.15s ease, background 0.15s ease;
}
.tempo__chip:hover:not(:disabled) {
    border-color: var(--pt-gold, #b8913a);
    background: var(--pt-gold-soft, #f6efe0);
}
.tempo__chip:active:not(:disabled) { transform: scale(0.97); }
.tempo__chip:disabled { opacity: 0.5; cursor: default; }
.tempo__back {
    display: block;
    margin: 10px auto 0;
    background: none;
    border: none;
    color: var(--pt-ink-soft, #6b7280);
    font-size: 0.82rem;
    cursor: pointer;
}
.tempo__back:hover:not(:disabled) { color: var(--pt-ink, #1b2b3a); text-decoration: underline; }

@keyframes tempo-pop {
    from { opacity: 0; transform: translateY(6px); }
    to   { opacity: 1; transform: translateY(0); }
}
@keyframes tempo-blink {
    0%, 80%, 100% { opacity: 0.3; transform: translateY(0); }
    40%           { opacity: 1; transform: translateY(-3px); }
}
</style>
