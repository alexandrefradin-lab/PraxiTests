<script setup>
import { computed } from 'vue'
import { Link, Head } from '@inertiajs/vue3'
import CandidateLayout from '@/Layouts/CandidateLayout.vue'
import DailyTipCard from '@/Components/DailyTipCard.vue'

const props = defineProps({
    plugin: { type: String, required: true },
    app: { type: Object, default: () => ({}) },
    exercises: { type: Array, default: () => [] },
    completedCount: { type: Number, default: 0 },
    dailyTip: { type: Object, default: null },
    tipEngagement: { type: Object, default: () => ({ streak: 0, longest_streak: 0, total_applied: 0, applied_today: false }) },
})

const iconFor = (name) => ({
    'sparkles': '✨',
    'heart-pulse': '🫀',
    'brain': '🧠',
    'compass': '🧭',
    'rocket': '🚀',
    'mic': '🎤',
    'target': '🎯',
    'calendar': '🗓️',
    'zap': '⚡',
    'list-checks': '✅',
    'list-ordered': '🔢',
    'person-standing': '🧍',
    'flame': '🔥',
    'messages': '💬',
    'yin-yang': '☯️',
    'hourglass-high': '⏳',
}[name] ?? '⭐')

// Regroupe les exercices par catégorie en conservant l'ordre d'apparition.
const groups = computed(() => {
    const map = new Map()
    for (const ex of props.exercises) {
        const key = ex.category || 'Exercices'
        if (!map.has(key)) map.set(key, [])
        map.get(key).push(ex)
    }
    return Array.from(map, ([category, items]) => ({ category, items }))
})

const total = computed(() => props.exercises.length)
</script>

<template>
    <CandidateLayout>
        <Head :title="app.title || 'Exercices'" />

        <div class="max-w-3xl mx-auto">

            <!-- En-tête -->
            <div class="mb-8">
                <Link
                    :href="route('treasure.index')"
                    style="font-size: 0.8rem; color: var(--text-secondary); text-decoration: none;"
                >
                    ← La Salle du Trésor
                </Link>
                <h1 class="mt-2" style="font-family: var(--font-display); font-size: 2.2rem; font-weight: 700; color: var(--text-primary); letter-spacing: -0.02em; line-height: 1.1;">
                    {{ app.title }}
                </h1>
                <p v-if="app.subtitle" class="mt-2" style="font-family: var(--font-body); font-size: 0.95rem; color: var(--text-secondary);">
                    {{ app.subtitle }}
                </p>
                <p class="mt-2" style="font-size: 0.85rem; color: var(--text-secondary);">
                    {{ completedCount }}/{{ total }} exercices réalisés · choisis ce qui te parle, dans l'ordre que tu veux.
                </p>
            </div>

            <!-- Tip du jour -->
            <DailyTipCard :plugin="plugin" :tip="dailyTip" :engagement="tipEngagement" />

            <!-- Exercices par catégorie -->
            <div v-for="group in groups" :key="group.category" class="mb-8">
                <h2 v-if="group.category" style="font-family: var(--font-display); font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.06em; color: var(--text-secondary); margin-bottom: 0.75rem;">
                    {{ group.category }}
                </h2>

                <div class="space-y-3">
                    <Link
                        v-for="ex in group.items"
                        :key="ex.id"
                        :href="route(plugin + '.show', ex.id)"
                        class="block"
                        style="text-decoration: none;"
                    >
                        <div
                            style="display: flex; gap: 1rem; align-items: flex-start; padding: 1.1rem 1.25rem; background: var(--surface, #fff); border: 1px solid var(--border, #e5e7eb); border-radius: var(--r-md, 12px); transition: transform .15s, box-shadow .15s; cursor: pointer;"
                        >
                            <div style="font-size: 1.5rem; line-height: 1;">{{ iconFor(ex.icon) }}</div>

                            <div style="flex: 1; min-width: 0;">
                                <div class="flex items-center justify-between gap-3">
                                    <h3 style="font-family: var(--font-display); font-weight: 700; font-size: 1.02rem; color: var(--text-primary);">
                                        {{ ex.title }}
                                    </h3>
                                    <span v-if="ex.completed" style="flex-shrink: 0; font-size: 0.75rem; font-weight: 600; color: #10B981;">✓ Fait</span>
                                </div>
                                <p v-if="ex.duration_min" class="mt-1" style="font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.04em; color: var(--text-secondary);">
                                    {{ ex.duration_min }} min<span v-if="ex.has_quiz"> · mise en situation</span>
                                </p>
                                <p v-if="ex.summary" class="mt-2" style="font-size: 0.88rem; color: var(--text-secondary); line-height: 1.5;">
                                    {{ ex.summary }}
                                </p>
                            </div>
                        </div>
                    </Link>
                </div>
            </div>

            <p v-if="!total" style="color: var(--text-secondary);">Aucun exercice disponible pour le moment.</p>

        </div>
    </CandidateLayout>
</template>
