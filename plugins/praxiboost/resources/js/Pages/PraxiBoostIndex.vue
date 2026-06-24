<script setup>
import { computed } from 'vue'
import { Link, Head } from '@inertiajs/vue3'
import CandidateLayout from '@/Layouts/CandidateLayout.vue'
import DailyTipCard from '@/Components/DailyTipCard.vue'

const props = defineProps({
    exercises: { type: Array, default: () => [] },
    totalEclats: { type: Number, default: 0 },
    dailyTip: { type: Object, default: null },
    tipEngagement: { type: Object, default: () => ({ streak: 0, longest_streak: 0, total_applied: 0, applied_today: false }) },
})

const iconFor = (name) => ({
    'sparkles': '✨',
    'heart-pulse': '🫀',
    'brain': '🧠',
    'compass': '🧭',
    'rocket': '🚀',
}[name] ?? '⭐')

const unlockedCount = computed(() => props.exercises.filter(e => e.unlocked).length)

// Prochain palier non débloqué (pour la barre de progression).
const nextLocked = computed(() => props.exercises.find(e => !e.unlocked) ?? null)
const nextPercent = computed(() => {
    if (!nextLocked.value) return 100
    return Math.min(100, Math.round((props.totalEclats / nextLocked.value.threshold_eclats) * 100))
})
</script>

<template>
    <CandidateLayout>
        <Head title="L'Étincelle" />

        <div class="max-w-3xl mx-auto">

            <!-- En-tête -->
            <div class="mb-8">
                <h1 style="font-family: var(--font-display); font-size: 2.2rem; font-weight: 700; color: var(--text-primary); letter-spacing: -0.02em; line-height: 1.1;">
                    L'Étincelle
                </h1>
                <p class="mt-2" style="font-family: var(--font-body); font-size: 0.95rem; color: var(--text-secondary);">
                    Chaque palier d'Éclats franchi débloque un nouvel exercice guidé.
                    Tu as <strong>{{ totalEclats }} Éclats</strong> · {{ unlockedCount }}/{{ exercises.length }} débloqués.
                </p>
            </div>

            <!-- Tip du jour -->
            <DailyTipCard plugin="praxiboost" :tip="dailyTip" :engagement="tipEngagement" />

            <!-- Progression vers le prochain palier -->
            <div v-if="nextLocked" class="mb-8" style="background:var(--bg-elevated);border:1px solid var(--glass-border);border-top:2px solid var(--color-primary);border-radius:10px;padding:1rem 1.25rem;">
                <div class="flex items-center justify-between mb-2" style="font-size:0.85rem;color:var(--text-secondary);">
                    <span>Prochain : <strong style="color:var(--text-primary);">{{ nextLocked.title }}</strong></span>
                    <span style="font-family:var(--font-data);font-size:0.78rem;">{{ totalEclats }} / {{ nextLocked.threshold_eclats }} Éclats</span>
                </div>
                <div style="display:flex;align-items:center;gap:0.75rem;">
                    <div style="flex:1;height:5px;background:rgba(166,117,32,0.15);border-radius:999px;overflow:hidden;">
                        <div :style="{ width: nextPercent + '%', height:'100%', background:'var(--color-primary)', transition:'width .4s' }"></div>
                    </div>
                    <span style="font-family:var(--font-data);font-size:0.7rem;color:var(--text-muted);flex-shrink:0;">{{ nextPercent }}%</span>
                </div>
                <p class="mt-2" style="font-size:0.8rem;color:var(--text-muted);">
                    <i class="ti ti-diamond" style="font-size:0.9rem;color:var(--color-primary);" aria-hidden="true"></i>
                    Encore <strong style="color:var(--text-secondary);">{{ nextLocked.remaining }} Éclats</strong> pour le débloquer.
                </p>
            </div>

            <!-- Liste des exercices -->
            <div class="space-y-4">
                <component
                    :is="ex.unlocked ? 'Link' : 'div'"
                    v-for="ex in exercises"
                    :key="ex.slug"
                    :href="ex.unlocked ? route('praxiboost.show', ex.slug) : undefined"
                    class="block"
                    style="text-decoration: none;"
                >
                    <div
                        :style="{
                            display: 'flex', gap: '1rem', alignItems: 'flex-start',
                            padding: '1.1rem 1.25rem',
                            background: 'var(--bg-elevated)',
                            border: '1px solid var(--glass-border)',
                            borderRadius: '10px',
                            opacity: ex.unlocked ? 1 : 0.6,
                            transition: 'transform .15s, box-shadow .15s',
                            cursor: ex.unlocked ? 'pointer' : 'default',
                        }"
                    >
                        <div style="font-size: 1.6rem; line-height: 1;">
                            <span v-if="ex.unlocked">{{ iconFor(ex.icon) }}</span>
                            <span v-else>🔒</span>
                        </div>

                        <div style="flex: 1; min-width: 0;">
                            <div class="flex items-center justify-between gap-3">
                                <h3 style="font-family: var(--font-display); font-weight: 700; font-size: 1.05rem; color: var(--text-primary);">
                                    {{ ex.title }}
                                </h3>
                                <span v-if="ex.completed" style="flex-shrink: 0; font-size: 0.75rem; font-weight: 600; color: #10B981;">✓ Fait</span>
                            </div>
                            <p class="mt-1" style="font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.04em; color: var(--text-secondary);">
                                {{ ex.category }} · {{ ex.duration_min }} min
                            </p>
                            <p class="mt-2" style="font-size: 0.9rem; color: var(--text-secondary);">
                                {{ ex.summary }}
                            </p>
                            <p v-if="!ex.unlocked" class="mt-2" style="font-size: 0.8rem; font-weight: 600; color: var(--color-primary);">
                                🔒 Se débloque à {{ ex.threshold_eclats }} Éclats — encore {{ ex.remaining }}
                            </p>
                        </div>
                    </div>
                </component>
            </div>

        </div>
    </CandidateLayout>
</template>
