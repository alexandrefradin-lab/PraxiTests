<script setup>
import { computed } from 'vue'
import { Link, Head } from '@inertiajs/vue3'
import CandidateLayout from '@/Layouts/CandidateLayout.vue'
import DailyTipCard from '@/Components/DailyTipCard.vue'

const props = defineProps({
    appDescription: { type: String, default: null },
    exercises:     { type: Array,  default: () => [] },
    totalEclats:   { type: Number, default: 0 },
    dailyTip:      { type: Object, default: null },
    tipEngagement: { type: Object, default: () => ({ streak: 0, longest_streak: 0, total_applied: 0, applied_today: false }) },
})

const iconFor = (name) => ({
    sparkles:    'ti-sparkles',
    'heart-pulse': 'ti-heartbeat',
    brain:       'ti-brain',
    compass:     'ti-compass',
    rocket:      'ti-rocket',
}[name] ?? 'ti-sparkles')

const unlockedCount = computed(() => props.exercises.filter(e => e.unlocked).length)

const nextLocked = computed(() => props.exercises.find(e => !e.unlocked) ?? null)
const nextPercent = computed(() => {
    if (!nextLocked.value) return 100
    return Math.min(100, Math.round((props.totalEclats / nextLocked.value.threshold_eclats) * 100))
})
</script>

<template>
    <CandidateLayout>
        <Head title="L'Etincelle" />

        <div class="pb-shell">

            <div class="pb-topbar">
                <div class="pb-topbar-left">
                    <div class="pb-app-name">L'Etincelle</div>
                    <div class="pb-app-sub">Exercices de developpement</div>
                </div>
                <div class="pb-topbar-right">
                    <div class="pb-eclats-pill">
                        <i class="ti ti-diamond" aria-hidden="true"></i>
                        {{ totalEclats }} Eclats
                    </div>
                    <div class="pb-count-pill">{{ unlockedCount }}/{{ exercises.length }}</div>
                </div>
            </div>

            <!-- Présentation du module (description du manifest) -->
            <p v-if="appDescription" class="pb-app-desc">{{ appDescription }}</p>

            <!-- Tip du jour -->
            <DailyTipCard plugin="praxiboost" :tip="dailyTip" :engagement="tipEngagement" />

            <!-- Progression vers le prochain palier -->
            <div v-if="nextLocked" class="pb-next-tier">
                <div class="pb-next-tier-top">
                    <span class="pb-next-tier-label">
                        Prochain : <strong>{{ nextLocked.title }}</strong>
                    </span>
                    <span class="pb-next-tier-count">{{ totalEclats }} / {{ nextLocked.threshold_eclats }}</span>
                </div>
                <div class="pb-prog-track">
                    <div class="pb-prog-fill" :style="{ width: nextPercent + '%' }"></div>
                </div>
                <div class="pb-next-tier-hint">
                    <i class="ti ti-diamond" aria-hidden="true"></i>
                    Encore <strong>{{ nextLocked.remaining }} Eclats</strong> pour debloquer cet exercice
                </div>
            </div>

            <div class="pb-section-label pb-section-label--mt">Exercices</div>

            <div class="pb-list">
                <component
                    :is="ex.unlocked ? 'Link' : 'div'"
                    v-for="ex in exercises"
                    :key="ex.slug"
                    :href="ex.unlocked ? route('praxiboost.show', ex.slug) : undefined"
                    class="pb-item"
                    :class="{ 'is-locked': !ex.unlocked, 'is-done': ex.completed }"
                    style="text-decoration:none;"
                >
                    <div class="pb-item-icon" :class="{ 'is-locked': !ex.unlocked }">
                        <i v-if="ex.unlocked" class="ti" :class="iconFor(ex.icon)" aria-hidden="true"></i>
                        <i v-else class="ti ti-lock" aria-hidden="true"></i>
                    </div>
                    <div class="pb-item-body">
                        <div class="pb-item-top">
                            <div class="pb-item-title">{{ ex.title }}</div>
                            <span v-if="ex.completed" class="pb-tag pb-tag-done">
                                <i class="ti ti-check" aria-hidden="true"></i> Fait
                            </span>
                        </div>
                        <div class="pb-item-eyebrow">{{ ex.category }} · {{ ex.duration_min }} min</div>
                        <div class="pb-item-desc">{{ ex.summary }}</div>
                        <div v-if="!ex.unlocked" class="pb-item-lock-hint">
                            <i class="ti ti-lock" aria-hidden="true"></i>
                            Se debloque a {{ ex.threshold_eclats }} Eclats — encore {{ ex.remaining }}
                        </div>
                    </div>
                    <i v-if="ex.unlocked" class="ti ti-chevron-right pb-item-arrow" aria-hidden="true"></i>
                </component>
            </div>

        </div>
    </CandidateLayout>
</template>

<style scoped>
.pb-shell { max-width: 680px; margin: 0 auto; padding: 0 0 3rem; }
.pb-topbar { display: flex; align-items: flex-start; justify-content: space-between; padding: 1.5rem 0 1rem; }
.pb-app-name { font-size: 1.4rem; font-weight: 500; color: var(--text-primary); font-family: var(--font-display); }
.pb-app-sub { font-size: 0.75rem; color: var(--text-secondary); margin-top: 2px; text-transform: uppercase; letter-spacing: 0.05em; }
.pb-topbar-right { display: flex; align-items: center; gap: 0.5rem; margin-top: 4px; }
.pb-eclats-pill { display: flex; align-items: center; gap: 4px; background: rgba(184,122,26,0.12); color: #7D5010; border-radius: 999px; padding: 4px 10px; font-size: 0.78rem; font-weight: 500; }
.pb-count-pill { background: var(--bg-elevated, #eee); color: var(--text-secondary); border-radius: 999px; padding: 4px 10px; font-size: 0.78rem; font-weight: 500; font-family: var(--font-data); }
.pb-app-desc { font-size: 0.9rem; line-height: 1.65; color: var(--text-secondary); background: var(--bg-elevated, #fafafa); border-left: 3px solid var(--color-primary, #B87A1A); border-radius: 0 10px 10px 0; padding: 0.9rem 1.1rem; margin: 0 0 1.25rem; }
.pb-next-tier { border: 1px solid var(--glass-border, #e5e7eb); border-top: 2px solid var(--color-primary, #B87A1A); border-radius: 12px; padding: 1rem 1.25rem; background: var(--bg-elevated, #fafafa); margin-bottom: 1.25rem; }
.pb-next-tier-top { display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.6rem; font-size: 0.85rem; color: var(--text-secondary); }
.pb-next-tier-top strong { color: var(--text-primary); }
.pb-next-tier-count { font-family: var(--font-data); font-size: 0.78rem; color: var(--text-muted); }
.pb-prog-track { height: 4px; background: rgba(184,122,26,0.15); border-radius: 999px; overflow: hidden; margin-bottom: 0.6rem; }
.pb-prog-fill { height: 100%; background: var(--color-primary, #B87A1A); border-radius: 999px; transition: width 0.4s; }
.pb-next-tier-hint { font-size: 0.78rem; color: var(--text-muted); display: flex; align-items: center; gap: 5px; }
.pb-next-tier-hint i { color: var(--color-primary, #B87A1A); }
.pb-next-tier-hint strong { color: var(--text-secondary); }
.pb-section-label { font-size: 0.68rem; text-transform: uppercase; letter-spacing: 0.07em; color: var(--text-muted, #aaa); margin-bottom: 0.6rem; }
.pb-section-label--mt { margin-top: 0.5rem; }
.pb-list { display: flex; flex-direction: column; gap: 8px; }
.pb-item { display: flex; align-items: flex-start; gap: 0.9rem; border: 1px solid var(--glass-border, #e5e7eb); border-radius: 12px; padding: 1rem 1.1rem; background: var(--bg-elevated, #fafafa); cursor: pointer; }
.pb-item.is-locked { opacity: 0.6; cursor: default; }
.pb-item:not(.is-locked):hover { background: rgba(184,122,26,0.04); }
.pb-item-icon { width: 42px; height: 42px; border-radius: 10px; background: rgba(184,122,26,0.12); display: flex; align-items: center; justify-content: center; flex-shrink: 0; font-size: 1.2rem; color: var(--color-primary, #B87A1A); }
.pb-item-icon.is-locked { background: var(--bg-elevated, #eee); color: var(--text-muted); }
.pb-item-body { flex: 1; min-width: 0; }
.pb-item-top { display: flex; align-items: center; justify-content: space-between; gap: 0.5rem; margin-bottom: 3px; }
.pb-item-title { font-family: var(--font-display); font-size: 1rem; font-weight: 600; color: var(--text-primary); line-height: 1.25; }
.pb-item-eyebrow { font-size: 0.68rem; text-transform: uppercase; letter-spacing: 0.05em; color: var(--text-muted); margin-bottom: 4px; }
.pb-item-desc { font-size: 0.85rem; color: var(--text-secondary); line-height: 1.5; }
.pb-item-lock-hint { font-size: 0.78rem; color: var(--color-primary, #B87A1A); font-weight: 500; margin-top: 5px; display: flex; align-items: center; gap: 4px; }
.pb-item-arrow { font-size: 0.85rem; color: var(--text-muted); align-self: center; flex-shrink: 0; }
.pb-tag { font-size: 0.72rem; padding: 2px 8px; border-radius: 999px; display: inline-flex; align-items: center; gap: 3px; font-weight: 500; flex-shrink: 0; }
.pb-tag-done { background: rgba(16,185,129,0.12); color: #065F46; }
</style>
