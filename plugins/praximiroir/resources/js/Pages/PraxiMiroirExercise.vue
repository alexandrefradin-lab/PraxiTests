<script setup>
import { ref } from 'vue'
import { Link, Head, useForm } from '@inertiajs/vue3'
import CandidateLayout from '@/Layouts/CandidateLayout.vue'
import MarkdownText from '@/Components/MarkdownText.vue'

const props = defineProps({
    exercise: { type: Object, required: true },
    state:    { type: Object, default: () => ({}) },
    nav:      { type: Object, default: () => ({}) },
    eclatsPerExercise: { type: Number, default: 20 },
})

const form = useForm({
    reflection: props.state.reflection ?? '',
    felt_score: props.state.felt_score ?? null,
})

const submitted = ref(props.state.completed ?? false)

function submit() {
    form.post(route('praximiroir.complete', props.exercise.day), {
        onSuccess: () => { submitted.value = true },
    })
}

const iconFor = (name) => ({
    camera:      'ti-camera',
    mountain:    'ti-mountain',
    bolt:        'ti-bolt',
    users:       'ti-users',
    shield:      'ti-shield',
    'check-circle': 'ti-circle-check',
    anchor:      'ti-anchor',
    compass:     'ti-compass',
    seedling:    'ti-plant',
    message:     'ti-message',
    gem:         'ti-diamond',
    fingerprint: 'ti-fingerprint',
    'eye-off':   'ti-eye-off',
    needle:      'ti-needle',
    ghost:       'ti-ghost',
    layers:      'ti-layers',
    timeline:    'ti-timeline',
    thread:      'ti-needle-thread',
    'book-open': 'ti-book-2',
    'book-plus': 'ti-book-plus',
    'list-check':'ti-list-check',
    mask:        'ti-masks-theater',
    key:         'ti-key',
    mail:        'ti-mail',
    sun:         'ti-sun',
    search:      'ti-search',
    star:        'ti-star',
    badge:       'ti-badge',
    layout:      'ti-layout-dashboard',
    heart:       'ti-heart',
    mirror:      'ti-sparkles',
}[name] ?? 'ti-sparkles')

const moodLabels = ['😔 Difficile', '😐 Neutre', '🙂 Bien', '😊 Très bien', '✨ Transformateur']
</script>

<template>
    <CandidateLayout>
        <Head :title="exercise.title" />

        <div class="pme-shell">

            <!-- Breadcrumb -->
            <div class="pme-breadcrumb">
                <Link :href="route('praximiroir.index')" class="pme-back">
                    <i class="ti ti-arrow-left" aria-hidden="true"></i>
                    La Forge
                </Link>
                <span class="pme-breadcrumb-sep">/</span>
                <span class="pme-breadcrumb-bloc">{{ exercise.bloc }}</span>
            </div>

            <!-- En-tête exercice -->
            <div class="pme-header">
                <div class="pme-header-icon">
                    <i class="ti" :class="iconFor(exercise.icon)" aria-hidden="true"></i>
                </div>
                <div class="pme-header-body">
                    <div class="pme-eyebrow">Jour {{ exercise.day }} · {{ exercise.bloc }} · {{ exercise.duration_min }} min</div>
                    <h1 class="pme-title">{{ exercise.title }}</h1>
                    <p class="pme-summary">{{ exercise.summary }}</p>
                </div>
            </div>

            <!-- Contenu -->
            <div class="pme-body">
                <MarkdownText :content="exercise.body" />
            </div>

            <!-- Prompt de réflexion -->
            <div class="pme-prompt-block">
                <div class="pme-prompt-label">
                    <i class="ti ti-pencil" aria-hidden="true"></i>
                    Ta réflexion
                </div>
                <div class="pme-prompt-question">{{ exercise.prompt }}</div>

                <form @submit.prevent="submit">
                    <textarea
                        v-model="form.reflection"
                        class="pme-textarea"
                        rows="8"
                        placeholder="Écris librement ici — c'est ton espace, personne d'autre ne le lit."
                        :disabled="submitted && !form.isDirty"
                    ></textarea>

                    <!-- Ressenti -->
                    <div class="pme-felt">
                        <div class="pme-felt-label">Comment tu te sens après cet exercice ?</div>
                        <div class="pme-felt-options">
                            <button
                                v-for="(label, i) in moodLabels"
                                :key="i"
                                type="button"
                                class="pme-felt-btn"
                                :class="{ active: form.felt_score === i + 1 }"
                                @click="form.felt_score = form.felt_score === i + 1 ? null : i + 1"
                            >{{ label }}</button>
                        </div>
                    </div>

                    <div class="pme-submit-row">
                        <button
                            type="submit"
                            class="pme-btn-primary"
                            :disabled="form.processing"
                        >
                            <i v-if="submitted && !form.isDirty" class="ti ti-check" aria-hidden="true"></i>
                            <span v-if="submitted && !form.isDirty">Accompli · +{{ eclatsPerExercise }} Éclats</span>
                            <span v-else-if="submitted">Mettre à jour ma réflexion</span>
                            <span v-else>Valider l'exercice · +{{ eclatsPerExercise }} Éclats</span>
                        </button>
                    </div>
                </form>
            </div>

            <!-- Navigation -->
            <div class="pme-nav">
                <Link
                    v-if="nav.prev"
                    :href="route('praximiroir.show', nav.prev)"
                    class="pme-nav-btn"
                >
                    <i class="ti ti-arrow-left" aria-hidden="true"></i>
                    Jour {{ nav.prev }}
                </Link>
                <span v-else class="pme-nav-btn pme-nav-btn--ghost"></span>

                <Link
                    v-if="nav.next"
                    :href="route('praximiroir.show', nav.next)"
                    class="pme-nav-btn pme-nav-btn--next"
                >
                    Jour {{ nav.next }}
                    <i class="ti ti-arrow-right" aria-hidden="true"></i>
                </Link>
                <span v-else class="pme-nav-btn pme-nav-btn--ghost"></span>
            </div>

        </div>
    </CandidateLayout>
</template>

<style scoped>
.pme-shell { max-width: 680px; margin: 0 auto; padding: 0 0 4rem; }

.pme-breadcrumb { display: flex; align-items: center; gap: 0.4rem; padding: 1.25rem 0 0.75rem; }
.pme-back { display: flex; align-items: center; gap: 4px; font-size: 0.82rem; color: #6B3FA0; text-decoration: none; font-weight: 500; }
.pme-back:hover { opacity: 0.75; }
.pme-breadcrumb-sep { color: var(--text-muted, #ccc); font-size: 0.82rem; }
.pme-breadcrumb-bloc { font-size: 0.82rem; color: var(--text-muted, #aaa); }

.pme-header { display: flex; gap: 1rem; align-items: flex-start; padding: 1rem 0 1.25rem; border-bottom: 1px solid var(--glass-border, #e5e7eb); margin-bottom: 1.5rem; }
.pme-header-icon { width: 3rem; height: 3rem; border-radius: 12px; background: rgba(107,63,160,0.1); display: flex; align-items: center; justify-content: center; flex-shrink: 0; color: #6B3FA0; font-size: 1.4rem; }
.pme-header-body { flex: 1; min-width: 0; }
.pme-eyebrow { font-size: 0.7rem; color: var(--text-muted, #aaa); text-transform: uppercase; letter-spacing: 0.06em; margin-bottom: 4px; }
.pme-title { font-size: 1.35rem; font-weight: 600; color: var(--text-primary); font-family: var(--font-display); line-height: 1.3; margin: 0 0 6px; }
.pme-summary { font-size: 0.88rem; color: var(--text-secondary); line-height: 1.55; margin: 0; }

.pme-body { margin-bottom: 2rem; line-height: 1.7; color: var(--text-primary); }
.pme-body :deep(h2) { font-size: 1rem; font-weight: 600; color: #6B3FA0; margin: 1.5rem 0 0.5rem; font-family: var(--font-display); }
.pme-body :deep(p) { margin: 0 0 0.85rem; font-size: 0.88rem; color: var(--text-secondary); }
.pme-body :deep(ul), .pme-body :deep(ol) { padding-left: 1.25rem; margin: 0 0 0.85rem; }
.pme-body :deep(li) { font-size: 0.88rem; color: var(--text-secondary); margin-bottom: 0.3rem; }
.pme-body :deep(strong) { color: var(--text-primary); font-weight: 600; }
.pme-body :deep(em) { font-style: italic; color: var(--text-secondary); }

.pme-prompt-block { background: rgba(107,63,160,0.04); border: 1px solid rgba(107,63,160,0.2); border-radius: 12px; padding: 1.25rem; margin-bottom: 2rem; }
.pme-prompt-label { display: flex; align-items: center; gap: 5px; font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.07em; color: #6B3FA0; font-weight: 600; margin-bottom: 0.75rem; }
.pme-prompt-question { font-size: 0.92rem; color: var(--text-primary); font-weight: 500; line-height: 1.55; margin-bottom: 1rem; font-style: italic; }
.pme-textarea { width: 100%; border: 1px solid rgba(107,63,160,0.25); border-radius: 8px; padding: 0.75rem 0.9rem; font-size: 0.88rem; color: var(--text-primary); background: var(--bg-surface, #fff); resize: vertical; line-height: 1.6; outline: none; box-sizing: border-box; font-family: inherit; }
.pme-textarea:focus { border-color: #6B3FA0; box-shadow: 0 0 0 3px rgba(107,63,160,0.1); }
.pme-textarea:disabled { opacity: 0.7; cursor: default; }

.pme-felt { margin-top: 1rem; }
.pme-felt-label { font-size: 0.75rem; color: var(--text-muted, #aaa); margin-bottom: 0.5rem; }
.pme-felt-options { display: flex; gap: 0.4rem; flex-wrap: wrap; }
.pme-felt-btn { border: 1px solid var(--glass-border, #e5e7eb); border-radius: 999px; padding: 4px 10px; font-size: 0.75rem; background: var(--bg-surface, #fff); color: var(--text-secondary); cursor: pointer; transition: all 0.15s; }
.pme-felt-btn:hover { border-color: #6B3FA0; color: #6B3FA0; }
.pme-felt-btn.active { background: #6B3FA0; border-color: #6B3FA0; color: #fff; }

.pme-submit-row { margin-top: 1rem; }
.pme-btn-primary { display: inline-flex; align-items: center; gap: 6px; background: #6B3FA0; color: #fff; border: none; border-radius: 8px; padding: 0.65rem 1.25rem; font-size: 0.88rem; font-weight: 600; cursor: pointer; transition: opacity 0.15s; }
.pme-btn-primary:hover { opacity: 0.88; }
.pme-btn-primary:disabled { opacity: 0.6; cursor: not-allowed; }

.pme-nav { display: flex; justify-content: space-between; align-items: center; padding-top: 1.5rem; border-top: 1px solid var(--glass-border, #e5e7eb); }
.pme-nav-btn { display: inline-flex; align-items: center; gap: 5px; font-size: 0.82rem; font-weight: 500; color: #6B3FA0; text-decoration: none; border: 1px solid rgba(107,63,160,0.25); border-radius: 8px; padding: 0.45rem 0.9rem; transition: all 0.15s; }
.pme-nav-btn:hover { background: rgba(107,63,160,0.06); }
.pme-nav-btn--next { margin-left: auto; }
.pme-nav-btn--ghost { visibility: hidden; }
</style>
