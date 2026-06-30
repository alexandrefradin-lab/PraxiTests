<script setup>
import { ref } from 'vue'
import { Link, Head, useForm } from '@inertiajs/vue3'
import CandidateLayout from '@/Layouts/CandidateLayout.vue'
import MarkdownText from '@/Components/MarkdownText.vue'

const props = defineProps({
    practice:          { type: Object, required: true },
    state:             { type: Object, default: () => ({}) },
    nav:               { type: Object, default: () => ({ prev: null, next: null }) },
    eclatsPerPractice: { type: Number, default: 15 },
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
    form.post(route('praxilead.complete', props.practice.day), {
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
        <Head :title="`Jour ${practice.day} — ${practice.title}`" />

        <div class="plp-shell">

            <div class="plp-topbar">
                <Link :href="route('praxilead.index')" class="plp-back" style="text-decoration:none;">
                    <i class="ti ti-arrow-left" aria-hidden="true"></i>
                    Le Cap
                </Link>
                <div class="plp-topbar-center">
                    <div class="plp-day-label">Jour {{ practice.day }} / 60</div>
                    <div class="plp-bloc-label">{{ practice.theme }}</div>
                </div>
                <div class="plp-xp-pill">
                    <i class="ti ti-sparkles" aria-hidden="true"></i>
                    + {{ eclatsPerPractice }} Eclats
                </div>
            </div>

            <div class="plp-hero">
                <div class="plp-hero-icon">
                    <i class="ti" :class="iconFor(practice.icon)" aria-hidden="true"></i>
                </div>
                <h1 class="plp-hero-title">{{ practice.title }}</h1>
                <p class="plp-hero-summary">{{ practice.summary }}</p>
                <div class="plp-hero-meta">
                    <span class="plp-meta-chip">
                        <i class="ti ti-clock" aria-hidden="true"></i> {{ practice.duration_min }} min
                    </span>
                    <span v-if="done" class="plp-meta-chip plp-meta-chip--done">
                        <i class="ti ti-check" aria-hidden="true"></i> Integree
                    </span>
                </div>
            </div>

            <div class="plp-section-label">Contenu de la pratique</div>

            <div class="plp-body-card">
                <MarkdownText :source="practice.body" class="plp-prose" />
            </div>

            <div v-if="practice.micro_challenge">
                <div class="plp-section-label plp-section-label--mt">Micro-defi du jour</div>
                <div class="plp-challenge">
                    <div class="plp-challenge-icon">
                        <i class="ti ti-bolt" aria-hidden="true"></i>
                    </div>
                    <div class="plp-challenge-body">
                        <div class="plp-challenge-label">Application concrete</div>
                        <div class="plp-challenge-text">{{ practice.micro_challenge }}</div>
                    </div>
                </div>
            </div>

            <div class="plp-section-label plp-section-label--mt">
                {{ done ? 'Mettre a jour mon ressenti' : 'Valider la pratique' }}
            </div>

            <div class="plp-completion">
                <div v-if="done" class="plp-done-badge">
                    <i class="ti ti-circle-check" aria-hidden="true"></i> Pratique integree
                </div>
                <p v-else class="plp-completion-hint">
                    Marque cette pratique comme integree pour gagner {{ eclatsPerPractice }} Eclats.
                </p>

                <div class="plp-felt-row">
                    <span class="plp-felt-label">Ressenti</span>
                    <div class="plp-felt-btns">
                        <button
                            v-for="n in 5" :key="n"
                            type="button"
                            @click="form.felt_score = n"
                            class="plp-felt-btn"
                            :class="{ 'is-active': form.felt_score === n }"
                        >{{ n }}</button>
                    </div>
                </div>

                <textarea
                    v-model="form.notes"
                    rows="3"
                    class="plp-notes"
                    placeholder="Ce que tu as teste, ce que ca a revele..."
                ></textarea>

                <button
                    @click="submit"
                    :disabled="form.processing"
                    class="plp-submit-btn"
                    :class="{ 'is-done': done }"
                >
                    <i class="ti" :class="done ? 'ti-refresh' : 'ti-check'" aria-hidden="true"></i>
                    {{ done ? 'Enregistrer le ressenti' : 'Marquer comme integree' }}
                </button>
            </div>

            <div class="plp-nav">
                <Link
                    v-if="nav.prev"
                    :href="route('praxilead.show', nav.prev)"
                    class="plp-nav-link"
                    style="text-decoration:none;"
                >
                    <i class="ti ti-arrow-left" aria-hidden="true"></i> Jour {{ nav.prev }}
                </Link>
                <span v-else></span>

                <Link
                    v-if="nav.next"
                    :href="route('praxilead.show', nav.next)"
                    class="plp-nav-link plp-nav-link--next"
                    style="text-decoration:none;"
                >
                    Jour {{ nav.next }} <i class="ti ti-arrow-right" aria-hidden="true"></i>
                </Link>
                <span v-else class="plp-nav-end">La prochaine pratique se debloque demain</span>
            </div>

        </div>
    </CandidateLayout>
</template>

<style scoped>
.plp-shell { max-width: 640px; margin: 0 auto; padding: 0 0 3rem; }
.plp-topbar { display: flex; align-items: center; justify-content: space-between; padding: 1.25rem 0 1rem; gap: 0.5rem; }
.plp-back { display: flex; align-items: center; gap: 5px; font-size: 0.82rem; color: var(--text-secondary); flex-shrink: 0; }
.plp-topbar-center { text-align: center; flex: 1; }
.plp-day-label { font-size: 0.82rem; font-weight: 600; color: var(--text-primary); font-family: var(--font-data); }
.plp-bloc-label { font-size: 0.68rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em; margin-top: 1px; }
.plp-xp-pill { display: flex; align-items: center; gap: 4px; font-size: 0.72rem; font-weight: 500; color: var(--color-primary-dark, #7D5010); background: rgba(184,122,26,0.1); border-radius: 999px; padding: 4px 10px; flex-shrink: 0; }
.plp-hero { padding: 1.25rem 0; border-bottom: 1px solid var(--glass-border, #e5e7eb); margin-bottom: 1.25rem; }
.plp-hero-icon { width: 48px; height: 48px; border-radius: 12px; background: rgba(184,122,26,0.1); display: flex; align-items: center; justify-content: center; font-size: 1.4rem; color: var(--color-primary, #B87A1A); margin-bottom: 0.75rem; }
.plp-hero-title { font-family: var(--font-display); font-size: 1.6rem; font-weight: 700; color: var(--text-primary); letter-spacing: -0.02em; line-height: 1.2; margin-bottom: 0.5rem; }
.plp-hero-summary { font-size: 0.95rem; color: var(--text-secondary); line-height: 1.6; margin-bottom: 0.75rem; font-style: italic; }
.plp-hero-meta { display: flex; gap: 6px; flex-wrap: wrap; }
.plp-meta-chip { display: inline-flex; align-items: center; gap: 4px; font-size: 0.72rem; padding: 3px 9px; border-radius: 999px; background: var(--bg-elevated, #eee); color: var(--text-secondary); font-weight: 500; }
.plp-meta-chip--done { background: rgba(16,185,129,0.1); color: #065F46; }
.plp-section-label { font-size: 0.68rem; text-transform: uppercase; letter-spacing: 0.07em; color: var(--text-muted, #aaa); margin-bottom: 0.65rem; }
.plp-section-label--mt { margin-top: 1.5rem; }
.plp-body-card { border: 1px solid var(--glass-border, #e5e7eb); border-radius: 12px; padding: 1.1rem 1.25rem; background: var(--bg-elevated, #fafafa); }
.plp-prose :deep(h2) { font-family: var(--font-display); font-weight: 700; font-size: 1.1rem; margin: 1.25rem 0 0.5rem; color: var(--text-primary); }
.plp-prose :deep(h3) { font-family: var(--font-display); font-weight: 700; font-size: 0.95rem; margin: 1rem 0 0.4rem; color: var(--text-primary); }
.plp-prose :deep(p) { font-size: 0.88rem; color: var(--text-primary); line-height: 1.7; margin: 0.45rem 0; }
.plp-prose :deep(ul), .plp-prose :deep(ol) { margin: 0.5rem 0 0.5rem 1.1rem; }
.plp-prose :deep(li) { font-size: 0.88rem; color: var(--text-primary); line-height: 1.6; margin: 0.25rem 0; }
.plp-prose :deep(strong) { color: var(--text-primary); }
.plp-challenge { display: flex; gap: 0.9rem; align-items: flex-start; border: 1px dashed var(--color-primary, #B87A1A); border-radius: 12px; padding: 1rem 1.1rem; background: rgba(184,122,26,0.04); }
.plp-challenge-icon { width: 36px; height: 36px; border-radius: 8px; background: rgba(184,122,26,0.12); display: flex; align-items: center; justify-content: center; font-size: 1rem; color: var(--color-primary, #B87A1A); flex-shrink: 0; }
.plp-challenge-label { font-size: 0.68rem; text-transform: uppercase; letter-spacing: 0.07em; color: var(--color-primary, #B87A1A); font-weight: 700; margin-bottom: 5px; }
.plp-challenge-text { font-size: 0.92rem; color: var(--text-primary); line-height: 1.6; }
.plp-completion { border: 1px solid var(--glass-border, #e5e7eb); border-radius: 12px; padding: 1.1rem 1.25rem; background: var(--bg-elevated, #fafafa); }
.plp-done-badge { display: inline-flex; align-items: center; gap: 6px; font-size: 0.85rem; font-weight: 600; color: #059669; margin-bottom: 0.75rem; }
.plp-completion-hint { font-size: 0.82rem; color: var(--text-secondary); margin-bottom: 0.9rem; }
.plp-felt-row { display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.75rem; }
.plp-felt-label { font-size: 0.8rem; color: var(--text-secondary); white-space: nowrap; }
.plp-felt-btns { display: flex; gap: 6px; }
.plp-felt-btn { width: 36px; height: 36px; border-radius: 8px; border: 1px solid var(--glass-border, #e5e7eb); background: transparent; color: var(--text-secondary); font-weight: 600; font-size: 0.85rem; cursor: pointer; }
.plp-felt-btn.is-active { background: var(--color-primary, #B87A1A); border-color: var(--color-primary, #B87A1A); color: #fff; }
.plp-notes { width: 100%; border: 1px solid var(--glass-border, #e5e7eb); border-radius: 8px; padding: 0.6rem 0.75rem; font-family: var(--font-body); font-size: 0.85rem; resize: vertical; margin-bottom: 0.9rem; background: var(--bg-base, #fff); color: var(--text-primary); }
.plp-submit-btn { display: inline-flex; align-items: center; gap: 6px; background: var(--color-primary, #B87A1A); color: #fff; border: none; border-radius: 999px; padding: 0.65rem 1.4rem; font-size: 0.88rem; font-weight: 600; cursor: pointer; }
.plp-submit-btn:disabled { opacity: 0.6; cursor: default; }
.plp-submit-btn.is-done { background: var(--text-secondary); }
.plp-nav { display: flex; align-items: center; justify-content: space-between; margin-top: 2rem; padding-top: 1rem; border-top: 1px solid var(--glass-border, #e5e7eb); }
.plp-nav-link { display: flex; align-items: center; gap: 5px; font-size: 0.85rem; color: var(--text-secondary); }
.plp-nav-link--next { color: var(--color-primary, #B87A1A); font-weight: 600; }
.plp-nav-end { font-size: 0.78rem; color: var(--text-muted); }
</style>
