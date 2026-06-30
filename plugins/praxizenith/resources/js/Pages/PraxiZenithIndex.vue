<script setup>
import { computed } from 'vue'
import { Link, Head } from '@inertiajs/vue3'
import CandidateLayout from '@/Layouts/CandidateLayout.vue'

const props = defineProps({
    exercises:  { type: Array,  default: () => [] },
    currentDay: { type: Number, default: 1 },
    totalDays:  { type: Number, default: 60 },
    completed:  { type: Number, default: 0 },
    streak:     { type: Number, default: 0 },
})

const iconFor = (name) => ({
    compass: 'ti-compass', target: 'ti-target', ear: 'ti-ear', message: 'ti-message',
    handshake: 'ti-handshake', gift: 'ti-gift', flame: 'ti-flame', shield: 'ti-shield',
    scale: 'ti-scale', users: 'ti-users', clock: 'ti-clock', book: 'ti-book',
    heart: 'ti-heart', rocket: 'ti-rocket', eye: 'ti-eye', seedling: 'ti-plant',
    anchor: 'ti-anchor', map: 'ti-map', lightbulb: 'ti-bulb', sun: 'ti-sun',
}[name] ?? 'ti-sparkles')

const todayExercise = computed(() => props.exercises.find(e => e.is_today) ?? null)
const donePercent   = computed(() => Math.round((props.completed / props.totalDays) * 100))

const dayStrip = computed(() => {
    const center = props.currentDay
    const start  = Math.max(1, center - 3)
    const end    = Math.min(props.totalDays, start + 6)
    return props.exercises.filter(e => e.day >= start && e.day <= end)
})

const upcomingDays = computed(() =>
    props.exercises.filter(e => !e.is_today && !e.completed && e.day > props.currentDay).slice(0, 3)
)

const currentBlock = computed(() => todayExercise.value?.theme ?? '')

const blocks = computed(() => {
    const out = []
    for (const e of props.exercises) {
        let b = out.find(x => x.theme === e.theme)
        if (!b) { b = { theme: e.theme, items: [] }; out.push(b) }
        b.items.push(e)
    }
    return out
})
</script>

<template>
    <CandidateLayout>
        <Head title="Le Sanctuaire de l'Attention — 60 jours de concentration" />

        <div class="pzn-shell">

            <div class="pzn-topbar">
                <div class="pzn-topbar-left">
                    <div class="pzn-app-name">Le Sanctuaire</div>
                    <div class="pzn-app-sub">Concentration - 60 jours</div>
                </div>
                <div class="pzn-topbar-right">
                    <div v-if="streak > 0" class="pzn-streak-pill">
                        <i class="ti ti-flame" aria-hidden="true"></i>
                        {{ streak }} jour{{ streak > 1 ? 's' : '' }}
                    </div>
                    <div class="pzn-progress-pill">{{ donePercent }} %</div>
                </div>
            </div>

            <div class="pzn-prog-track">
                <div class="pzn-prog-fill" :style="{ width: donePercent + '%' }"></div>
            </div>
            <div class="pzn-prog-meta">
                <span>{{ completed }} exercice{{ completed > 1 ? 's' : '' }} integre{{ completed > 1 ? 's' : '' }}</span>
                <span>Jour {{ currentDay }} / {{ totalDays }}</span>
            </div>

            <div class="pzn-day-strip">
                <div
                    v-for="e in dayStrip" :key="e.day"
                    class="pzn-strip-item"
                    :class="{
                        'is-done':   e.completed && !e.is_today,
                        'is-today':  e.is_today,
                        'is-locked': !e.unlocked && !e.completed,
                    }"
                >
                    <div class="pzn-strip-lbl">J{{ e.day }}</div>
                    <div class="pzn-strip-dot"></div>
                </div>
            </div>

            <div v-if="currentBlock" class="pzn-bloc-badge">
                <i class="ti" :class="iconFor(todayExercise?.icon)" aria-hidden="true"></i>
                {{ currentBlock }}
            </div>

            <div v-if="todayExercise">
                <div class="pzn-section-label">Exercice du jour</div>
                <Link
                    :href="todayExercise.unlocked ? route('praxizenith.show', todayExercise.day) : '#'"
                    class="pzn-today-card"
                    :class="{ 'is-completed': todayExercise.completed, 'is-locked': !todayExercise.unlocked }"
                    style="text-decoration:none;"
                >
                    <div class="pzn-today-card-top">
                        <div class="pzn-today-icon">
                            <i class="ti" :class="iconFor(todayExercise.icon)" aria-hidden="true"></i>
                        </div>
                        <div class="pzn-today-body">
                            <div class="pzn-today-eyebrow">Jour {{ todayExercise.day }} - {{ todayExercise.duration_min }} min</div>
                            <div class="pzn-today-title">{{ todayExercise.title }}</div>
                            <div class="pzn-today-desc">{{ todayExercise.summary }}</div>
                        </div>
                    </div>
                    <div class="pzn-today-footer">
                        <span v-if="todayExercise.completed" class="pzn-tag pzn-tag-done">
                            <i class="ti ti-check" aria-hidden="true"></i> Exercice integre
                        </span>
                        <span v-else-if="todayExercise.unlocked" class="pzn-tag pzn-tag-go">
                            Commencer l'exercice
                        </span>
                        <span v-else class="pzn-tag pzn-tag-locked">
                            <i class="ti ti-lock" aria-hidden="true"></i> Debloque demain
                        </span>
                        <span class="pzn-tag pzn-tag-xp">+ {{ todayExercise.eclats ?? 15 }} Eclats</span>
                    </div>
                </Link>
            </div>

            <div v-if="upcomingDays.length">
                <div class="pzn-section-label">Cette semaine - a venir</div>
                <div class="pzn-upcoming">
                    <div v-for="e in upcomingDays" :key="e.day" class="pzn-upcoming-item">
                        <div class="pzn-upcoming-num">{{ e.day }}</div>
                        <div class="pzn-upcoming-title">{{ e.title }}</div>
                        <i class="ti ti-lock pzn-upcoming-lock" aria-hidden="true"></i>
                    </div>
                </div>
            </div>

            <div class="pzn-section-label pzn-section-label--mt">Tous les jours</div>
            <div v-for="block in blocks" :key="block.theme" class="pzn-block">
                <div class="pzn-block-header">
                    <div
                        class="pzn-block-dot"
                        :class="{
                            'all-done':  block.items.every(i => i.completed),
                            'has-today': block.items.some(i => i.is_today),
                        }"
                    ></div>
                    <span class="pzn-block-title">{{ block.theme }}</span>
                    <span class="pzn-block-count">{{ block.items.filter(i => i.completed).length }}/{{ block.items.length }}</span>
                </div>
                <div class="pzn-block-days">
                    <Link
                        v-for="e in block.items.filter(i => i.unlocked)"
                        :key="'u' + e.day"
                        :href="route('praxizenith.show', e.day)"
                        class="pzn-block-day"
                        :class="{ 'is-done': e.completed, 'is-today': e.is_today }"
                        style="text-decoration:none;"
                        :title="e.title"
                    >
                        <i v-if="e.completed" class="ti ti-check" aria-hidden="true"></i>
                        <span v-else>{{ e.day }}</span>
                    </Link>
                    <div
                        v-for="e in block.items.filter(i => !i.unlocked)"
                        :key="'l' + e.day"
                        class="pzn-block-day is-locked"
                        :title="e.title"
                    >
                        <i class="ti ti-lock" aria-hidden="true"></i>
                    </div>
                </div>
            </div>

        </div>
    </CandidateLayout>
</template>

<style scoped>
.pzn-shell { max-width: 680px; margin: 0 auto; padding: 0 0 3rem; }
.pzn-topbar { display: flex; align-items: flex-start; justify-content: space-between; padding: 1.5rem 0 1rem; }
.pzn-app-name { font-size: 1.4rem; font-weight: 500; color: var(--text-primary); font-family: var(--font-display); }
.pzn-app-sub { font-size: 0.75rem; color: var(--text-secondary); margin-top: 2px; text-transform: uppercase; letter-spacing: 0.05em; }
.pzn-topbar-right { display: flex; align-items: center; gap: 0.5rem; margin-top: 4px; }
.pzn-streak-pill { display: flex; align-items: center; gap: 4px; background: rgba(184,122,26,0.12); color: #7D5010; border-radius: 999px; padding: 4px 10px; font-size: 0.78rem; font-weight: 500; }
.pzn-progress-pill { background: var(--bg-elevated, #eee); color: var(--text-secondary); border-radius: 999px; padding: 4px 10px; font-size: 0.78rem; font-weight: 500; font-family: var(--font-data); }
.pzn-prog-track { height: 4px; background: var(--bg-elevated, #eee); border-radius: 999px; overflow: hidden; margin-bottom: 6px; }
.pzn-prog-fill { height: 100%; background: var(--color-primary, #B87A1A); border-radius: 999px; transition: width 0.4s; }
.pzn-prog-meta { display: flex; justify-content: space-between; font-size: 0.72rem; color: var(--text-muted, #888); margin-bottom: 1.25rem; }
.pzn-day-strip { display: flex; border: 1px solid var(--glass-border, #e5e7eb); border-radius: 10px; overflow: hidden; margin-bottom: 1.25rem; }
.pzn-strip-item { flex: 1; padding: 10px 0; text-align: center; border-right: 1px solid var(--glass-border, #e5e7eb); position: relative; }
.pzn-strip-item:last-child { border-right: none; }
.pzn-strip-lbl { font-size: 0.65rem; color: var(--text-muted, #aaa); text-transform: uppercase; letter-spacing: 0.04em; margin-bottom: 4px; }
.pzn-strip-dot { width: 6px; height: 6px; border-radius: 50%; margin: 0 auto; background: var(--bg-elevated, #e5e7eb); }
.pzn-strip-item.is-done .pzn-strip-dot { background: var(--color-primary, #B87A1A); opacity: 0.7; }
.pzn-strip-item.is-today { background: var(--bg-elevated, #f5f0e8); }
.pzn-strip-item.is-today .pzn-strip-lbl { color: var(--color-primary, #B87A1A); font-weight: 600; }
.pzn-strip-item.is-today .pzn-strip-dot { background: var(--color-primary, #B87A1A); }
.pzn-bloc-badge { display: inline-flex; align-items: center; gap: 5px; font-size: 0.72rem; color: var(--color-primary-dark, #7D5010); background: rgba(184,122,26,0.1); border-radius: 999px; padding: 3px 10px; margin-bottom: 0.75rem; font-weight: 500; }
.pzn-section-label { font-size: 0.68rem; text-transform: uppercase; letter-spacing: 0.07em; color: var(--text-muted, #aaa); margin-bottom: 0.6rem; }
.pzn-section-label--mt { margin-top: 1.5rem; }
.pzn-today-card { display: block; border: 1px solid var(--color-primary, #B87A1A); border-radius: 12px; padding: 1rem 1.1rem; background: rgba(184,122,26,0.04); margin-bottom: 1.25rem; cursor: pointer; }
.pzn-today-card:hover { background: rgba(184,122,26,0.08); }
.pzn-today-card.is-completed { border-color: var(--color-success, #10B981); background: rgba(16,185,129,0.04); }
.pzn-today-card.is-locked { border-color: var(--glass-border, #e5e7eb); background: transparent; opacity: 0.6; cursor: default; }
.pzn-today-card-top { display: flex; gap: 0.9rem; align-items: flex-start; margin-bottom: 0.9rem; }
.pzn-today-icon { width: 42px; height: 42px; border-radius: 10px; background: rgba(184,122,26,0.12); display: flex; align-items: center; justify-content: center; flex-shrink: 0; font-size: 1.2rem; color: var(--color-primary, #B87A1A); }
.pzn-today-body { flex: 1; }
.pzn-today-eyebrow { font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.06em; color: var(--color-primary, #B87A1A); margin-bottom: 4px; }
.pzn-today-title { font-size: 1rem; font-weight: 600; color: var(--text-primary); font-family: var(--font-display); margin-bottom: 4px; line-height: 1.3; }
.pzn-today-desc { font-size: 0.82rem; color: var(--text-secondary); line-height: 1.5; }
.pzn-today-footer { display: flex; align-items: center; gap: 6px; flex-wrap: wrap; }
.pzn-tag { font-size: 0.72rem; padding: 3px 9px; border-radius: 999px; display: inline-flex; align-items: center; gap: 4px; font-weight: 500; }
.pzn-tag-go { background: var(--color-primary, #B87A1A); color: #fff; }
.pzn-tag-done { background: rgba(16,185,129,0.12); color: #065F46; }
.pzn-tag-locked { background: var(--bg-elevated, #eee); color: var(--text-muted, #aaa); }
.pzn-tag-xp { background: rgba(184,122,26,0.1); color: var(--color-primary-dark, #7D5010); }
.pzn-upcoming { display: flex; flex-direction: column; gap: 6px; margin-bottom: 1.25rem; }
.pzn-upcoming-item { display: flex; align-items: center; gap: 0.75rem; padding: 0.65rem 0.9rem; border: 1px solid var(--glass-border, #e5e7eb); border-radius: 10px; background: var(--bg-elevated, #f9f9f9); }
.pzn-upcoming-num { width: 28px; height: 28px; border-radius: 6px; background: var(--bg-surface, #eee); display: flex; align-items: center; justify-content: center; font-size: 0.72rem; font-weight: 600; color: var(--text-muted); flex-shrink: 0; font-family: var(--font-data); }
.pzn-upcoming-title { flex: 1; font-size: 0.85rem; color: var(--text-secondary); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.pzn-upcoming-lock { font-size: 0.75rem; color: var(--text-muted); }
.pzn-block { margin-bottom: 1rem; }
.pzn-block-header { display: flex; align-items: center; gap: 8px; margin-bottom: 8px; }
.pzn-block-dot { width: 8px; height: 8px; border-radius: 50%; background: var(--bg-elevated, #ddd); flex-shrink: 0; }
.pzn-block-dot.all-done { background: var(--color-primary, #B87A1A); }
.pzn-block-dot.has-today { background: var(--color-primary, #B87A1A); opacity: 0.6; }
.pzn-block-title { font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.06em; color: var(--text-secondary); font-weight: 600; flex: 1; }
.pzn-block-count { font-size: 0.68rem; color: var(--text-muted); font-family: var(--font-data); }
.pzn-block-days { display: flex; gap: 5px; flex-wrap: wrap; padding-left: 16px; }
.pzn-block-day { width: 32px; height: 32px; border-radius: 7px; display: flex; align-items: center; justify-content: center; font-size: 0.72rem; font-weight: 600; font-family: var(--font-data); border: 1px solid var(--glass-border, #e5e7eb); background: var(--bg-elevated); color: var(--text-secondary); cursor: pointer; }
.pzn-block-day.is-done { background: rgba(184,122,26,0.15); border-color: rgba(184,122,26,0.3); color: var(--color-primary-dark, #7D5010); }
.pzn-block-day.is-today { background: var(--color-primary, #B87A1A); border-color: var(--color-primary, #B87A1A); color: #fff; }
.pzn-block-day.is-locked { opacity: 0.4; cursor: default; font-size: 0.65rem; }
</style>
