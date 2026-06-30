<script setup>
import { ref } from 'vue'
import { Link, Head, useForm } from '@inertiajs/vue3'
import CandidateLayout from '@/Layouts/CandidateLayout.vue'
import MarkdownText from '@/Components/MarkdownText.vue'

const props = defineProps({
    exercise:          { type: Object, required: true },
    state:             { type: Object, default: () => ({}) },
    nav:               { type: Object, default: () => ({ prev: null, next: null }) },
    eclatsPerExercise: { type: Number, default: 15 },
})

const iconFor = (name) => ({
    compass: 'ti-compass', target: 'ti-target', ear: 'ti-ear', message: 'ti-message',
    handshake: 'ti-handshake', gift: 'ti-gift', flame: 'ti-flame', shield: 'ti-shield',
    scale: 'ti-scale', users: 'ti-users', clock: 'ti-clock', book: 'ti-book',
    heart: 'ti-heart', rocket: 'ti-rocket', eye: 'ti-eye', seedling: 'ti-plant',
    anchor: 'ti-anchor', map: 'ti-map', lightbulb: 'ti-bulb', sun: 'ti-sun',
}[name] ?? 'ti-sparkles')

const form = useForm({
    felt_score: props.state?.felt_score ?? null,
    notes: props.state?.notes ?? '',
})

const done = ref(props.state?.completed ?? false)

const submit = () => {
    const wasDone = done.value
    form.post(route('praxizenith.complete', props.exercise.day), {
        preserveScroll: true,
        onSuccess: () => {
            done.value = true
            if (!wasDone) {
                import('canvas-confetti').then(({ default: confetti }) => {
                    confetti({ particleCount: 90, spread: 70, origin: { y: 0.7 } })
                }).catch(() => {})
            }
        },
    })
}
</script>

<template>
    <CandidateLayout>
        <Head :title="`Jour ${exercise.day} — ${exercise.title}`" />

        <div class="pze-shell">

            <div class="pze-topbar">
                <Link :href="route('praxizenith.index')" class="pze-back" style="text-decoration:none;">
                    <i class="ti ti-arrow-left" aria-hidden="true"></i>
                    Le Sanctuaire
                </Link>
                <div class="pze-topbar-center">
                    <div class="pze-day-label">Jour {{ exercise.day }} / 60</div>
                    <div class="pze-bloc-label">{{ exercise.theme }}</div>
                </div>
                <div class="pze-xp-pill">
                    <i class="ti ti-sparkles" aria-hidden="true"></i>
                    + {{ eclatsPerExercise }} Eclats
                </div>
            </div>

            <div class="pze-hero">
                <div class="pze-hero-icon">
                    <i class="ti" :class="iconFor(exercise.icon)" aria-hidden="true"></i>
                </div>
                <h1 class="pze-hero-title">{{ exercise.title }}</h1>
                <p class="pze-hero-summary">{{ exercise.summary }}</p>
                <div class="pze-hero-meta">
                    <span class="pze-meta-chip">
                        <i class="ti ti-clock" aria-hidden="true"></i> {{ exercise.duration_min }} min
                    </span>
                    <span v-if="done" class="pze-meta-chip pze-meta-chip--done">
                        <i class="ti ti-check" aria-hidden="true"></i> Integre
                    </span>
                </div>
            </div>

            <div class="pze-section-label">Contenu de l'exercice</div>

            <div class="pze-body-card">
                <MarkdownText :source="exercise.body" class="pze-prose" />
            </div>

            <div v-if="exercise.micro_challenge">
                <div class="pze-section-label pze-section-label--mt">Micro-defi du jour</div>
                <div class="pze-challenge">
                    <div class="pze-challenge-icon">
                        <i class="ti ti-bolt" aria-hidden="true"></i>
                    </div>
                    <div class="pze-challenge-body">
                        <div class="pze-challenge-label">Application concrete</div>
                        <div class="pze-challenge-text">{{ exercise.micro_challenge }}</div>
                    </div>
                </div>
            </div>

            <div class="pze-section-label pze-section-label--mt">
                {{ done ? 'Mettre a jour mon ressenti' : 'Valider l\'exercice' }}
            </div>

            <div class="pze-completion">
                <div v-if="done" class="pze-done-badge">
                    <i class="ti ti-circle-check" aria-hidden="true"></i> Exercice integre
                </div>
                <p v-else class="pze-completion-hint">
                    Marque cet exercice comme integre pour gagner {{ eclatsPerExercise }} Eclats.
                </p>

                <div class="pze-felt-row">
                    <span class="pze-felt-label">Ressenti</span>
                    <div class="pze-felt-btns">
                        <button
                            v-for="n in 5" :key="n"
                            type="button"
                            @click="form.felt_score = n"
                            class="pze-felt-btn"
                            :class="{ 'is-active': form.felt_score === n }"
                        >{{ n }}</button>
                    </div>
                </div>

                <textarea
                    v-model="form.notes"
                    rows="3"
                    class="pze-notes"
                    placeholder="Ce que tu as teste, ce que ca a revele..."
                ></textarea>

                <button
                    @click="submit"
                    :disabled="form.processing"
                    class="pze-submit-btn"
                    :class="{ 'is-done': done }"
                >
                    <i class="ti" :class="done ? 'ti-refresh' : 'ti-check'" aria-hidden="true"></i>
                    {{ done ? 'Enregistrer le ressenti' : 'Marquer comme integre' }}
                </button>
            </div>

            <div class="pze-nav">
                <Link
                    v-if="nav.prev"
                    :href="route('praxizenith.show', nav.prev)"
                    class="pze-nav-link"
                    style="text-decoration:none;"
                >
                    <i class="ti ti-arrow-left" aria-hidden="true"></i> Jour {{ nav.prev }}
                </Link>
                <span v-else></span>

                <Link
                    v-if="nav.next"
                    :href="route('praxizenith.show', nav.next)"
                    class="pze-nav-link pze-nav-link--next"
                    style="text-decoration:none;"
                >
                    Jour {{ nav.next }} <i class="ti ti-arrow-right" aria-hidden="true"></i>
                </Link>
                <span v-else class="pze-nav-end">Le prochain exercice se debloque demain</span>
            </div>

        </div>
    </CandidateLayout>
</template>

<style scoped>
.pze-shell { max-width: 640px; margin: 0 auto; padding: 0 0 3rem; }
.pze-topbar { display: flex; align-items: center; justify-content: space-between; padding: 1.25rem 0 1rem; gap: 0.5rem; }
.pze-back { display: flex; align-items: center; gap: 5px; font-size: 0.82rem; color: var(--text-secondary); flex-shrink: 0; }
.pze-topbar-center { text-align: center; flex: 1; }
.pze-day-label { font-size: 0.82rem; font-weight: 600; color: var(--text-primary); font-family: var(--font-data); }
.pze-bloc-label { font-size: 0.68rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em; margin-top: 1px; }
.pze-xp-pill { display: flex; align-items: center; gap: 4px; font-size: 0.72rem; font-weight: 500; color: var(--color-primary-dark, #7D5010); background: rgba(184,122,26,0.1); border-radius: 999px; padding: 4px 10px; flex-shrink: 0; }
.pze-hero { padding: 1.25rem 0; border-bottom: 1px solid var(--glass-border, #e5e7eb); margin-bottom: 1.25rem; }
.pze-hero-icon { width: 48px; height: 48px; border-radius: 12px; background: rgba(184,122,26,0.1); display: flex; align-items: center; justify-content: center; font-size: 1.4rem; color: var(--color-primary, #B87A1A); margin-bottom: 0.75rem; }
.pze-hero-title { font-family: var(--font-display); font-size: 1.6rem; font-weight: 700; color: var(--text-primary); letter-spacing: -0.02em; line-height: 1.2; margin-bottom: 0.5rem; }
.pze-hero-summary { font-size: 0.95rem; color: var(--text-secondary); line-height: 1.6; margin-bottom: 0.75rem; font-style: italic; }
.pze-hero-meta { display: flex; gap: 6px; flex-wrap: wrap; }
.pze-meta-chip { display: inline-flex; align-items: center; gap: 4px; font-size: 0.72rem; padding: 3px 9px; border-radius: 999px; background: var(--bg-elevated, #eee); color: var(--text-secondary); font-weight: 500; }
.pze-meta-chip--done { background: rgba(16,185,129,0.1); color: #065F46; }
.pze-section-label { font-size: 0.68rem; text-transform: uppercase; letter-spacing: 0.07em; color: var(--text-muted, #aaa); margin-bottom: 0.65rem; }
.pze-section-label--mt { margin-top: 1.5rem; }
.pze-body-card { border: 1px solid var(--glass-border, #e5e7eb); border-radius: 12px; padding: 1.1rem 1.25rem; background: var(--bg-elevated, #fafafa); }
.pze-prose :deep(h2) { font-family: var(--font-display); font-weight: 700; font-size: 1.1rem; margin: 1.25rem 0 0.5rem; color: var(--text-primary); }
.pze-prose :deep(h3) { font-family: var(--font-display); font-weight: 700; font-size: 0.95rem; margin: 1rem 0 0.4rem; color: var(--text-primary); }
.pze-prose :deep(p) { font-size: 0.88rem; color: var(--text-primary); line-height: 1.7; margin: 0.45rem 0; }
.pze-prose :deep(ul), .pze-prose :deep(ol) { margin: 0.5rem 0 0.5rem 1.1rem; }
.pze-prose :deep(li) { font-size: 0.88rem; color: var(--text-primary); line-height: 1.6; margin: 0.25rem 0; }
.pze-prose :deep(strong) { color: var(--text-primary); }
.pze-challenge { display: flex; gap: 0.9rem; align-items: flex-start; border: 1px dashed var(--color-primary, #B87A1A); border-radius: 12px; padding: 1rem 1.1rem; background: rgba(184,122,26,0.04); }
.pze-challenge-icon { width: 36px; height: 36px; border-radius: 8px; background: rgba(184,122,26,0.12); display: flex; align-items: center; justify-content: center; font-size: 1rem; color: var(--color-primary, #B87A1A); flex-shrink: 0; }
.pze-challenge-label { font-size: 0.68rem; text-transform: uppercase; letter-spacing: 0.07em; color: var(--color-primary, #B87A1A); font-weight: 700; margin-bottom: 5px; }
.pze-challenge-text { font-size: 0.92rem; color: var(--text-primary); line-height: 1.6; }
.pze-completion { border: 1px solid var(--glass-border, #e5e7eb); border-radius: 12px; padding: 1.1rem 1.25rem; background: var(--bg-elevated, #fafafa); }
.pze-done-badge { display: inline-flex; align-items: center; gap: 6px; font-size: 0.85rem; font-weight: 600; color: #059669; margin-bottom: 0.75rem; }
.pze-completion-hint { font-size: 0.82rem; color: var(--text-secondary); margin-bottom: 0.9rem; }
.pze-felt-row { display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.75rem; }
.pze-felt-label { font-size: 0.8rem; color: var(--text-secondary); white-space: nowrap; }
.pze-felt-btns { display: flex; gap: 6px; }
.pze-felt-btn { width: 36px; height: 36px; border-radius: 8px; border: 1px solid var(--glass-border, #e5e7eb); background: transparent; color: var(--text-secondary); font-weight: 600; font-size: 0.85rem; cursor: pointer; }
.pze-felt-btn.is-active { background: var(--color-primary, #B87A1A); border-color: var(--color-primary, #B87A1A); color: #fff; }
.pze-notes { width: 100%; border: 1px solid var(--glass-border, #e5e7eb); border-radius: 8px; padding: 0.6rem 0.75rem; font-family: var(--font-body); font-size: 0.85rem; resize: vertical; margin-bottom: 0.9rem; background: var(--bg-base, #fff); color: var(--text-primary); }
.pze-submit-btn { display: inline-flex; align-items: center; gap: 6px; background: var(--color-primary, #B87A1A); color: #fff; border: none; border-radius: 999px; padding: 0.65rem 1.4rem; font-size: 0.88rem; font-weight: 600; cursor: pointer; }
.pze-submit-btn:disabled { opacity: 0.6; cursor: default; }
.pze-submit-btn.is-done { background: var(--text-secondary); }
.pze-nav { display: flex; align-items: center; justify-content: space-between; margin-top: 2rem; padding-top: 1rem; border-top: 1px solid var(--glass-border, #e5e7eb); }
.pze-nav-link { display: flex; align-items: center; gap: 5px; font-size: 0.85rem; color: var(--text-secondary); }
.pze-nav-link--next { color: var(--color-primary, #B87A1A); font-weight: 600; }
.pze-nav-end { font-size: 0.78rem; color: var(--text-muted); }
</style>
