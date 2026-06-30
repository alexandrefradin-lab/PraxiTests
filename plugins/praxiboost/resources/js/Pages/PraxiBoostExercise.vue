<script setup>
import { ref } from 'vue'
import { Link, Head, useForm } from '@inertiajs/vue3'
import CandidateLayout from '@/Layouts/CandidateLayout.vue'
import MarkdownText from '@/Components/MarkdownText.vue'

const props = defineProps({
    exercise: { type: Object, required: true },
    state:    { type: Object, default: () => ({}) },
})

const iconFor = (name) => ({
    sparkles:      'ti-sparkles',
    'heart-pulse': 'ti-heartbeat',
    brain:         'ti-brain',
    compass:       'ti-compass',
    rocket:        'ti-rocket',
}[name] ?? 'ti-sparkles')

const form = useForm({
    felt_score: props.state?.felt_score ?? null,
    notes: props.state?.notes ?? '',
})

const done = ref(props.state?.completed ?? false)

const submit = () => {
    form.post(route('praxiboost.complete', props.exercise.slug), {
        preserveScroll: true,
        onSuccess: () => {
            done.value = true
            import('canvas-confetti').then(({ default: confetti }) => {
                confetti({ particleCount: 90, spread: 70, origin: { y: 0.7 } })
            }).catch(() => {})
        },
    })
}
</script>

<template>
    <CandidateLayout>
        <Head :title="exercise.title" />

        <div class="pbe-shell">

            <div class="pbe-topbar">
                <Link :href="route('praxiboost.index')" class="pbe-back" style="text-decoration:none;">
                    <i class="ti ti-arrow-left" aria-hidden="true"></i>
                    L'Etincelle
                </Link>
                <div class="pbe-topbar-center">
                    <div class="pbe-category-label">{{ exercise.category }}</div>
                </div>
                <div class="pbe-meta-pill">
                    <i class="ti ti-clock" aria-hidden="true"></i>
                    {{ exercise.duration_min }} min
                </div>
            </div>

            <div class="pbe-hero">
                <div class="pbe-hero-icon">
                    <i class="ti" :class="iconFor(exercise.icon)" aria-hidden="true"></i>
                </div>
                <h1 class="pbe-hero-title">{{ exercise.title }}</h1>
                <p v-if="exercise.summary" class="pbe-hero-summary">{{ exercise.summary }}</p>
                <div class="pbe-hero-meta">
                    <span v-if="done" class="pbe-meta-chip pbe-meta-chip--done">
                        <i class="ti ti-check" aria-hidden="true"></i> Exercice fait
                    </span>
                </div>
            </div>

            <div class="pbe-section-label">Contenu de l'exercice</div>

            <div class="pbe-body-card">
                <MarkdownText :source="exercise.body" class="pbe-prose" />
            </div>

            <div class="pbe-section-label pbe-section-label--mt">
                {{ done ? 'Mettre a jour mon ressenti' : 'Tu as termine cet exercice ?' }}
            </div>

            <div class="pbe-completion">
                <div v-if="done" class="pbe-done-badge">
                    <i class="ti ti-circle-check" aria-hidden="true"></i> Exercice marque comme fait
                </div>

                <div class="pbe-felt-row">
                    <span class="pbe-felt-label">Ressenti</span>
                    <div class="pbe-felt-btns">
                        <button
                            v-for="n in 5" :key="n"
                            type="button"
                            @click="form.felt_score = n"
                            class="pbe-felt-btn"
                            :class="{ 'is-active': form.felt_score === n }"
                        >{{ n }}</button>
                    </div>
                </div>

                <textarea
                    v-model="form.notes"
                    rows="3"
                    class="pbe-notes"
                    placeholder="Ce que tu retiens, ce que tu ressens..."
                ></textarea>

                <button
                    @click="submit"
                    :disabled="form.processing"
                    class="pbe-submit-btn"
                    :class="{ 'is-done': done }"
                >
                    <i class="ti" :class="done ? 'ti-refresh' : 'ti-check'" aria-hidden="true"></i>
                    {{ done ? 'Enregistrer' : 'Marquer comme fait' }}
                </button>
            </div>

        </div>
    </CandidateLayout>
</template>

<style scoped>
.pbe-shell { max-width: 640px; margin: 0 auto; padding: 0 0 3rem; }
.pbe-topbar { display: flex; align-items: center; justify-content: space-between; padding: 1.25rem 0 1rem; gap: 0.5rem; }
.pbe-back { display: flex; align-items: center; gap: 5px; font-size: 0.82rem; color: var(--text-secondary); flex-shrink: 0; }
.pbe-topbar-center { text-align: center; flex: 1; }
.pbe-category-label { font-size: 0.68rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em; }
.pbe-meta-pill { display: flex; align-items: center; gap: 4px; font-size: 0.72rem; color: var(--text-secondary); background: var(--bg-elevated, #eee); border-radius: 999px; padding: 4px 10px; flex-shrink: 0; font-weight: 500; }
.pbe-hero { padding: 1.25rem 0; border-bottom: 1px solid var(--glass-border, #e5e7eb); margin-bottom: 1.25rem; }
.pbe-hero-icon { width: 48px; height: 48px; border-radius: 12px; background: rgba(184,122,26,0.1); display: flex; align-items: center; justify-content: center; font-size: 1.4rem; color: var(--color-primary, #B87A1A); margin-bottom: 0.75rem; }
.pbe-hero-title { font-family: var(--font-display); font-size: 1.6rem; font-weight: 700; color: var(--text-primary); letter-spacing: -0.02em; line-height: 1.2; margin-bottom: 0.5rem; }
.pbe-hero-summary { font-size: 0.95rem; color: var(--text-secondary); line-height: 1.6; margin-bottom: 0.75rem; font-style: italic; }
.pbe-hero-meta { display: flex; gap: 6px; flex-wrap: wrap; min-height: 1px; }
.pbe-meta-chip { display: inline-flex; align-items: center; gap: 4px; font-size: 0.72rem; padding: 3px 9px; border-radius: 999px; background: var(--bg-elevated, #eee); color: var(--text-secondary); font-weight: 500; }
.pbe-meta-chip--done { background: rgba(16,185,129,0.1); color: #065F46; }
.pbe-section-label { font-size: 0.68rem; text-transform: uppercase; letter-spacing: 0.07em; color: var(--text-muted, #aaa); margin-bottom: 0.65rem; }
.pbe-section-label--mt { margin-top: 1.5rem; }
.pbe-body-card { border: 1px solid var(--glass-border, #e5e7eb); border-radius: 12px; padding: 1.1rem 1.25rem; background: var(--bg-elevated, #fafafa); }
.pbe-prose :deep(h2) { font-family: var(--font-display); font-weight: 700; font-size: 1.1rem; margin: 1.25rem 0 0.5rem; color: var(--text-primary); }
.pbe-prose :deep(h3) { font-family: var(--font-display); font-weight: 700; font-size: 0.95rem; margin: 1rem 0 0.4rem; color: var(--text-primary); }
.pbe-prose :deep(p) { font-size: 0.88rem; color: var(--text-primary); line-height: 1.7; margin: 0.45rem 0; }
.pbe-prose :deep(ul), .pbe-prose :deep(ol) { margin: 0.5rem 0 0.5rem 1.1rem; }
.pbe-prose :deep(li) { font-size: 0.88rem; color: var(--text-primary); line-height: 1.6; margin: 0.25rem 0; }
.pbe-prose :deep(strong) { color: var(--text-primary); }
.pbe-completion { border: 1px solid var(--glass-border, #e5e7eb); border-radius: 12px; padding: 1.1rem 1.25rem; background: var(--bg-elevated, #fafafa); }
.pbe-done-badge { display: inline-flex; align-items: center; gap: 6px; font-size: 0.85rem; font-weight: 600; color: #059669; margin-bottom: 0.75rem; }
.pbe-felt-row { display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.75rem; }
.pbe-felt-label { font-size: 0.8rem; color: var(--text-secondary); white-space: nowrap; }
.pbe-felt-btns { display: flex; gap: 6px; }
.pbe-felt-btn { width: 36px; height: 36px; border-radius: 8px; border: 1px solid var(--glass-border, #e5e7eb); background: transparent; color: var(--text-secondary); font-weight: 600; font-size: 0.85rem; cursor: pointer; }
.pbe-felt-btn.is-active { background: var(--color-primary, #B87A1A); border-color: var(--color-primary, #B87A1A); color: #fff; }
.pbe-notes { width: 100%; border: 1px solid var(--glass-border, #e5e7eb); border-radius: 8px; padding: 0.6rem 0.75rem; font-family: var(--font-body); font-size: 0.85rem; resize: vertical; margin-bottom: 0.9rem; background: var(--bg-base, #fff); color: var(--text-primary); }
.pbe-submit-btn { display: inline-flex; align-items: center; gap: 6px; background: var(--color-primary, #B87A1A); color: #fff; border: none; border-radius: 999px; padding: 0.65rem 1.4rem; font-size: 0.88rem; font-weight: 600; cursor: pointer; }
.pbe-submit-btn:disabled { opacity: 0.6; cursor: default; }
.pbe-submit-btn.is-done { background: var(--text-secondary); }
</style>
