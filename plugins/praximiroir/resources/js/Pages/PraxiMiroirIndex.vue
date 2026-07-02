<script setup>
import { computed } from 'vue'
import { Link, Head } from '@inertiajs/vue3'
import CandidateLayout from '@/Layouts/CandidateLayout.vue'

const props = defineProps({
    appDescription: { type: String, default: null },
    exercises:  { type: Array,  default: () => [] },
    currentDay: { type: Number, default: 1 },
    totalDays:  { type: Number, default: 30 },
    completed:  { type: Number, default: 0 },
    streak:     { type: Number, default: 0 },
})

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

const blocs = computed(() => {
    const out = []
    for (const e of props.exercises) {
        let b = out.find(x => x.bloc === e.bloc)
        if (!b) { b = { bloc: e.bloc, items: [] }; out.push(b) }
        b.items.push(e)
    }
    return out
})
</script>

<template>
    <CandidateLayout>
        <Head title="La Forge de l'Identité — 30 jours" />

        <div class="pm-shell">

            <div class="pm-topbar">
                <div class="pm-topbar-left">
                    <div class="pm-app-name">La Forge de l'Identité</div>
                    <div class="pm-app-sub">Introspection · 30 jours</div>
                </div>
                <div class="pm-topbar-right">
                    <div v-if="streak > 0" class="pm-streak-pill">
                        <i class="ti ti-flame" aria-hidden="true"></i>
                        {{ streak }} jour{{ streak > 1 ? 's' : '' }}
                    </div>
                    <div class="pm-progress-pill">{{ donePercent }} %</div>
                </div>
            </div>

            <!-- Présentation du module (description du manifest) -->
            <p v-if="appDescription" class="pm-app-desc">{{ appDescription }}</p>

            <div class="pm-prog-track">
                <div class="pm-prog-fill" :style="{ width: donePercent + '%' }"></div>
            </div>
            <div class="pm-prog-meta">
                <span>{{ completed }} exercice{{ completed > 1 ? 's' : '' }} accompli{{ completed > 1 ? 's' : '' }}</span>
                <span>Jour {{ currentDay }} / {{ totalDays }}</span>
            </div>

            <div class="pm-day-strip">
                <div
                    v-for="e in dayStrip" :key="e.day"
                    class="pm-strip-item"
                    :class="{
                        'is-done':   e.completed && !e.is_today,
                        'is-today':  e.is_today,
                        'is-locked': !e.unlocked && !e.completed,
                    }"
                >
                    <div class="pm-strip-lbl">J{{ e.day }}</div>
                    <div class="pm-strip-dot"></div>
                </div>
            </div>

            <div v-if="todayExercise">
                <div class="pm-section-label">Exercice du jour</div>
                <Link
                    :href="todayExercise.unlocked ? route('praximiroir.show', todayExercise.day) : '#'"
                    class="pm-today-card"
                    :class="{ 'is-completed': todayExercise.completed, 'is-locked': !todayExercise.unlocked }"
                    style="text-decoration:none;"
                >
                    <div class="pm-today-card-top">
                        <div class="pm-today-icon">
                            <i class="ti" :class="iconFor(todayExercise.icon)" aria-hidden="true"></i>
                        </div>
                        <div class="pm-today-body">
                            <div class="pm-today-eyebrow">Jour {{ todayExercise.day }} · {{ todayExercise.bloc }} · {{ todayExercise.duration_min }} min</div>
                            <div class="pm-today-title">{{ todayExercise.title }}</div>
                            <div class="pm-today-desc">{{ todayExercise.summary }}</div>
                        </div>
                    </div>
                    <div class="pm-today-footer">
                        <span v-if="todayExercise.completed" class="pm-tag pm-tag-done">
                            <i class="ti ti-check" aria-hidden="true"></i> Accompli
                        </span>
                        <span v-else-if="todayExercise.unlocked" class="pm-tag pm-tag-go">
                            Commencer l'exercice
                        </span>
                        <span v-else class="pm-tag pm-tag-locked">
                            <i class="ti ti-lock" aria-hidden="true"></i> Demain
                        </span>
                        <span class="pm-tag pm-tag-xp">+ 20 Éclats</span>
                    </div>
                </Link>
            </div>

            <div v-if="upcomingDays.length">
                <div class="pm-section-label">Cette semaine — à venir</div>
                <div class="pm-upcoming">
                    <div v-for="e in upcomingDays" :key="e.day" class="pm-upcoming-item">
                        <div class="pm-upcoming-num">{{ e.day }}</div>
                        <div class="pm-upcoming-info">
                            <div class="pm-upcoming-bloc">{{ e.bloc }}</div>
                            <div class="pm-upcoming-title">{{ e.title }}</div>
                        </div>
                        <i class="ti ti-lock pm-upcoming-lock" aria-hidden="true"></i>
                    </div>
                </div>
            </div>

            <div class="pm-section-label pm-section-label--mt">Les 8 blocs du parcours</div>
            <div v-for="bloc in blocs" :key="bloc.bloc" class="pm-bloc">
                <div class="pm-bloc-header">
                    <div
                        class="pm-bloc-dot"
                        :class="{
                            'all-done':  bloc.items.every(i => i.completed),
                            'has-today': bloc.items.some(i => i.is_today),
                        }"
                    ></div>
                    <span class="pm-bloc-title">{{ bloc.bloc }}</span>
                    <span class="pm-bloc-count">{{ bloc.items.filter(i => i.completed).length }}/{{ bloc.items.length }}</span>
                </div>
                <div class="pm-bloc-days">
                    <Link
                        v-for="e in bloc.items.filter(i => i.unlocked)"
                        :key="'u' + e.day"
                        :href="route('praximiroir.show', e.day)"
                        class="pm-bloc-day"
                        :class="{ 'is-done': e.completed, 'is-today': e.is_today }"
                        style="text-decoration:none;"
                        :title="e.title"
                    >
                        <i v-if="e.completed" class="ti ti-check" aria-hidden="true"></i>
                        <span v-else>{{ e.day }}</span>
                    </Link>
                    <div
                        v-for="e in bloc.items.filter(i => !i.unlocked)"
                        :key="'l' + e.day"
                        class="pm-bloc-day is-locked"
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
/* Palette : violet profond #6B3FA0 — introspection & identité */
.pm-shell { max-width: 680px; margin: 0 auto; padding: 0 0 3rem; }

.pm-topbar { display: flex; align-items: flex-start; justify-content: space-between; padding: 1.5rem 0 1rem; }
.pm-app-name { font-size: 1.4rem; font-weight: 500; color: var(--text-primary); font-family: var(--font-display); }
.pm-app-sub { font-size: 0.75rem; color: var(--text-secondary); margin-top: 2px; text-transform: uppercase; letter-spacing: 0.05em; }
.pm-app-desc { font-size: 0.9rem; line-height: 1.65; color: var(--text-secondary); background: var(--bg-elevated, #fafafa); border-left: 3px solid var(--color-primary, #B87A1A); border-radius: 0 10px 10px 0; padding: 0.9rem 1.1rem; margin: 0 0 1.25rem; }
.pm-topbar-right { display: flex; align-items: center; gap: 0.5rem; margin-top: 4px; }

.pm-streak-pill { display: flex; align-items: center; gap: 4px; background: rgba(107,63,160,0.12); color: #4a2870; border-radius: 999px; padding: 4px 10px; font-size: 0.78rem; font-weight: 500; }
.pm-progress-pill { background: var(--bg-elevated, #eee); color: var(--text-secondary); border-radius: 999px; padding: 4px 10px; font-size: 0.78rem; font-weight: 500; font-family: var(--font-data); }

.pm-prog-track { height: 4px; background: var(--bg-elevated, #eee); border-radius: 999px; overflow: hidden; margin-bottom: 6px; }
.pm-prog-fill { height: 100%; background: #6B3FA0; border-radius: 999px; transition: width 0.4s; }
.pm-prog-meta { display: flex; justify-content: space-between; font-size: 0.72rem; color: var(--text-muted, #888); margin-bottom: 1.25rem; }

.pm-day-strip { display: flex; border: 1px solid var(--glass-border, #e5e7eb); border-radius: 10px; overflow: hidden; margin-bottom: 1.5rem; }
.pm-strip-item { flex: 1; padding: 10px 0; text-align: center; border-right: 1px solid var(--glass-border, #e5e7eb); }
.pm-strip-item:last-child { border-right: none; }
.pm-strip-lbl { font-size: 0.65rem; color: var(--text-muted, #aaa); text-transform: uppercase; letter-spacing: 0.04em; margin-bottom: 4px; }
.pm-strip-dot { width: 6px; height: 6px; border-radius: 50%; margin: 0 auto; background: var(--bg-elevated, #e5e7eb); }
.pm-strip-item.is-done .pm-strip-dot { background: #6B3FA0; opacity: 0.6; }
.pm-strip-item.is-today { background: rgba(107,63,160,0.06); }
.pm-strip-item.is-today .pm-strip-lbl { color: #6B3FA0; font-weight: 600; }
.pm-strip-item.is-today .pm-strip-dot { background: #6B3FA0; }

.pm-section-label { font-size: 0.68rem; text-transform: uppercase; letter-spacing: 0.07em; color: var(--text-muted, #aaa); margin-bottom: 0.6rem; }
.pm-section-label--mt { margin-top: 1.5rem; }

.pm-today-card { display: block; border: 1px solid #6B3FA0; border-radius: 12px; padding: 1rem 1.1rem; background: rgba(107,63,160,0.04); margin-bottom: 1.25rem; cursor: pointer; transition: background 0.15s; }
.pm-today-card:hover { background: rgba(107,63,160,0.08); }
.pm-today-card.is-completed { border-color: var(--glass-border, #e5e7eb); background: var(--bg-elevated, #f8f7fc); }
.pm-today-card.is-locked { border-color: var(--glass-border, #e5e7eb); cursor: default; opacity: 0.7; }
.pm-today-card-top { display: flex; gap: 0.9rem; align-items: flex-start; margin-bottom: 0.75rem; }
.pm-today-icon { width: 2.5rem; height: 2.5rem; border-radius: 10px; background: rgba(107,63,160,0.1); display: flex; align-items: center; justify-content: center; flex-shrink: 0; color: #6B3FA0; font-size: 1.15rem; }
.pm-today-body { flex: 1; min-width: 0; }
.pm-today-eyebrow { font-size: 0.68rem; color: var(--text-muted, #aaa); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 3px; }
.pm-today-title { font-size: 1rem; font-weight: 600; color: var(--text-primary); line-height: 1.3; margin-bottom: 4px; font-family: var(--font-display); }
.pm-today-desc { font-size: 0.82rem; color: var(--text-secondary); line-height: 1.5; }
.pm-today-footer { display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap; }
.pm-tag { display: inline-flex; align-items: center; gap: 4px; border-radius: 999px; padding: 4px 10px; font-size: 0.75rem; font-weight: 500; }
.pm-tag-done { background: rgba(107,63,160,0.1); color: #4a2870; }
.pm-tag-go { background: #6B3FA0; color: #fff; }
.pm-tag-locked { background: var(--bg-elevated, #eee); color: var(--text-muted, #aaa); }
.pm-tag-xp { background: rgba(107,63,160,0.08); color: #6B3FA0; margin-left: auto; }

.pm-upcoming { display: flex; flex-direction: column; gap: 0.4rem; margin-bottom: 1rem; }
.pm-upcoming-item { display: flex; align-items: center; gap: 0.75rem; padding: 0.6rem 0.8rem; border: 1px solid var(--glass-border, #e5e7eb); border-radius: 8px; background: var(--bg-surface, #fff); }
.pm-upcoming-num { width: 1.6rem; height: 1.6rem; border-radius: 50%; background: var(--bg-elevated, #f0edf6); color: #6B3FA0; font-size: 0.72rem; font-weight: 600; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.pm-upcoming-info { flex: 1; min-width: 0; }
.pm-upcoming-bloc { font-size: 0.65rem; color: #6B3FA0; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 1px; }
.pm-upcoming-title { font-size: 0.82rem; color: var(--text-secondary); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.pm-upcoming-lock { color: var(--text-muted, #ccc); font-size: 0.8rem; flex-shrink: 0; }

.pm-bloc { margin-bottom: 1rem; }
.pm-bloc-header { display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.4rem; }
.pm-bloc-dot { width: 8px; height: 8px; border-radius: 50%; background: var(--bg-elevated, #ddd); flex-shrink: 0; }
.pm-bloc-dot.all-done { background: #6B3FA0; }
.pm-bloc-dot.has-today { background: #6B3FA0; box-shadow: 0 0 0 3px rgba(107,63,160,0.2); }
.pm-bloc-title { font-size: 0.78rem; font-weight: 500; color: var(--text-secondary); flex: 1; }
.pm-bloc-count { font-size: 0.7rem; color: var(--text-muted, #aaa); font-family: var(--font-data); }
.pm-bloc-days { display: flex; gap: 0.3rem; flex-wrap: wrap; }
.pm-bloc-day { width: 2rem; height: 2rem; border-radius: 6px; border: 1px solid var(--glass-border, #e5e7eb); display: flex; align-items: center; justify-content: center; font-size: 0.7rem; color: var(--text-muted, #aaa); cursor: pointer; transition: all 0.15s; }
.pm-bloc-day:hover { border-color: #6B3FA0; color: #6B3FA0; }
.pm-bloc-day.is-done { background: #6B3FA0; border-color: #6B3FA0; color: #fff; }
.pm-bloc-day.is-today { border-color: #6B3FA0; color: #6B3FA0; font-weight: 600; box-shadow: 0 0 0 2px rgba(107,63,160,0.15); }
.pm-bloc-day.is-locked { cursor: default; opacity: 0.45; }
.pm-bloc-day.is-locked:hover { border-color: var(--glass-border, #e5e7eb); color: var(--text-muted, #aaa); }
</style>
