<script setup>
import { computed, ref } from 'vue'
import { Link, Head, useForm } from '@inertiajs/vue3'
import CandidateLayout from '@/Layouts/CandidateLayout.vue'

const props = defineProps({
    practice:          { type: Object, required: true },
    state:             { type: Object, default: () => ({}) },
    nav:               { type: Object, default: () => ({ prev: null, next: null }) },
    eclatsPerPractice: { type: Number, default: 20 },
})

const iconFor = (name) => ({
    compass: 'ti-compass', target: 'ti-target', ear: 'ti-ear', message: 'ti-message',
    handshake: 'ti-handshake', gift: 'ti-gift', flame: 'ti-flame', shield: 'ti-shield',
    scale: 'ti-scale', users: 'ti-users', clock: 'ti-clock', book: 'ti-book',
    heart: 'ti-heart', rocket: 'ti-rocket', eye: 'ti-eye', seedling: 'ti-plant',
    anchor: 'ti-anchor', map: 'ti-map', lightbulb: 'ti-bulb', sun: 'ti-sun',
}[name] ?? 'ti-sparkles')

const exerciseSections = computed(() => {
    const body = props.practice.body ?? ''
    if (!body.trim()) return []
    const sections = []
    let current = null
    const sectionIcons = ['ti-book-open', 'ti-pencil', 'ti-notes']
    const sectionColors = ['warm', 'cool', 'green']
    for (const raw of body.split('\n')) {
        const line = raw.trimEnd()
        const h2 = line.match(/^## (.+)/)
        const h3 = line.match(/^### (.+)/)
        const hr = /^---$/.test(line)
        if (h2 || h3 || hr) {
            if (current) sections.push(current)
            if (!hr) {
                const idx = sections.length
                current = {
                    title: (h2 || h3)[1],
                    lines: [],
                    icon: sectionIcons[idx % sectionIcons.length],
                    color: sectionColors[idx % sectionColors.length],
                }
            } else {
                current = null
            }
        } else if (current) {
            current.lines.push(line)
        } else if (sections.length === 0 && line) {
            if (!current) {
                current = { title: 'Contexte et objectif', lines: [], icon: sectionIcons[0], color: 'warm' }
            }
            current.lines.push(line)
        }
    }
    if (current) sections.push(current)
    return sections.filter(s => s.lines.some(l => l.trim()))
})

const renderLines = (lines) => {
    let html = '', list = null
    const esc = (s) => s.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
    const inline = (s) => esc(s)
        .replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>')
        .replace(/\*(.+?)\*/g, '<em>$1</em>')
    const closeList = () => { if (list) { html += '</' + list + '>'; list = null } }
    for (const raw of lines) {
        const line = raw.trimEnd()
        const ol = line.match(/^\d+\.\s+(.*)/)
        if (ol) { if (list !== 'ol') { closeList(); list = 'ol'; html += '<ol>' } html += '<li>' + inline(ol[1]) + '</li>'; continue }
        const ul = line.match(/^[-*]\s+(.*)/)
        if (ul) { if (list !== 'ul') { closeList(); list = 'ul'; html += '<ul>' } html += '<li>' + inline(ul[1]) + '</li>'; continue }
        if (line === '') { closeList(); continue }
        closeList(); html += '<p>' + inline(line) + '</p>'
    }
    closeList()
    return html
}

const durationPerSection = computed(() => {
    const total = props.practice.duration_min ?? 10
    const n = exerciseSections.value.length || 1
    return Math.max(1, Math.round(total / n))
})

const form = useForm({
    felt_score: props.state?.felt_score ?? null,
    notes: props.state?.notes ?? '',
})

const done       = ref(props.state?.completed ?? false)
const activeStep = ref(done.value ? null : 0)

const startStep = (idx) => { activeStep.value = idx }

const submit = () => {
    const wasDone = done.value
    form.post(route('praxivision.complete', props.practice.day), {
        preserveScroll: true,
        onSuccess: () => {
            done.value = true
            activeStep.value = null
            if (!wasDone) {
                import('canvas-confetti').then(({ default: confetti }) => {
                    confetti({ particleCount: 100, spread: 70, origin: { y: 0.7 } })
                }).catch(() => {})
            }
        },
    })
}
</script>

<template>
    <CandidateLayout>
        <Head :title="`Jour ${practice.day} — ${practice.title}`" />

        <div class="pvp-shell">

            <div class="pvp-topbar">
                <Link :href="route('praxivision.index')" class="pvp-back" style="text-decoration:none;">
                    <i class="ti ti-arrow-left" aria-hidden="true"></i>
                    L'Eveilleur
                </Link>
                <div class="pvp-topbar-center">
                    <div class="pvp-day-label">Jour {{ practice.day }} / 60</div>
                    <div class="pvp-bloc-label">{{ practice.theme }}</div>
                </div>
                <div class="pvp-xp-pill">
                    <i class="ti ti-sparkles" aria-hidden="true"></i>
                    + {{ eclatsPerPractice }} Eclats
                </div>
            </div>

            <div class="pvp-hero">
                <div class="pvp-hero-icon">
                    <i class="ti" :class="iconFor(practice.icon)" aria-hidden="true"></i>
                </div>
                <h1 class="pvp-hero-title">{{ practice.title }}</h1>
                <p class="pvp-hero-summary">{{ practice.summary }}</p>
                <div class="pvp-hero-meta">
                    <span class="pvp-meta-chip">
                        <i class="ti ti-clock" aria-hidden="true"></i> {{ practice.duration_min }} min
                    </span>
                    <span v-if="done" class="pvp-meta-chip pvp-meta-chip--done">
                        <i class="ti ti-check" aria-hidden="true"></i> Integree
                    </span>
                    <span v-else class="pvp-meta-chip pvp-meta-chip--active">
                        {{ exerciseSections.length }} etape{{ exerciseSections.length > 1 ? 's' : '' }}
                    </span>
                </div>
            </div>

            <div class="pvp-section-label">Exercices du jour</div>

            <div class="pvp-exercises">
                <div
                    v-for="(section, idx) in exerciseSections"
                    :key="idx"
                    class="pvp-ex-card"
                    :class="{
                        'is-done':    done || idx < (activeStep ?? 0),
                        'is-active':  !done && idx === activeStep,
                        'is-waiting': !done && idx > (activeStep ?? 0),
                    }"
                >
                    <div class="pvp-ex-top">
                        <div class="pvp-ex-icon" :class="section.color">
                            <i class="ti" :class="section.icon" aria-hidden="true"></i>
                        </div>
                        <div class="pvp-ex-info">
                            <div class="pvp-ex-title">{{ section.title }}</div>
                            <div class="pvp-ex-duration">~ {{ durationPerSection }} min</div>
                        </div>
                        <div class="pvp-ex-status">
                            <i v-if="done || idx < (activeStep ?? 0)" class="ti ti-circle-check pvp-status-done" aria-hidden="true"></i>
                            <div v-else-if="!done && idx === activeStep" class="pvp-status-ring"></div>
                            <i v-else class="ti ti-lock pvp-status-lock" aria-hidden="true"></i>
                        </div>
                    </div>

                    <div v-if="!done && idx === activeStep" class="pvp-ex-content">
                        <div class="pvp-ex-body" v-html="renderLines(section.lines)"></div>
                        <button
                            v-if="idx < exerciseSections.length - 1"
                            @click="startStep(idx + 1)"
                            class="pvp-ex-btn pvp-ex-btn--next"
                        >
                            Etape suivante <i class="ti ti-arrow-right" aria-hidden="true"></i>
                        </button>
                        <button
                            v-else
                            @click="activeStep = null"
                            class="pvp-ex-btn pvp-ex-btn--done"
                        >
                            <i class="ti ti-check" aria-hidden="true"></i> J'ai termine les exercices
                        </button>
                    </div>

                    <div v-if="done || (activeStep !== null && idx < activeStep)" class="pvp-ex-done-bar">
                        <div class="pvp-ex-body pvp-ex-body--compact" v-html="renderLines(section.lines)"></div>
                    </div>

                    <div v-if="idx === 0 && activeStep === null && !done" class="pvp-ex-content">
                        <button @click="startStep(0)" class="pvp-ex-btn pvp-ex-btn--start">
                            Commencer les exercices <i class="ti ti-arrow-right" aria-hidden="true"></i>
                        </button>
                    </div>
                </div>
            </div>

            <div class="pvp-section-label pvp-section-label--mt">Micro-defi du jour</div>

            <div class="pvp-challenge">
                <div class="pvp-challenge-icon">
                    <i class="ti ti-bolt" aria-hidden="true"></i>
                </div>
                <div class="pvp-challenge-body">
                    <div class="pvp-challenge-label">Application concrete</div>
                    <div class="pvp-challenge-text">{{ practice.micro_challenge }}</div>
                </div>
            </div>

            <div class="pvp-section-label pvp-section-label--mt">
                {{ done ? 'Mettre a jour mon ressenti' : 'Valider la pratique' }}
            </div>

            <div class="pvp-completion">
                <div v-if="done" class="pvp-done-badge">
                    <i class="ti ti-circle-check" aria-hidden="true"></i> Pratique integree
                </div>
                <p v-else class="pvp-completion-hint">
                    Marque cette pratique comme integree pour gagner {{ eclatsPerPractice }} Eclats.
                </p>

                <div class="pvp-felt-row">
                    <span class="pvp-felt-label">Ressenti</span>
                    <div class="pvp-felt-btns">
                        <button
                            v-for="n in 5" :key="n"
                            type="button"
                            @click="form.felt_score = n"
                            class="pvp-felt-btn"
                            :class="{ 'is-active': form.felt_score === n }"
                        >{{ n }}</button>
                    </div>
                </div>

                <textarea
                    v-model="form.notes"
                    rows="3"
                    class="pvp-notes"
                    placeholder="Ce que tu as teste, ce que ca a revele..."
                ></textarea>

                <button
                    @click="submit"
                    :disabled="form.processing"
                    class="pvp-submit-btn"
                    :class="{ 'is-done': done }"
                >
                    <i class="ti" :class="done ? 'ti-refresh' : 'ti-check'" aria-hidden="true"></i>
                    {{ done ? 'Enregistrer le ressenti' : 'Marquer comme integree' }}
                </button>
            </div>

            <div class="pvp-nav">
                <Link
                    v-if="nav.prev"
                    :href="route('praxivision.show', nav.prev)"
                    class="pvp-nav-link"
                    style="text-decoration:none;"
                >
                    <i class="ti ti-arrow-left" aria-hidden="true"></i> Jour {{ nav.prev }}
                </Link>
                <span v-else></span>

                <Link
                    v-if="nav.next"
                    :href="route('praxivision.show', nav.next)"
                    class="pvp-nav-link pvp-nav-link--next"
                    style="text-decoration:none;"
                >
                    Jour {{ nav.next }} <i class="ti ti-arrow-right" aria-hidden="true"></i>
                </Link>
                <span v-else class="pvp-nav-end">La prochaine pratique se debloque demain</span>
            </div>

        </div>
    </CandidateLayout>
</template>

<style scoped>
.pvp-shell {
    max-width: 640px;
    margin: 0 auto;
    padding: 0 0 3rem;
}
.pvp-topbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1.25rem 0 1rem;
    gap: 0.5rem;
}
.pvp-back {
    display: flex;
    align-items: center;
    gap: 5px;
    font-size: 0.82rem;
    color: var(--text-secondary);
    flex-shrink: 0;
}
.pvp-topbar-center { text-align: center; flex: 1; }
.pvp-day-label {
    font-size: 0.82rem;
    font-weight: 600;
    color: var(--text-primary);
    font-family: var(--font-data);
}
.pvp-bloc-label {
    font-size: 0.68rem;
    color: var(--text-muted);
    text-transform: uppercase;
    letter-spacing: 0.05em;
    margin-top: 1px;
}
.pvp-xp-pill {
    display: flex;
    align-items: center;
    gap: 4px;
    font-size: 0.72rem;
    font-weight: 500;
    color: var(--color-primary-dark, #7D5010);
    background: rgba(184,122,26,0.1);
    border-radius: 999px;
    padding: 4px 10px;
    flex-shrink: 0;
}
.pvp-hero {
    padding: 1.25rem 0;
    border-bottom: 1px solid var(--glass-border, #e5e7eb);
    margin-bottom: 1.25rem;
}
.pvp-hero-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    background: rgba(184,122,26,0.1);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.4rem;
    color: var(--color-primary, #B87A1A);
    margin-bottom: 0.75rem;
}
.pvp-hero-title {
    font-family: var(--font-display);
    font-size: 1.6rem;
    font-weight: 700;
    color: var(--text-primary);
    letter-spacing: -0.02em;
    line-height: 1.2;
    margin-bottom: 0.5rem;
}
.pvp-hero-summary {
    font-size: 0.95rem;
    color: var(--text-secondary);
    line-height: 1.6;
    margin-bottom: 0.75rem;
    font-style: italic;
}
.pvp-hero-meta { display: flex; gap: 6px; flex-wrap: wrap; }
.pvp-meta-chip {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    font-size: 0.72rem;
    padding: 3px 9px;
    border-radius: 999px;
    background: var(--bg-elevated, #eee);
    color: var(--text-secondary);
    font-weight: 500;
}
.pvp-meta-chip--done { background: rgba(16,185,129,0.1); color: #065F46; }
.pvp-meta-chip--active { background: rgba(184,122,26,0.1); color: var(--color-primary-dark, #7D5010); }
.pvp-section-label {
    font-size: 0.68rem;
    text-transform: uppercase;
    letter-spacing: 0.07em;
    color: var(--text-muted, #aaa);
    margin-bottom: 0.65rem;
}
.pvp-section-label--mt { margin-top: 1.5rem; }
.pvp-exercises { display: flex; flex-direction: column; gap: 8px; margin-bottom: 0; }
.pvp-ex-card {
    border: 1px solid var(--glass-border, #e5e7eb);
    border-radius: 12px;
    overflow: hidden;
    background: var(--bg-elevated, #fafafa);
}
.pvp-ex-card.is-done { opacity: 0.65; }
.pvp-ex-card.is-active { border-color: var(--color-primary, #B87A1A); background: rgba(184,122,26,0.03); }
.pvp-ex-card.is-waiting { opacity: 0.45; }
.pvp-ex-top {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.85rem 1rem;
}
.pvp-ex-icon {
    width: 38px;
    height: 38px;
    border-radius: 9px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1rem;
    flex-shrink: 0;
}
.pvp-ex-icon.warm { background: rgba(184,122,26,0.12); color: var(--color-primary, #B87A1A); }
.pvp-ex-icon.cool { background: rgba(14,116,144,0.1); color: #0E7490; }
.pvp-ex-icon.green { background: rgba(16,185,129,0.1); color: #059669; }
.pvp-ex-info { flex: 1; }
.pvp-ex-title {
    font-size: 0.9rem;
    font-weight: 600;
    color: var(--text-primary);
    font-family: var(--font-display);
    margin-bottom: 2px;
}
.pvp-ex-duration { font-size: 0.72rem; color: var(--text-muted); }
.pvp-ex-status { display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.pvp-status-done { font-size: 1.1rem; color: #10B981; }
.pvp-status-ring {
    width: 20px;
    height: 20px;
    border-radius: 50%;
    border: 2px solid var(--color-primary, #B87A1A);
}
.pvp-status-lock { font-size: 0.82rem; color: var(--text-muted); }
.pvp-ex-content {
    border-top: 1px solid var(--glass-border, #e5e7eb);
    padding: 0.9rem 1rem 1rem;
}
.pvp-ex-done-bar {
    border-top: 1px solid var(--glass-border, #e5e7eb);
    padding: 0.6rem 1rem 0.75rem;
}
.pvp-ex-body {
    font-size: 0.88rem;
    color: var(--text-primary);
    line-height: 1.7;
    margin-bottom: 0.9rem;
}
.pvp-ex-body--compact {
    font-size: 0.8rem;
    color: var(--text-secondary);
    margin-bottom: 0;
}
.pvp-ex-body :deep(p) { margin: 0.4rem 0; }
.pvp-ex-body :deep(ul),
.pvp-ex-body :deep(ol) { margin: 0.4rem 0 0.4rem 1.1rem; }
.pvp-ex-body :deep(li) { margin: 0.25rem 0; }
.pvp-ex-body :deep(strong) { color: var(--text-primary); }
.pvp-ex-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    border: none;
    border-radius: 8px;
    padding: 8px 16px;
    font-size: 0.82rem;
    font-weight: 600;
    cursor: pointer;
}
.pvp-ex-btn--next { background: var(--bg-elevated, #eee); color: var(--text-secondary); }
.pvp-ex-btn--done { background: var(--color-primary, #B87A1A); color: #fff; }
.pvp-ex-btn--start { background: var(--color-primary, #B87A1A); color: #fff; }
.pvp-challenge {
    display: flex;
    gap: 0.9rem;
    align-items: flex-start;
    border: 1px dashed var(--color-primary, #B87A1A);
    border-radius: 12px;
    padding: 1rem 1.1rem;
    background: rgba(184,122,26,0.04);
}
.pvp-challenge-icon {
    width: 36px;
    height: 36px;
    border-radius: 8px;
    background: rgba(184,122,26,0.12);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1rem;
    color: var(--color-primary, #B87A1A);
    flex-shrink: 0;
}
.pvp-challenge-label {
    font-size: 0.68rem;
    text-transform: uppercase;
    letter-spacing: 0.07em;
    color: var(--color-primary, #B87A1A);
    font-weight: 700;
    margin-bottom: 5px;
}
.pvp-challenge-text {
    font-size: 0.92rem;
    color: var(--text-primary);
    line-height: 1.6;
}
.pvp-completion {
    border: 1px solid var(--glass-border, #e5e7eb);
    border-radius: 12px;
    padding: 1.1rem 1.25rem;
    background: var(--bg-elevated, #fafafa);
}
.pvp-done-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 0.85rem;
    font-weight: 600;
    color: #059669;
    margin-bottom: 0.75rem;
}
.pvp-completion-hint {
    font-size: 0.82rem;
    color: var(--text-secondary);
    margin-bottom: 0.9rem;
}
.pvp-felt-row {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    margin-bottom: 0.75rem;
}
.pvp-felt-label { font-size: 0.8rem; color: var(--text-secondary); white-space: nowrap; }
.pvp-felt-btns { display: flex; gap: 6px; }
.pvp-felt-btn {
    width: 36px;
    height: 36px;
    border-radius: 8px;
    border: 1px solid var(--glass-border, #e5e7eb);
    background: transparent;
    color: var(--text-secondary);
    font-weight: 600;
    font-size: 0.85rem;
    cursor: pointer;
}
.pvp-felt-btn.is-active {
    background: var(--color-primary, #B87A1A);
    border-color: var(--color-primary, #B87A1A);
    color: #fff;
}
.pvp-notes {
    width: 100%;
    border: 1px solid var(--glass-border, #e5e7eb);
    border-radius: 8px;
    padding: 0.6rem 0.75rem;
    font-family: var(--font-body);
    font-size: 0.85rem;
    resize: vertical;
    margin-bottom: 0.9rem;
    background: var(--bg-base, #fff);
    color: var(--text-primary);
}
.pvp-submit-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: var(--color-primary, #B87A1A);
    color: #fff;
    border: none;
    border-radius: 999px;
    padding: 0.65rem 1.4rem;
    font-size: 0.88rem;
    font-weight: 600;
    cursor: pointer;
}
.pvp-submit-btn:disabled { opacity: 0.6; cursor: default; }
.pvp-submit-btn.is-done { background: var(--text-secondary); }
.pvp-nav {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-top: 2rem;
    padding-top: 1rem;
    border-top: 1px solid var(--glass-border, #e5e7eb);
}
.pvp-nav-link {
    display: flex;
    align-items: center;
    gap: 5px;
    font-size: 0.85rem;
    color: var(--text-secondary);
}
.pvp-nav-link--next { color: var(--color-primary, #B87A1A); font-weight: 600; }
.pvp-nav-end { font-size: 0.78rem; color: var(--text-muted); }
</style>
