<script setup>
import { computed, ref } from 'vue'
import CandidateLayout from '@/Layouts/CandidateLayout.vue'
import ScoreGauge from '@/Components/ScoreGauge.vue'
import RadarChart from '@/Components/RadarChart.vue'
import MarkdownText from '@/Components/MarkdownText.vue'

const props = defineProps({ attempt: Object, result: Object })
const scoring   = computed(() => props.result?.scoring ?? {})

const normScores    = computed(() => scoring.value.norm_scores       ?? {})
const dimensions    = computed(() => scoring.value.dimensions        ?? {})
const globalScore   = computed(() => scoring.value.global_score      ?? 0)
const niveau        = computed(() => scoring.value.niveau            ?? '')
const phrase        = computed(() => scoring.value.phrase            ?? '')
const topForces     = computed(() => scoring.value.top_forces        ?? [])
const topDev        = computed(() => scoring.value.top_dev           ?? [])
const weekPlan      = computed(() => scoring.value.week_plan         ?? {})
const program       = computed(() => scoring.value.exercise_program  ?? {})
const motivatingStat = computed(() => scoring.value.motivating_stat  ?? '')

// ── Parcours 60 jours ──────────────────────────────────────────────────────
const journey       = computed(() => props.result?.journey           ?? {})
const journeyDays   = computed(() => journey.value.days              ?? [])
const currentDay    = computed(() => journey.value.current_day       ?? 1)
const streak        = computed(() => journey.value.streak            ?? 0)
const completionRate = computed(() => journey.value.completion_rate  ?? 0)
const todayEntry    = computed(() =>
    journeyDays.value.find(d => d.day === currentDay.value) ?? null
)
const completedDays = computed(() => new Set(journey.value.completed_days ?? []))

// ── Navigation onglets ─────────────────────────────────────────────────────
const activeTab  = ref('resultats')
const openExercise = ref(null)
const toggleExercise = (id) => {
    openExercise.value = openExercise.value === id ? null : id
}

// ── Couleurs ───────────────────────────────────────────────────────────────
const scoreColor = (score) => {
    if (score < 30) return 'var(--pt-danger, #DC2626)'
    if (score < 50) return 'var(--pt-warning, #D97706)'
    if (score < 70) return 'var(--pt-info, #0891B2)'
    return 'var(--pt-success, #16A34A)'
}
const scoreLabel = (score) => {
    if (score < 30) return 'Zone prioritaire'
    if (score < 50) return 'En développement'
    if (score < 70) return 'Solide'
    return 'Point fort'
}
const dimColor = (dimKey) => dimensions.value[dimKey]?.color ?? 'var(--pt-navy)'
const stars    = (n) => '★'.repeat(n) + '☆'.repeat(3 - n)

// Axes du radar : valeur 0–100 issue de norm_scores, label + couleur depuis dimensions
const radarAxes = computed(() =>
    Object.entries(dimensions.value).map(([dimKey, dimInfo]) => ({
        label: dimInfo.label ?? dimKey,
        value: Math.round(Number(normScores.value[dimKey]) || 0),
        color: dimInfo.color,
    }))
)

const globalColor = computed(() => {
    if (globalScore.value < 30) return 'var(--pt-danger, #DC2626)'
    if (globalScore.value < 50) return 'var(--pt-warning, #D97706)'
    if (globalScore.value < 70) return 'var(--pt-info, #0891B2)'
    if (globalScore.value < 85) return 'var(--pt-success, #16A34A)'
    return 'var(--pt-gold, #B45309)'
})

// ── Phase couleurs ─────────────────────────────────────────────────────────
const phaseColor = (phase) => {
    const map = {
        decouverte:   'var(--pt-indigo,   #4F46E5)',
        installation: 'var(--pt-info,     #0891B2)',
        renforcement: 'var(--pt-warning,  #D97706)',
        maitrise:     'var(--pt-success,  #16A34A)',
    }
    return map[phase] ?? 'var(--pt-navy)'
}
const phaseLabel = (phase) => {
    const map = {
        decouverte:   'Découverte',
        installation: 'Installation',
        renforcement: 'Renforcement',
        maitrise:     'Maîtrise',
    }
    return map[phase] ?? phase
}

// ── Statut d'une case du parcours ──────────────────────────────────────────
const dayStatus = (dayNum) => {
    if (completedDays.value.has(dayNum)) return 'done'
    if (dayNum === currentDay.value) return 'today'
    if (dayNum < currentDay.value) return 'missed'
    return 'future'
}

// ── Exercices à plat ───────────────────────────────────────────────────────
const allExercises = computed(() => {
    const list = []
    Object.entries(program.value).forEach(([dimKey, exercises]) => {
        exercises.forEach(ex => list.push({ ...ex, dimKey }))
    })
    const seen = new Set()
    return list.filter(e => { if (seen.has(e.id)) return false; seen.add(e.id); return true })
})
</script>

<template>
    <CandidateLayout>
        <Head title="Tes résultats — PraxiFlow" />

        <div style="max-width: 56rem; margin: 0 auto;">

            <!-- ══════════════════════════════════════════════════
                 EN-TÊTE
            ══════════════════════════════════════════════════ -->
            <div style="text-align: center; margin-bottom: 2.5rem;">
                <span class="pt-badge">PraxiFlow · Gestion du temps</span>
                <h1 style="font-size: 2.25rem; font-weight: 600; letter-spacing: -0.025em; margin-top: 1rem; color: var(--pt-navy);">
                    Ta productivité, analysée
                </h1>
                <p style="margin-top: 0.5rem; font-size: 0.875rem; max-width: 36rem; margin-left: auto; margin-right: auto; color: var(--pt-text-muted);">
                    {{ phrase }}
                </p>
            </div>

            <!-- ══════════════════════════════════════════════════
                 SCORE GLOBAL
            ══════════════════════════════════════════════════ -->
            <section class="pt-card" style="padding: 2rem; margin-bottom: 2rem; text-align: center;">
                <p style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 0.5rem; color: var(--pt-text-muted);">
                    Score de productivité global
                </p>
                <div style="display: flex; align-items: center; justify-content: center; gap: 1.5rem;">
                    <ScoreGauge :score="globalScore" :color="globalColor" :size="140" />
                    <div style="text-align: left;">
                        <p style="font-size: 1.125rem; font-weight: 600;" :style="{ color: globalColor }">{{ niveau }}</p>
                        <p style="font-size: 0.875rem; margin-top: 0.25rem; max-width: 20rem; color: var(--pt-text-muted);">
                            {{ motivatingStat }}
                        </p>
                    </div>
                </div>
            </section>

            <!-- ══════════════════════════════════════════════════
                 NAVIGATION ONGLETS
            ══════════════════════════════════════════════════ -->
            <div style="display: flex; gap: 0.25rem; margin-bottom: 2rem; border-bottom: 1px solid var(--pt-cream, #E5E7EB);">
                <button
                    v-for="tab in [
                        { key: 'resultats', label: 'Résultats' },
                        { key: 'parcours',  label: 'Parcours 60 jours' },
                        { key: 'programme', label: 'Exercices' },
                        { key: 'plan',      label: 'Plan 7 jours' },
                    ]"
                    :key="tab.key"
                    @click="activeTab = tab.key"
                    style="padding: 0.75rem 1.25rem; font-size: 0.875rem; font-weight: 500; background: none; border: none; cursor: pointer; transition: all 0.2s;"
                    :style="activeTab === tab.key
                        ? 'border-bottom: 2px solid var(--pt-navy); color: var(--pt-navy); margin-bottom: -1px;'
                        : 'color: var(--pt-text-muted, #6B7280);'"
                >
                    {{ tab.label }}
                </button>
            </div>

            <!-- ══════════════════════════════════════════════════
                 ONGLET : RÉSULTATS
            ══════════════════════════════════════════════════ -->
            <div v-show="activeTab === 'resultats'">

                <!-- Profil en un coup d'œil — toile d'araignée -->
                <section v-if="radarAxes.length >= 3" class="pt-card" style="padding: 2rem; margin-bottom: 1.5rem;">
                    <h2 style="font-size: 1.25rem; font-weight: 600; margin-bottom: 0.25rem; color: var(--pt-navy);">Ton profil en un coup d'œil</h2>
                    <p style="font-size: 0.875rem; margin-bottom: 1rem; color: var(--pt-text-muted);">Tes 5 dimensions de productivité, sur une échelle de 0 à 100.</p>
                    <div style="display: flex; justify-content: center;">
                        <RadarChart :axes="radarAxes" />
                    </div>
                </section>

                <!-- 5 Jauges dimensions -->
                <section class="pt-card" style="padding: 2rem; margin-bottom: 1.5rem;">
                    <h2 style="font-size: 1.25rem; font-weight: 600; margin-bottom: 1.5rem; color: var(--pt-navy);">Tes 5 dimensions</h2>
                    <div style="display: flex; flex-direction: column; gap: 1.5rem;">
                        <div v-for="(dimInfo, dimKey) in dimensions" :key="dimKey">
                            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.5rem;">
                                <div>
                                    <span style="font-weight: 500; color: var(--pt-navy);">{{ dimInfo.label }}</span>
                                    <p style="font-size: 0.75rem; margin-top: 0.125rem; color: var(--pt-text-muted);">{{ dimInfo.description }}</p>
                                </div>
                                <span
                                    style="font-size: 0.75rem; padding: 0.25rem 0.75rem; border-radius: 9999px; font-weight: 500; white-space: nowrap; margin-left: 1rem;"
                                    :style="{
                                        backgroundColor: scoreColor(normScores[dimKey] ?? 0) + '18',
                                        color: scoreColor(normScores[dimKey] ?? 0)
                                    }"
                                >
                                    {{ scoreLabel(normScores[dimKey] ?? 0) }}
                                </span>
                            </div>
                            <div style="display: flex; align-items: center; gap: 0.75rem;">
                                <div style="flex: 1; border-radius: 9999px; overflow: hidden; height: 10px; background: var(--pt-cream, #F3F4F6);">
                                    <div
                                        style="height: 100%; border-radius: 9999px; transition: width 0.8s ease;"
                                        :style="{
                                            width: (normScores[dimKey] ?? 0) + '%',
                                            backgroundColor: dimColor(dimKey)
                                        }"
                                    ></div>
                                </div>
                                <span style="font-size: 0.875rem; font-weight: 600; width: 2.5rem; text-align: right;" :style="{ color: dimColor(dimKey) }">
                                    {{ normScores[dimKey] ?? 0 }}
                                </span>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Forces & axes de développement -->
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(16rem, 1fr)); gap: 1.5rem; margin-bottom: 1.5rem;">
                    <section class="pt-card" style="padding: 1.5rem;">
                        <h2 style="font-weight: 600; margin-bottom: 1rem; color: var(--pt-navy);">Tes points forts</h2>
                        <ul style="list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 0.75rem;">
                            <li v-for="(dimKey, i) in topForces" :key="dimKey" style="display: flex; align-items: center; gap: 0.75rem;">
                                <span
                                    style="width: 1.5rem; height: 1.5rem; border-radius: 9999px; display: flex; align-items: center; justify-content: center; font-size: 0.75rem; font-weight: 700; color: white; flex-shrink: 0;"
                                    :style="{ backgroundColor: dimColor(dimKey) }"
                                >{{ i + 1 }}</span>
                                <div>
                                    <p style="font-size: 0.875rem; font-weight: 500; color: var(--pt-navy);">{{ dimensions[dimKey]?.label }}</p>
                                    <p style="font-size: 0.75rem; color: var(--pt-text-muted);">Score : {{ normScores[dimKey] }}/100</p>
                                </div>
                            </li>
                        </ul>
                    </section>

                    <section class="pt-card" style="padding: 1.5rem;" v-if="topDev.length">
                        <h2 style="font-weight: 600; margin-bottom: 1rem; color: var(--pt-navy);">Priorités de développement</h2>
                        <ul style="list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 0.75rem;">
                            <li v-for="(dimKey, i) in topDev" :key="dimKey" style="display: flex; align-items: center; gap: 0.75rem;">
                                <span
                                    style="width: 1.5rem; height: 1.5rem; border-radius: 9999px; display: flex; align-items: center; justify-content: center; font-size: 0.75rem; font-weight: 700; color: white; flex-shrink: 0; background-color: var(--pt-danger, #DC2626);"
                                >{{ i + 1 }}</span>
                                <div>
                                    <p style="font-size: 0.875rem; font-weight: 500; color: var(--pt-navy);">{{ dimensions[dimKey]?.label }}</p>
                                    <p style="font-size: 0.75rem; color: var(--pt-text-muted);">Score : {{ normScores[dimKey] }}/100</p>
                                </div>
                            </li>
                        </ul>
                    </section>
                </div>
            </div>

            <!-- ══════════════════════════════════════════════════
                 ONGLET : PARCOURS 60 JOURS
            ══════════════════════════════════════════════════ -->
            <div v-show="activeTab === 'parcours'">

                <!-- Bandeau stats parcours -->
                <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem; margin-bottom: 1.5rem;">
                    <section class="pt-card" style="padding: 1.25rem; text-align: center;">
                        <p style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.08em; color: var(--pt-text-muted); margin-bottom: 0.25rem;">Jour actuel</p>
                        <p style="font-size: 2rem; font-weight: 700; color: var(--pt-navy);">{{ currentDay }}<span style="font-size: 1rem; font-weight: 400; color: var(--pt-text-muted);">/60</span></p>
                    </section>
                    <section class="pt-card" style="padding: 1.25rem; text-align: center;">
                        <p style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.08em; color: var(--pt-text-muted); margin-bottom: 0.25rem;">Streak</p>
                        <p style="font-size: 2rem; font-weight: 700; color: var(--pt-warning, #D97706);">{{ streak }}<span style="font-size: 1rem; font-weight: 400; color: var(--pt-text-muted);"> j.</span></p>
                    </section>
                    <section class="pt-card" style="padding: 1.25rem; text-align: center;">
                        <p style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.08em; color: var(--pt-text-muted); margin-bottom: 0.25rem;">Progression</p>
                        <p style="font-size: 2rem; font-weight: 700; color: var(--pt-success, #16A34A);">{{ completionRate }}<span style="font-size: 1rem; font-weight: 400; color: var(--pt-text-muted);">%</span></p>
                    </section>
                </div>

                <!-- Exercice du jour -->
                <section
                    v-if="todayEntry"
                    class="pt-card"
                    style="padding: 1.5rem; margin-bottom: 1.5rem; border-left: 4px solid var(--pt-gold, #B45309);"
                >
                    <div style="display: flex; align-items: flex-start; justify-content: space-between; gap: 1rem; flex-wrap: wrap;">
                        <div style="flex: 1; min-width: 0;">
                            <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem; flex-wrap: wrap;">
                                <span
                                    style="font-size: 0.7rem; font-weight: 600; padding: 0.2rem 0.6rem; border-radius: 9999px; color: white;"
                                    :style="{ backgroundColor: phaseColor(todayEntry.phase) }"
                                >
                                    {{ phaseLabel(todayEntry.phase) }}
                                </span>
                                <span style="font-size: 0.75rem; color: var(--pt-text-muted);">Jour {{ todayEntry.day }} · Semaine {{ todayEntry.week }}</span>
                            </div>
                            <h3 style="font-size: 1.125rem; font-weight: 600; color: var(--pt-navy); margin-bottom: 0.5rem;">
                                {{ todayEntry.title }}
                            </h3>
                            <p style="font-size: 0.875rem; color: var(--pt-text-muted); margin-bottom: 0.75rem; font-style: italic;">
                                « {{ todayEntry.intention }} »
                            </p>
                        </div>
                        <div style="text-align: right; flex-shrink: 0;">
                            <span style="font-size: 1.5rem; font-weight: 700; color: var(--pt-navy);">{{ todayEntry.duration_minutes }}'</span>
                        </div>
                    </div>

                    <!-- Anchor habit -->
                    <div style="background: var(--pt-cream, #F8F7F4); border-radius: 0.5rem; padding: 0.75rem 1rem; margin-bottom: 0.75rem;">
                        <p style="font-size: 0.75rem; font-weight: 600; color: var(--pt-navy); margin-bottom: 0.2rem;">Anchor habit</p>
                        <p style="font-size: 0.875rem; color: var(--pt-text-muted);">{{ todayEntry.anchor }}</p>
                    </div>

                    <!-- Micro-habit -->
                    <div style="margin-bottom: 0.75rem;">
                        <p style="font-size: 0.75rem; font-weight: 600; color: var(--pt-navy); margin-bottom: 0.2rem;">Exercice du jour</p>
                        <p style="font-size: 0.875rem; color: var(--pt-navy);">{{ todayEntry.micro_habit }}</p>
                    </div>

                    <!-- Tip science -->
                    <div style="border-top: 1px solid var(--pt-cream, #E5E7EB); padding-top: 0.75rem; margin-top: 0.25rem;">
                        <p style="font-size: 0.75rem; color: var(--pt-text-muted);">
                            <span style="font-weight: 600; color: var(--pt-navy);">Science : </span>{{ todayEntry.tip_science }}
                        </p>
                    </div>

                    <!-- CTA -->
                    <div style="margin-top: 1rem;">
                        <button
                            class="pt-btn"
                            style="width: 100%;"
                        >
                            Commencer l'exercice · {{ todayEntry.duration_minutes }} min
                        </button>
                    </div>
                </section>

                <!-- Thème de la semaine -->
                <div
                    v-if="todayEntry"
                    style="padding: 0.75rem 1rem; border-radius: 0.5rem; margin-bottom: 1.5rem; font-size: 0.875rem; background: color-mix(in srgb, var(--pt-navy) 8%, transparent);"
                >
                    <span style="font-weight: 600; color: var(--pt-navy);">Thème semaine {{ todayEntry.week }} : </span>
                    <span style="color: var(--pt-text-muted);">{{ todayEntry.weekly_theme }}</span>
                </div>

                <!-- Grille 60 cases (10×6) -->
                <section class="pt-card" style="padding: 1.5rem; margin-bottom: 1.5rem;">
                    <h3 style="font-size: 1rem; font-weight: 600; margin-bottom: 1.25rem; color: var(--pt-navy);">Vue d'ensemble — 60 jours</h3>

                    <!-- Légende phases -->
                    <div style="display: flex; gap: 1rem; flex-wrap: wrap; margin-bottom: 1rem;">
                        <div v-for="(info, key) in { decouverte: 'Découverte J1–14', installation: 'Installation J15–28', renforcement: 'Renforcement J29–42', maitrise: 'Maîtrise J43–60' }" :key="key"
                            style="display: flex; align-items: center; gap: 0.35rem; font-size: 0.7rem; color: var(--pt-text-muted);">
                            <span style="width: 0.6rem; height: 0.6rem; border-radius: 2px;" :style="{ backgroundColor: phaseColor(key) }"></span>
                            {{ info }}
                        </div>
                    </div>

                    <!-- Grille 10 colonnes × 6 lignes -->
                    <div style="display: grid; grid-template-columns: repeat(10, 1fr); gap: 0.3rem;">
                        <div
                            v-for="n in 60"
                            :key="n"
                            style="aspect-ratio: 1; border-radius: 0.25rem; display: flex; align-items: center; justify-content: center; font-size: 0.6rem; font-weight: 600; cursor: default; transition: transform 0.1s;"
                            :title="`Jour ${n}`"
                            :style="{
                                backgroundColor: dayStatus(n) === 'done'
                                    ? phaseColor(n <= 14 ? 'decouverte' : n <= 28 ? 'installation' : n <= 42 ? 'renforcement' : 'maitrise')
                                    : dayStatus(n) === 'today'
                                        ? 'var(--pt-gold, #B45309)'
                                        : dayStatus(n) === 'missed'
                                            ? 'var(--pt-danger-light, #FEE2E2)'
                                            : 'var(--pt-cream, #F3F4F6)',
                                color: dayStatus(n) === 'done' || dayStatus(n) === 'today'
                                    ? 'white'
                                    : dayStatus(n) === 'missed'
                                        ? 'var(--pt-danger, #DC2626)'
                                        : 'var(--pt-text-muted)',
                                transform: dayStatus(n) === 'today' ? 'scale(1.15)' : 'scale(1)',
                                boxShadow: dayStatus(n) === 'today' ? '0 0 0 2px var(--pt-gold, #B45309)' : 'none',
                            }"
                        >
                            {{ n }}
                        </div>
                    </div>

                    <!-- Légende statuts -->
                    <div style="display: flex; gap: 1.25rem; flex-wrap: wrap; margin-top: 1rem;">
                        <div style="display: flex; align-items: center; gap: 0.35rem; font-size: 0.7rem; color: var(--pt-text-muted);">
                            <span style="width: 0.75rem; height: 0.75rem; border-radius: 3px; background: var(--pt-success, #16A34A);"></span> Complété
                        </div>
                        <div style="display: flex; align-items: center; gap: 0.35rem; font-size: 0.7rem; color: var(--pt-text-muted);">
                            <span style="width: 0.75rem; height: 0.75rem; border-radius: 3px; background: var(--pt-gold, #B45309);"></span> Aujourd'hui
                        </div>
                        <div style="display: flex; align-items: center; gap: 0.35rem; font-size: 0.7rem; color: var(--pt-text-muted);">
                            <span style="width: 0.75rem; height: 0.75rem; border-radius: 3px; background: var(--pt-danger-light, #FEE2E2);"></span> Manqué
                        </div>
                        <div style="display: flex; align-items: center; gap: 0.35rem; font-size: 0.7rem; color: var(--pt-text-muted);">
                            <span style="width: 0.75rem; height: 0.75rem; border-radius: 3px; background: var(--pt-cream, #F3F4F6);"></span> À venir
                        </div>
                    </div>
                </section>

                <!-- Récompense du jour -->
                <div
                    v-if="todayEntry"
                    class="pt-card"
                    style="padding: 1.25rem; background: color-mix(in srgb, var(--pt-success, #16A34A) 6%, white);"
                >
                    <p style="font-size: 0.75rem; font-weight: 600; color: var(--pt-success, #16A34A); margin-bottom: 0.25rem;">Votre récompense du jour</p>
                    <p style="font-size: 0.875rem; color: var(--pt-navy); font-style: italic;">« {{ todayEntry.reward }} »</p>
                </div>
            </div>

            <!-- ══════════════════════════════════════════════════
                 ONGLET : EXERCICES
            ══════════════════════════════════════════════════ -->
            <div v-show="activeTab === 'programme'">
                <p style="font-size: 0.875rem; margin-bottom: 1.5rem; color: var(--pt-text-muted);">
                    {{ allExercises.length }} exercices scientifiques classés par dimension. Clique pour voir les instructions complètes.
                </p>

                <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                    <div v-for="ex in allExercises" :key="ex.id" class="pt-card" style="overflow: hidden;">
                        <button
                            style="width: 100%; text-align: left; padding: 1.25rem 1.5rem; display: flex; align-items: flex-start; gap: 1rem; background: none; border: none; cursor: pointer;"
                            @click="toggleExercise(ex.id)"
                            :aria-expanded="openExercise === ex.id"
                            :aria-controls="'flow-exercise-panel-' + ex.id"
                        >
                            <div style="width: 4px; align-self: stretch; border-radius: 9999px; flex-shrink: 0;" :style="{ backgroundColor: dimColor(ex.dimKey) }"></div>
                            <div style="flex: 1; min-width: 0;">
                                <span style="font-weight: 500; font-size: 0.875rem; color: var(--pt-navy);">{{ ex.title }}</span>
                                <div style="display: flex; align-items: center; gap: 0.75rem; margin-top: 0.25rem; flex-wrap: wrap;">
                                    <span style="font-size: 0.75rem; color: var(--pt-text-muted);">{{ dimensions[ex.dimKey]?.label }}</span>
                                    <span style="font-size: 0.75rem; color: var(--pt-text-muted);">·</span>
                                    <span style="font-size: 0.75rem; color: var(--pt-text-muted);">{{ ex.duration_minutes }} min</span>
                                    <span style="font-size: 0.75rem; color: var(--pt-text-muted);">·</span>
                                    <span style="font-size: 0.75rem;" :style="{ color: dimColor(ex.dimKey) }">{{ stars(ex.difficulty) }}</span>
                                </div>
                            </div>
                            <span
                                style="flex-shrink: 0; font-size: 1.125rem; transition: transform 0.2s; color: var(--pt-text-muted);"
                                :style="{ transform: openExercise === ex.id ? 'rotate(180deg)' : 'rotate(0deg)' }"
                            >▾</span>
                        </button>

                        <div v-show="openExercise === ex.id" :id="'flow-exercise-panel-' + ex.id" style="padding: 0 1.5rem 1.5rem; border-top: 1px solid var(--pt-cream, #F3F4F6);">
                            <div style="margin-top: 1rem; margin-bottom: 1rem; padding: 1rem; border-radius: 0.5rem; font-size: 0.875rem; background: var(--pt-cream, #F8F7F4); color: var(--pt-text-muted);">
                                <p style="font-weight: 500; margin-bottom: 0.25rem; color: var(--pt-navy);">Base scientifique</p>
                                <p>{{ ex.scientific_basis }}</p>
                            </div>
                            <p style="font-size: 0.875rem; font-weight: 600; margin-bottom: 0.75rem; color: var(--pt-navy);">Instructions pas à pas</p>
                            <ol style="list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 0.5rem;">
                                <li v-for="(step, idx) in ex.instructions" :key="idx" style="display: flex; gap: 0.75rem; font-size: 0.875rem;">
                                    <span
                                        style="flex-shrink: 0; width: 1.5rem; height: 1.5rem; border-radius: 9999px; display: flex; align-items: center; justify-content: center; font-size: 0.75rem; font-weight: 700; color: white; margin-top: 0.125rem;"
                                        :style="{ backgroundColor: dimColor(ex.dimKey) }"
                                    >{{ idx + 1 }}</span>
                                    <span style="color: var(--pt-navy);">{{ step }}</span>
                                </li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ══════════════════════════════════════════════════
                 ONGLET : PLAN 7 JOURS
            ══════════════════════════════════════════════════ -->
            <div v-show="activeTab === 'plan'">
                <div class="pt-card" style="padding: 1.5rem; margin-bottom: 1.5rem; border-left: 4px solid var(--pt-gold, #B45309);">
                    <p style="font-weight: 600; color: var(--pt-navy);">Ton plan d'action personnalisé — 7 jours</p>
                    <p style="font-size: 0.875rem; margin-top: 0.25rem; color: var(--pt-text-muted);">
                        Conçu à partir de tes scores. Chaque journée représente 5 à 10 minutes d'exercice ciblé. La régularité prime sur l'intensité.
                    </p>
                </div>

                <div style="display: flex; flex-direction: column; gap: 1rem;">
                    <div v-for="(day, dayKey) in weekPlan" :key="dayKey" class="pt-card" style="padding: 1.5rem;">
                        <div style="display: flex; align-items: flex-start; gap: 1rem;">
                            <div style="flex-shrink: 0; width: 2.5rem; height: 2.5rem; border-radius: 9999px; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 0.875rem; color: white; background: var(--pt-navy);">
                                {{ dayKey.replace('jour_', 'J') }}
                            </div>
                            <div style="flex: 1;">
                                <p style="font-weight: 600; font-size: 0.875rem; color: var(--pt-navy);">{{ day.titre }}</p>
                                <p style="font-size: 0.75rem; margin-top: 0.125rem; margin-bottom: 0.75rem; color: var(--pt-text-muted);">{{ day.description }}</p>
                                <div style="display: flex; flex-wrap: wrap; gap: 0.5rem;">
                                    <span
                                        v-for="ex in day.exercices"
                                        :key="ex.id"
                                        style="font-size: 0.75rem; padding: 0.375rem 0.75rem; border-radius: 9999px; font-weight: 500; cursor: pointer; background: var(--pt-cream, #F3F4F6); color: var(--pt-navy);"
                                        @click="activeTab = 'programme'; openExercise = ex.id"
                                    >
                                        {{ ex.title }} · {{ ex.duration_minutes }} min
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ══════════════════════════════════════════════════
                 SYNTHÈSE IA
            ══════════════════════════════════════════════════ -->
            <div v-if="result?.ai_synthesis" class="mt-6 pt-4 border-t border-amber-200">
                <h3 class="font-semibold mb-2">Synthèse personnalisée</h3>
                <MarkdownText :source="result.ai_synthesis" />
                <p style="margin-top:0.85rem;padding-top:0.7rem;border-top:1px solid rgba(217,119,6,0.25);font-size:11.5px;line-height:1.55;color:#9a8866">
                    <strong style="font-weight:600;color:#57534e">Outil d'auto-évaluation et de développement personnel.</strong>
                    Cette synthèse est générée par IA, à titre informatif. Elle ne constitue pas un avis
                    professionnel et ne remplace pas un psychologue, un médecin ou un coach.
                </p>
            </div>

            <!-- ══════════════════════════════════════════════════
                 FOOTER
            ══════════════════════════════════════════════════ -->
            <div style="text-align: center; margin-top: 3rem; padding-bottom: 2rem;">
                <a :href="route('results.pdf', attempt.id)" class="pt-btn-ghost">
                    Télécharger en PDF
                </a>
            </div>

        </div>
    </CandidateLayout>
</template>
