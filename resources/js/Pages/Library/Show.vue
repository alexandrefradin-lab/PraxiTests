<script setup>
import { ref } from 'vue'
import { Link, Head, useForm } from '@inertiajs/vue3'
import CandidateLayout from '@/Layouts/CandidateLayout.vue'
import MarkdownText from '@/Components/MarkdownText.vue'

const props = defineProps({
    plugin: { type: String, required: true },
    app: { type: Object, default: () => ({}) },
    exercise: { type: Object, required: true },
    state: { type: Object, default: () => ({}) },
})

// Quiz interactif (mise en situation type PraxiLink) ───────────────────────
const picked = ref(null)
const revealed = ref(false)
const choose = (key) => {
    picked.value = key
    revealed.value = true
}
const isCorrect = (key) => props.exercise.quiz && key === props.exercise.quiz.correct

// Formulaire « marqué comme fait » ─────────────────────────────────────────
const form = useForm({
    felt_score: props.state.felt_score ?? null,
    notes: props.state.notes ?? '',
})
const submit = () => {
    form.post(route(props.plugin + '.complete', props.exercise.id), {
        preserveScroll: true,
    })
}
</script>

<template>
    <CandidateLayout>
        <Head :title="exercise.title" />

        <div class="max-w-2xl mx-auto">

            <Link
                :href="route(plugin + '.index')"
                style="font-size: 0.8rem; color: var(--text-secondary); text-decoration: none;"
            >
                ← {{ app.title }}
            </Link>

            <!-- En-tête de l'exercice -->
            <div class="mt-3 mb-6">
                <h1 style="font-family: var(--font-display); font-size: 1.8rem; font-weight: 700; color: var(--text-primary); line-height: 1.15;">
                    {{ exercise.title }}
                </h1>
                <p class="mt-2" style="font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.05em; color: var(--text-secondary);">
                    <span v-if="exercise.category">{{ exercise.category }}</span>
                    <span v-if="exercise.category && exercise.duration_min"> · </span>
                    <span v-if="exercise.duration_min">{{ exercise.duration_min }} min</span>
                </p>
            </div>

            <!-- Pourquoi / base -->
            <div
                v-if="exercise.summary"
                class="mb-6"
                style="background: var(--surface, #fff); border: 1px solid var(--border, #e5e7eb); border-left: 3px solid var(--primary, #4F46E5); border-radius: var(--r-md, 12px); padding: 1rem 1.25rem; font-size: 0.9rem; color: var(--text-secondary); line-height: 1.6;"
            >
                {{ exercise.summary }}
            </div>

            <!-- Quiz interactif (optionnel) -->
            <div v-if="exercise.quiz" class="mb-6">
                <div
                    style="background: var(--surface, #fff); border: 1px solid var(--border, #e5e7eb); border-radius: var(--r-md, 12px); padding: 1.1rem 1.25rem;"
                >
                    <p style="font-size: 0.95rem; color: var(--text-primary); line-height: 1.6;">{{ exercise.quiz.scenario }}</p>
                    <p class="mt-3" style="font-weight: 600; color: var(--text-primary);">{{ exercise.quiz.question }}</p>

                    <div class="mt-3 space-y-2">
                        <button
                            v-for="(label, key) in exercise.quiz.options"
                            :key="key"
                            type="button"
                            @click="choose(key)"
                            :style="{
                                display: 'block', width: '100%', textAlign: 'left',
                                padding: '0.7rem 0.9rem', borderRadius: '10px', cursor: 'pointer',
                                fontSize: '0.9rem', lineHeight: '1.45',
                                border: '1px solid ' + (revealed && isCorrect(key) ? '#10B981' : (revealed && picked === key ? '#EF4444' : 'var(--border, #e5e7eb)')),
                                background: revealed && isCorrect(key) ? 'rgba(16,185,129,0.08)' : (revealed && picked === key ? 'rgba(239,68,68,0.06)' : 'transparent'),
                                color: 'var(--text-primary)',
                            }"
                        >
                            <strong>{{ key }}.</strong> {{ label }}
                        </button>
                    </div>

                    <div
                        v-if="revealed"
                        class="mt-3"
                        style="font-size: 0.88rem; line-height: 1.55; color: var(--text-secondary);"
                    >
                        <p style="font-weight: 600; color: var(--text-primary);">
                            {{ isCorrect(picked) ? '✓ Bonne réponse' : '→ Réponse attendue : ' + exercise.quiz.correct }}
                        </p>
                        <p class="mt-1">{{ exercise.quiz.feedback }}</p>
                    </div>
                </div>
            </div>

            <!-- Étapes guidées -->
            <ol
                v-if="exercise.steps && exercise.steps.length"
                class="mb-6"
                style="list-style: none; padding: 0; margin: 0;"
            >
                <li
                    v-for="(step, i) in exercise.steps"
                    :key="i"
                    style="display: flex; gap: 0.85rem; align-items: flex-start; padding: 0.7rem 0;"
                    :style="{ borderTop: i === 0 ? 'none' : '1px solid var(--border, #f0f0f0)' }"
                >
                    <span
                        style="flex-shrink: 0; width: 26px; height: 26px; border-radius: 50%; background: var(--primary, #4F46E5); color: #fff; font-size: 0.8rem; font-weight: 700; display: flex; align-items: center; justify-content: center;"
                    >{{ i + 1 }}</span>
                    <span style="font-size: 0.95rem; color: var(--text-primary); line-height: 1.6; padding-top: 2px;">{{ step }}</span>
                </li>
            </ol>

            <!-- Corps markdown (optionnel) -->
            <MarkdownText v-if="exercise.body" :source="exercise.body" class="mb-6" />

            <!-- Marquer comme fait -->
            <div
                style="background: var(--surface, #fff); border: 1px solid var(--border, #e5e7eb); border-radius: var(--r-md, 12px); padding: 1.1rem 1.25rem;"
            >
                <p v-if="state.completed" style="font-size: 0.9rem; color: #10B981; font-weight: 600;">
                    ✓ Tu as déjà réalisé cet exercice. Tu peux le refaire et mettre à jour ton ressenti.
                </p>
                <p v-else style="font-size: 0.9rem; color: var(--text-primary); font-weight: 600;">
                    Comment te sens-tu après cet exercice ?
                </p>

                <div class="mt-3 flex items-center gap-2">
                    <button
                        v-for="n in 5"
                        :key="n"
                        type="button"
                        @click="form.felt_score = n"
                        :style="{
                            width: '38px', height: '38px', borderRadius: '10px', cursor: 'pointer',
                            border: '1px solid var(--border, #e5e7eb)',
                            background: form.felt_score === n ? 'var(--primary, #4F46E5)' : 'transparent',
                            color: form.felt_score === n ? '#fff' : 'var(--text-secondary)',
                            fontWeight: 600,
                        }"
                    >{{ n }}</button>
                    <span style="font-size: 0.75rem; color: var(--text-secondary); margin-left: 0.4rem;">1 = difficile · 5 = très aidant</span>
                </div>

                <textarea
                    v-model="form.notes"
                    rows="3"
                    placeholder="Une note pour toi (optionnel)…"
                    class="mt-3"
                    style="width: 100%; border: 1px solid var(--border, #e5e7eb); border-radius: 10px; padding: 0.7rem 0.9rem; font-size: 0.9rem; font-family: var(--font-body); resize: vertical;"
                ></textarea>

                <button
                    type="button"
                    @click="submit"
                    :disabled="form.processing"
                    style="margin-top: 0.85rem; background: var(--primary, #4F46E5); color: #fff; border: none; border-radius: 10px; padding: 0.7rem 1.4rem; font-size: 0.92rem; font-weight: 600; cursor: pointer;"
                >
                    {{ state.completed ? 'Mettre à jour' : 'Marquer comme fait' }}
                </button>
            </div>

        </div>
    </CandidateLayout>
</template>
