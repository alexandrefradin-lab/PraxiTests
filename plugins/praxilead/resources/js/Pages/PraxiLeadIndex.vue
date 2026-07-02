<script setup>
import { computed } from 'vue'
import { Link, Head } from '@inertiajs/vue3'
import CandidateLayout from '@/Layouts/CandidateLayout.vue'

const props = defineProps({
    appDescription: { type: String, default: null },
    practices:  { type: Array,  default: () => [] },
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

const todayPractice = computed(() => props.practices.find(p => p.is_today) ?? null)
const donePercent   = computed(() => Math.round((props.completed / props.totalDays) * 100))

const dayStrip = computed(() => {
    const center = props.currentDay
    const start  = Math.max(1, center - 3)
    const end    = Math.min(props.totalDays, start + 6)
    return props.practices.filter(p => p.day >= start && p.day <= end)
})

const upcomingDays = computed(() =>
    props.practices.filter(p => !p.is_today && !p.completed && p.day > props.currentDay).slice(0, 3)
)

const currentBlock = computed(() => todayPractice.value?.theme ?? '')

const blocks = computed(() => {
    const out = []
    for (const p of props.practices) {
        let b = out.find(x => x.theme === p.theme)
        if (!b) { b = { theme: p.theme, items: [] }; out.push(b) }
        b.items.push(p)
    }
    return out
})
</script>

<template>
    <CandidateLayout>
        <Head title="Le Cap — 60 jours de management" />

        <div class="pl-shell">

            <div class="pl-topbar">
                <div class="pl-topbar-left">
                    <div class="pl-app-name">Le Cap</div>
                    <div class="pl-app-sub">Management - 60 jours</div>
                </div>
                <div class="pl-topbar-right">
                    <div v-if="streak > 0" class="pl-streak-pill">
                        <i class="ti ti-flame" aria-hidden="true"></i>
                        {{ streak }} jour{{ streak > 1 ? 's' : '' }}
                    </div>
                    <div class="pl-progress-pill">{{ donePercent }} %</div>
                </div>
            </div>

            <!-- Présentation du module (description du manifest) -->
            <p v-if="appDescription" class="pl-app-desc">{{ appDescription }}</p>

            <div class="pl-prog-track">
                <div class="pl-prog-fill" :style="{ width: donePercent + '%' }"></div>
            </div>
            <div class="pl-prog-meta">
                <span>{{ completed }} pratique{{ completed > 1 ? 's' : '' }} integree{{ completed > 1 ? 's' : '' }}</span>
                <span>Jour {{ currentDay }} / {{ totalDays }}</span>
            </div>

            <div class="pl-day-strip">
                <div
                    v-for="p in dayStrip" :key="p.day"
                    class="pl-strip-item"
                    :class="{
                        'is-done':   p.completed && !p.is_today,
                        'is-today':  p.is_today,
                        'is-locked': !p.unlocked && !p.completed,
                    }"
                >
                    <div class="pl-strip-lbl">J{{ p.day }}</div>
                    <div class="pl-strip-dot"></div>
                </div>
            </div>

            <div v-if="currentBlock" class="pl-bloc-badge">
                <i class="ti" :class="iconFor(todayPractice?.icon)" aria-hidden="true"></i>
                {{ currentBlock }}
            </div>

            <div v-if="todayPractice">
                <div class="pl-section-label">Pratique du jour</div>
                <Link
                    :href="todayPractice.unlocked ? route('praxilead.show', todayPractice.day) : '#'"
                    class="pl-today-card"
                    :class="{ 'is-completed': todayPractice.completed, 'is-locked': !todayPractice.unlocked }"
                    style="text-decoration:none;"
                >
                    <div class="pl-today-card-top">
                        <div class="pl-today-icon">
                            <i class="ti" :class="iconFor(todayPractice.icon)" aria-hidden="true"></i>
                        </div>
                        <div class="pl-today-body">
                            <div class="pl-today-eyebrow">Jour {{ todayPractice.day }} - {{ todayPractice.duration_min }} min</div>
                            <div class="pl-today-title">{{ todayPractice.title }}</div>
                            <div class="pl-today-desc">{{ todayPractice.summary }}</div>
                        </div>
                    </div>
                    <div class="pl-today-footer">
                        <span v-if="todayPractice.completed" class="pl-tag pl-tag-done">
                            <i class="ti ti-check" aria-hidden="true"></i> Pratique integree
                        </span>
                        <span v-else-if="todayPractice.unlocked" class="pl-tag pl-tag-go">
                            Commencer la pratique
                        </span>
                        <span v-else class="pl-tag pl-tag-locked">
                            <i class="ti ti-lock" aria-hidden="true"></i> Debloque demain
                        </span>
                        <span class="pl-tag pl-tag-xp">+ {{ todayPractice.eclats ?? 15 }} Eclats</span>
                    </div>
                </Link>
            </div>

            <div v-if="upcomingDays.length">
                <div class="pl-section-label">Cette semaine - a venir</div>
                <div class="pl-upcoming">
                    <div v-for="p in upcomingDays" :key="p.day" class="pl-upcoming-item">
                        <div class="pl-upcoming-num">{{ p.day }}</div>
                        <div class="pl-upcoming-title">{{ p.title }}</div>
                        <i class="ti ti-lock pl-upcoming-lock" aria-hidden="true"></i>
                    </div>
                </div>
            </div>

            <div class="pl-section-label pl-section-label--mt">Tous les jours</div>
            <div v-for="block in blocks" :key="block.theme" class="pl-block">
                <div class="pl-block-header">
                    <div
                        class="pl-block-dot"
                        :class="{
                            'all-done':  block.items.every(i => i.completed),
                            'has-today': block.items.some(i => i.is_today),
                        }"
                    ></div>
                    <span class="pl-block-title">{{ block.theme }}</span>
                    <span class="pl-block-count">{{ block.items.filter(i => i.completed).length }}/{{ block.items.length }}</span>
                </div>
                <div class="pl-block-days">
                    <Link
                        v-for="p in block.items.filter(i => i.unlocked)"
                        :key="'u' + p.day"
                        :href="route('praxilead.show', p.day)"
                        class="pl-block-day"
                        :class="{ 'is-done': p.completed, 'is-today': p.is_today }"
                        style="text-decoration:none;"
                        :title="p.title"
                    >
                        <i v-if="p.completed" class="ti ti-check" aria-hidden="true"></i>
                        <span v-else>{{ p.day }}</span>
                    </Link>
                    <div
                        v-for="p in block.items.filter(i => !i.unlocked)"
                        :key="'l' + p.day"
                        class="pl-block-day is-locked"
                        :title="p.title"
                    >
                        <i class="ti ti-lock" aria-hidden="true"></i>
                    </div>
                </div>
            </div>

        </div>
    </CandidateLayout>
</template>

<style scoped>
.pl-shell { max-width: 680px; margin: 0 auto; padding: 0 0 3rem; }
.pl-topbar { display: flex; align-items: flex-start; justify-content: space-between; padding: 1.5rem 0 1rem; }
.pl-app-name { font-size: 1.4rem; font-weight: 500; color: var(--text-primary); font-family: var(--font-display); }
.pl-app-sub { font-size: 0.75rem; color: var(--text-secondary); margin-top: 2px; text-transform: uppercase; letter-spacing: 0.05em; }
.pl-app-desc { font-size: 0.9rem; line-height: 1.65; color: var(--text-secondary); background: var(--bg-elevated, #fafafa); border-left: 3px solid var(--color-primary, #B87A1A); border-radius: 0 10px 10px 0; padding: 0.9rem 1.1rem; margin: 0 0 1.25rem; }
.pl-topbar-right { display: flex; align-items: center; gap: 0.5rem; margin-top: 4px; }
.pl-streak-pill { display: flex; align-items: center; gap: 4px; background: rgba(184,122,26,0.12); color: #7D5010; border-radius: 999px; padding: 4px 10px; font-size: 0.78rem; font-weight: 500; }
.pl-progress-pill { background: var(--bg-elevated, #eee); color: var(--text-secondary); border-radius: 999px; padding: 4px 10px; font-size: 0.78rem; font-weight: 500; font-family: var(--font-data); }
.pl-prog-track { height: 4px; background: var(--bg-elevated, #eee); border-radius: 999px; overflow: hidden; margin-bottom: 6px; }
.pl-prog-fill { height: 100%; background: var(--color-primary, #B87A1A); border-radius: 999px; transition: width 0.4s; }
.pl-prog-meta { display: flex; justify-content: space-between; font-size: 0.72rem; color: var(--text-muted, #888); margin-bottom: 1.25rem; }
.pl-day-strip { display: flex; border: 1px solid var(--glass-border, #e5e7eb); border-radius: 10px; overflow: hidden; margin-bottom: 1.25rem; }
.pl-strip-item { flex: 1; padding: 10px 0; text-align: center; border-right: 1px solid var(--glass-border, #e5e7eb); position: relative; }
.pl-strip-item:last-child { border-right: none; }
.pl-strip-lbl { font-size: 0.65rem; color: var(--text-muted, #aaa); text-transform: uppercase; letter-spacing: 0.04em; margin-bottom: 4px; }
.pl-strip-dot { width: 6px; height: 6px; border-radius: 50%; margin: 0 auto; background: var(--bg-elevated, #e5e7eb); }
.pl-strip-item.is-done .pl-strip-dot { background: var(--color-primary, #B87A1A); opacity: 0.7; }
.pl-strip-item.is-today { background: var(--bg-elevated, #f5f0e8); }
.pl-strip-item.is-today .pl-strip-lbl { color: var(--color-primary, #B87A1A); font-weight: 600; }
.pl-strip-item.is-today .pl-strip-dot { background: var(--color-primary, #B87A1A); }
.pl-bloc-badge { display: inline-flex; align-items: center; gap: 5px; font-size: 0.72rem; color: var(--color-primary-dark, #7D5010); background: rgba(184,122,26,0.1); border-radius: 999px; padding: 3px 10px; margin-bottom: 0.75rem; font-weight: 500; }
.pl-section-label { font-size: 0.68rem; text-transform: uppercase; letter-spacing: 0.07em; color: var(--text-muted, #aaa); margin-bottom: 0.6rem; }
.pl-section-label--mt { margin-top: 1.5rem; }
.pl-today-card { display: block; border: 1px solid var(--color-primary, #B87A1A); border-radius: 12px; padding: 1rem 1.1rem; background: rgba(184,122,26,0.04); margin-bottom: 1.25rem; cursor: pointer; }
.pl-today-card:hover { background: rgba(184,122,26,0.08); }
.pl-today-card.is-completed { border-color: var(--color-success, #10B981); background: rgba(16,185,129,0.04); }
.pl-today-card.is-locked { border-color: var(--glass-border, #e5e7eb); background: transparent; opacity: 0.6; cursor: default; }
.pl-today-card-top { display: flex; gap: 0.9rem; align-items: flex-start; margin-bottom: 0.9rem; }
.pl-today-icon { width: 42px; height: 42px; border-radius: 10px; background: rgba(184,122,26,0.12); display: flex; align-items: center; justify-content: center; flex-shrink: 0; font-size: 1.2rem; color: var(--color-primary, #B87A1A); }
.pl-today-body { flex: 1; }
.pl-today-eyebrow { font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.06em; color: var(--color-primary, #B87A1A); margin-bottom: 4px; }
.pl-today-title { font-size: 1rem; font-weight: 600; color: var(--text-primary); font-family: var(--font-display); margin-bottom: 4px; line-height: 1.3; }
.pl-today-desc { font-size: 0.82rem; color: var(--text-secondary); line-height: 1.5; }
.pl-today-footer { display: flex; align-items: center; gap: 6px; flex-wrap: wrap; }
.pl-tag { font-size: 0.72rem; padding: 3px 9px; border-radius: 999px; display: inline-flex; align-items: center; gap: 4px; font-weight: 500; }
.pl-tag-go { background: var(--color-primary, #B87A1A); color: #fff; }
.pl-tag-done { background: rgba(16,185,129,0.12); color: #065F46; }
.pl-tag-locked { background: var(--bg-elevated, #eee); color: var(--text-muted, #aaa); }
.pl-tag-xp { background: rgba(184,122,26,0.1); color: var(--color-primary-dark, #7D5010); }
.pl-upcoming { display: flex; flex-direction: column; gap: 6px; margin-bottom: 1.25rem; }
.pl-upcoming-item { display: flex; align-items: center; gap: 0.75rem; padding: 0.65rem 0.9rem; border: 1px solid var(--glass-border, #e5e7eb); border-radius: 10px; background: var(--bg-elevated, #f9f9f9); }
.pl-upcoming-num { width: 28px; height: 28px; border-radius: 6px; background: var(--bg-surface, #eee); display: flex; align-items: center; justify-content: center; font-size: 0.72rem; font-weight: 600; color: var(--text-muted); flex-shrink: 0; font-family: var(--font-data); }
.pl-upcoming-title { flex: 1; font-size: 0.85rem; color: var(--text-secondary); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.pl-upcoming-lock { font-size: 0.75rem; color: var(--text-muted); }
.pl-block { margin-bottom: 1rem; }
.pl-block-header { display: flex; align-items: center; gap: 8px; margin-bottom: 8px; }
.pl-block-dot { width: 8px; height: 8px; border-radius: 50%; background: var(--bg-elevated, #ddd); flex-shrink: 0; }
.pl-block-dot.all-done { background: var(--color-primary, #B87A1A); }
.pl-block-dot.has-today { background: var(--color-primary, #B87A1A); opacity: 0.6; }
.pl-block-title { font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.06em; color: var(--text-secondary); font-weight: 600; flex: 1; }
.pl-block-count { font-size: 0.68rem; color: var(--text-muted); font-family: var(--font-data); }
.pl-block-days { display: flex; gap: 5px; flex-wrap: wrap; padding-left: 16px; }
.pl-block-day { width: 32px; height: 32px; border-radius: 7px; display: flex; align-items: center; justify-content: center; font-size: 0.72rem; font-weight: 600; font-family: var(--font-data); border: 1px solid var(--glass-border, #e5e7eb); background: var(--bg-elevated); color: var(--text-secondary); cursor: pointer; }
.pl-block-day.is-done { background: rgba(184,122,26,0.15); border-color: rgba(184,122,26,0.3); color: var(--color-primary-dark, #7D5010); }
.pl-block-day.is-today { background: var(--color-primary, #B87A1A); border-color: var(--color-primary, #B87A1A); color: #fff; }
.pl-block-day.is-locked { opacity: 0.4; cursor: default; font-size: 0.65rem; }
</style>
