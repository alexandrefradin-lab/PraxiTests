<script setup>
import { computed, ref } from 'vue'
import { Link, Head, useForm } from '@inertiajs/vue3'
import CandidateLayout from '@/Layouts/CandidateLayout.vue'
import MarkdownText from '@/Components/MarkdownText.vue'

const props = defineProps({
    exercise: { type: Object, required: true },
    state: { type: Object, default: () => ({}) },
})


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

        <div class="max-w-2xl mx-auto">

            <Link :href="route('praxiboost.index')" class="pt-btn-ghost text-sm" style="display: inline-block; margin-bottom: 1.25rem;">
                ← Tous les exercices
            </Link>

            <p style="font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.04em; color: var(--text-secondary);">
                {{ exercise.category }} · {{ exercise.duration_min }} min
            </p>
            <h1 class="mt-1" style="font-family: var(--font-display); font-size: 2rem; font-weight: 700; color: var(--text-primary); letter-spacing: -0.02em; line-height: 1.15;">
                {{ exercise.title }}
            </h1>

            <MarkdownText :source="props.exercise.body" class="praxiboost-prose mt-6" />

            <!-- Marquer comme fait -->
            <div class="mt-10" style="background: var(--color-surface, #F5EDD8); border: 1px solid var(--glass-border, #e5d9c0); border-radius: var(--r-md, 12px); padding: 1.25rem 1.5rem;">
                <div v-if="done" class="flex items-center gap-2 mb-3" style="color: #10B981; font-weight: 600;">
                    ✓ Exercice marqué comme fait
                </div>
                <h3 style="font-family: var(--font-display); font-weight: 700; color: var(--text-primary);">
                    {{ done ? 'Mettre à jour ton ressenti' : 'Tu as terminé cet exercice ?' }}
                </h3>

                <label class="block mt-4" style="font-size: 0.85rem; color: var(--text-secondary);">Ton ressenti (optionnel)</label>
                <div class="flex gap-2 mt-2">
                    <button
                        v-for="n in 5" :key="n" type="button"
                        @click="form.felt_score = n"
                        :style="{
                            width: '40px', height: '40px', borderRadius: '8px',
                            border: '1px solid var(--glass-border, #e5d9c0)',
                            background: form.felt_score === n ? 'var(--color-primary, #A67520)' : 'transparent',
                            color: form.felt_score === n ? '#fff' : 'var(--text-secondary)',
                            cursor: 'pointer', fontWeight: 600,
                        }"
                    >{{ n }}</button>
                </div>

                <label class="block mt-4" style="font-size: 0.85rem; color: var(--text-secondary);">Notes (optionnel)</label>
                <textarea
                    v-model="form.notes" rows="3"
                    class="mt-2 w-full"
                    style="border: 1px solid var(--glass-border, #e5d9c0); border-radius: 8px; padding: 0.6rem 0.75rem; font-family: var(--font-body); resize: vertical;"
                    placeholder="Ce que tu retiens, ce que tu ressens…"
                ></textarea>

                <button
                    @click="submit" :disabled="form.processing"
                    class="pt-btn-primary mt-4"
                    style="cursor: pointer;"
                >
                    {{ done ? 'Enregistrer' : 'Marquer comme fait' }}
                </button>
            </div>

        </div>
    </CandidateLayout>
</template>

<style scoped>
.praxiboost-prose :deep(h2) { font-family: var(--font-display); font-weight: 700; font-size: 1.25rem; margin: 1.6rem 0 0.6rem; color: var(--text-primary); }
.praxiboost-prose :deep(h3) { font-family: var(--font-display); font-weight: 700; font-size: 1.05rem; margin: 1.2rem 0 0.4rem; color: var(--text-primary); }
.praxiboost-prose :deep(p) { margin: 0.6rem 0; }
.praxiboost-prose :deep(ul), .praxiboost-prose :deep(ol) { margin: 0.6rem 0 0.6rem 1.25rem; }
.praxiboost-prose :deep(li) { margin: 0.3rem 0; }
.praxiboost-prose :deep(strong) { color: var(--text-primary); }
</style>
