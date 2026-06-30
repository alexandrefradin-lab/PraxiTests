<script setup>
import { computed } from 'vue'
import CandidateLayout from '@/Layouts/CandidateLayout.vue'
import ScoreGauge from '@/Components/ScoreGauge.vue'
import RadarChart from '@/Components/RadarChart.vue'
import MarkdownText from '@/Components/MarkdownText.vue'
import RestitutionHeader from '@/Components/RestitutionHeader.vue'
import ResultPanel from '@/Components/ResultPanel.vue'
import ResultPdfButton from '@/Components/ResultPdfButton.vue'

const props = defineProps({
    attempt:              Object,
    result:               Object,
    journeyCurrentDay:    { type: Number, default: 1 },
    journeyStreak:        { type: Number, default: 0 },
    journeyCompletedDays: { type: Array,  default: () => [] },
    journeyCompletionRate:{ type: Number, default: 0 },
    journeyTodayEntry:    { type: Object, default: null },
})

const scoring = computed(() => props.result?.scoring ?? {})
const dims    = computed(() => scoring.value.dimensions ?? {})
const norms   = computed(() => scoring.value.norm_scores ?? {})

// Niveau de score → couleur
const scoreColor = (pct) => {
    if (pct < 20) return 'var(--pt-danger, #dc2626)'
    if (pct < 45) return 'var(--pt-warning, #ea580c)'
    if (pct < 70) return 'var(--pt-info, #2563eb)'
    return 'var(--pt-success, #16a34a)'
}

// Niveau de score → couleur éclaircie (lisible sur panneau sombre)
const scoreColorDark = (pct) => {
    if (pct < 20) return '#f87171'
    if (pct < 45) return '#fb923c'
    if (pct < 70) return '#60a5fa'
    return '#4ade80'
}

// Niveau de score → étiquette
const scoreLabel = (pct) => {
    if (pct < 20) return 'À développer'
    if (pct < 45) return 'En construction'
    if (pct < 70) return 'Bien développé'
    return 'Maîtrisé'
}

// Icône SVG inline par dimension (fallback cercle coloré)
const dimIcon = (dimId) => {
    const icons = {
        gestion_du_trac:      '🫀',
        preparation_mentale:  '🧠',
        presence_physique:    '🧍',
        structure_du_discours:'📋',
        impact_vocal:         '🎙️',
    }
    return icons[dimId] ?? '●'
}

// Exercice recommandé du jour
const recommended = computed(() => scoring.value.recommended_exercise ?? null)

// Citation
const quote = computed(() => scoring.value.quote ?? null)

// Méta
const meta  = computed(() => scoring.value.meta ?? {})

// Score global
const global = computed(() => scoring.value.global_score ?? 0)

// Axes du radar : valeur 0–100 issue de norm_scores, label + couleur depuis dimensions
const radarAxes = computed(() =>
    Object.entries(dims.value).map(([dimId, dimInfo]) => ({
        label: dimInfo.label ?? dimId,
        value: Math.round(Number(norms.value[dimId]) || 0),
        color: dimInfo.color,
    }))
)

// ── Parcours 60 jours ────────────────────────────────────────────────────────

const journey = computed(() => props.result?.journey ?? {})
const journeyStats   = computed(() => journey.value.stats ?? {})
const journeyGrid    = computed(() => {
    // Priorité : result.journey.grid (objet {1: true, 2: true, …})
    // Fallback  : journeyCompletedDays (tableau [1, 2, 3, …]) → converti en objet
    if (journey.value.grid) return journey.value.grid
    const grid = {}
    props.journeyCompletedDays.forEach(d => { grid[d] = true })
    return grid
})
const journeyToday   = computed(() => journey.value.today ?? props.journeyTodayEntry ?? null)
const journeyPhases  = computed(() => journey.value.phases ?? {})

const currentDay  = computed(() => journeyStats.value.current_day ?? props.journeyCurrentDay ?? 1)
const streak      = computed(() => journeyStats.value.streak ?? props.journeyStreak ?? 0)
const progressPct = computed(() => journeyStats.value.progress_pct ?? props.journeyCompletionRate ?? 0)
const currentPhase = computed(() => journeyStats.value.phase ?? 'decouverte')

// Couleur par phase
const phaseColor = (phase) => {
    const colors = {
        decouverte:   'var(--pt-phase-discovery, #6366F1)',
        installation: 'var(--pt-phase-install, #8B5CF6)',
        renforcement: 'var(--pt-phase-reinforce, #EC4899)',
        maitrise:     'var(--pt-gold, #F59E0B)',
    }
    return colors[phase] ?? 'var(--pt-navy)'
}

// Couleur d'une case de la grille
const cellColor = (day) => {
    const done = journeyGrid.value[day] === true
    if (done) return phaseColor(dayPhase(day))
    if (day === currentDay.value) return 'var(--pt-gold, #F59E0B)'
    return 'var(--pt-cream, #f8f5ef)'
}

// Opacité d'une case : futur = plus transparent
const cellOpacity = (day) => {
    if (journeyGrid.value[day]) return '1'
    if (day === currentDay.value) return '1'
    if (day > currentDay.value) return '0.35'
    return '0.6'
}

// Phase d'un jour donné
const dayPhase = (day) => {
    if (day <= 15)  return 'decouverte'
    if (day <= 30)  return 'installation'
    if (day <= 45)  return 'renforcement'
    return 'maitrise'
}

// Label phase
const phaseLabel = (phase) => {
    const labels = {
        decouverte:   'Découverte',
        installation: 'Installation',
        renforcement: 'Renforcement',
        maitrise:     'Maîtrise',
    }
    return labels[phase] ?? phase
}

// Jours par semaine pour la grille (10 colonnes × 6 lignes)
const gridRows = computed(() => {
    const rows = []
    for (let row = 0; row < 6; row++) {
        const cols = []
        for (let col = 0; col < 10; col++) {
            cols.push(row * 10 + col + 1)
        }
        rows.push(cols)
    }
    return rows
})
</script>

<template>
    <CandidateLayout>
        <Head title="Tes résultats — PraxiSpeak" />

        <div class="max-w-4xl mx-auto">

            <!-- En-tête ───────────────────────────────────────────────── -->
            <RestitutionHeader
                kicker="PraxiSpeak · Prise de parole en public"
                title="Ton profil d'orateur"
                :subtitle="scoring.phrase_orateur"
            />

            <!-- Score global de l'orateur ─────────────────────────────── -->
            <section class="pt-card ac-card-ornate p-8 mb-8">
                <div class="flex flex-col md:flex-row items-center gap-8">

                    <!-- Jauge circulaire SVG -->
                    <div class="flex-shrink-0">
                        <ScoreGauge :score="global" :color="scoreColor(global)" :size="140" />
                    </div>

                    <!-- Niveau + méta -->
                    <div class="flex-1 text-center md:text-left">
                        <p class="text-xs uppercase tracking-widest mb-1" style="color: var(--pt-text-muted)">
                            Niveau orateur
                        </p>
                        <p class="text-2xl font-semibold mb-3" style="color: var(--pt-navy)">
                            {{ scoring.niveau_orateur }}
                        </p>
                        <div class="flex flex-wrap gap-3 justify-center md:justify-start">
                            <span class="pt-badge">
                                {{ meta.completed_count }} / {{ meta.total_exercises }} exercices
                            </span>
                            <span class="pt-badge">
                                {{ meta.completion_pct }}% accompli
                            </span>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Profil en un coup d'œil — toile d'araignée ─────────────── -->
            <ResultPanel v-if="radarAxes.length >= 3" label="Ton profil en un coup d'œil" class="mb-8">
                <div class="flex justify-center">
                    <RadarChart :axes="radarAxes" dark />
                </div>
            </ResultPanel>

            <!-- Jauges par dimension ──────────────────────────────────── -->
            <ResultPanel class="mb-8">
                <h2 class="ac-panel-title mb-6">
                    Tes 5 dimensions
                </h2>
                <div class="space-y-5">
                    <div
                        v-for="(dimInfo, dimId) in dims"
                        :key="dimId"
                    >
                        <!-- Ligne étiquette + score -->
                        <div class="flex items-center justify-between mb-1">
                            <div class="flex items-center gap-2">
                                <span class="text-lg leading-none">{{ dimIcon(dimId) }}</span>
                                <span class="font-medium ac-dark-name">
                                    {{ dimInfo.label }}
                                </span>
                                <span
                                    v-if="dimId === scoring.top_dimension"
                                    class="pt-badge"
                                    style="background: var(--pt-gold, #f59e0b); color: #fff; font-size: 0.65rem"
                                >Force</span>
                                <span
                                    v-if="dimId === scoring.weak_dimension"
                                    class="pt-badge"
                                    style="background: rgba(240,232,212,0.12); color: #F0E8D4; font-size: 0.65rem"
                                >À travailler</span>
                            </div>
                            <span
                                class="text-xs px-2 py-0.5 rounded-full font-medium"
                                :style="{
                                    backgroundColor: scoreColorDark(norms[dimId] ?? 0) + '24',
                                    color: scoreColorDark(norms[dimId] ?? 0)
                                }"
                            >{{ scoreLabel(norms[dimId] ?? 0) }}</span>
                        </div>

                        <!-- Barre de progression -->
                        <div class="flex items-center gap-3">
                            <div class="ac-dark-track flex-1">
                                <div
                                    :style="{
                                        width: (norms[dimId] ?? 0) + '%',
                                        backgroundColor: dimInfo.color ?? scoreColorDark(norms[dimId] ?? 0),
                                        transition: 'width 0.8s ease'
                                    }"
                                ></div>
                            </div>
                            <span
                                class="text-sm font-semibold w-10 text-right"
                                :style="{ color: scoreColorDark(norms[dimId] ?? 0) }"
                            >{{ norms[dimId] ?? 0 }}%</span>
                        </div>

                        <!-- Description courte -->
                        <p class="ac-dark-def">
                            {{ dimInfo.description }}
                        </p>
                    </div>
                </div>
            </ResultPanel>

            <!-- Exercice recommandé du jour ───────────────────────────── -->
            <section v-if="recommended" class="pt-card p-8 mb-8" style="border-left: 4px solid var(--pt-gold, #f59e0b)">
                <div class="flex items-start gap-4">
                    <div
                        class="flex-shrink-0 w-10 h-10 rounded-full flex items-center justify-center font-bold" style="color: var(--pt-cream, #fffbf5); background: var(--pt-gold, #f59e0b)"
                    >✦</div>
                    <div class="flex-1">
                        <p class="text-xs uppercase tracking-widest mb-1" style="color: var(--pt-text-muted)">
                            Exercice recommandé du jour
                        </p>
                        <h3 class="text-lg font-semibold mb-1" style="color: var(--pt-navy)">
                            {{ recommended.title }}
                        </h3>
                        <div class="flex items-center gap-3 mb-3">
                            <span class="pt-badge">{{ recommended.duration_minutes }} min</span>
                            <span class="pt-badge">
                                Difficulté {{ recommended.difficulty }}/3
                            </span>
                            <span class="pt-badge">{{ dims[recommended.category]?.label }}</span>
                        </div>
                        <ol class="list-decimal list-inside space-y-1">
                            <li
                                v-for="(step, i) in recommended.instructions"
                                :key="i"
                                class="text-sm"
                                style="color: var(--pt-text-muted)"
                            >{{ step }}</li>
                        </ol>
                        <p class="text-xs italic mt-3 pt-3" style="border-top: 1px solid var(--pt-cream); color: var(--pt-text-muted)">
                            📚 {{ recommended.scientific_basis }}
                        </p>
                    </div>
                </div>
            </section>

            <!-- Citation inspirante ───────────────────────────────────── -->
            <section v-if="quote" class="pt-card p-8 mb-8 text-center">
                <div
                    class="text-5xl font-serif leading-none mb-4"
                    style="color: var(--pt-gold, #f59e0b)"
                >"</div>
                <blockquote
                    class="text-xl font-medium italic mb-4"
                    style="color: var(--pt-navy)"
                >
                    {{ quote.quote }}
                </blockquote>
                <p class="text-sm" style="color: var(--pt-text-muted)">
                    — {{ quote.author }}
                </p>
            </section>

            <!-- ═══════════════════════════════════════════════════════════ -->
            <!-- Parcours 60 jours                                          -->
            <!-- ═══════════════════════════════════════════════════════════ -->
            <section class="pt-card p-8 mb-8" style="border-top: 3px solid var(--pt-gold, #F59E0B)">

                <!-- En-tête parcours -->
                <div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:1rem; margin-bottom:1.5rem">
                    <div>
                        <p style="font-size:0.7rem; text-transform:uppercase; letter-spacing:0.1em; color:var(--pt-text-muted); margin-bottom:0.25rem">
                            Parcours 60 jours
                        </p>
                        <h2 style="font-size:1.25rem; font-weight:600; color:var(--pt-navy); margin:0">
                            Mon chemin d'orateur
                        </h2>
                    </div>
                    <!-- Badges streak + phase -->
                    <div style="display:flex; gap:0.5rem; flex-wrap:wrap; align-items:center">
                        <span
                            v-if="streak > 0"
                            class="pt-badge"
                            style="background:var(--pt-gold, #F59E0B); color:#fff; font-size:0.7rem"
                        >
                            🔥 {{ streak }} jour{{ streak > 1 ? 's' : '' }} de suite
                        </span>
                        <span
                            class="pt-badge"
                            :style="{ background: phaseColor(currentPhase) + '20', color: phaseColor(currentPhase), fontSize: '0.7rem' }"
                        >
                            Phase {{ phaseLabel(currentPhase) }}
                        </span>
                        <span class="pt-badge" style="font-size:0.7rem">
                            Jour {{ Math.min(currentDay, 60) }} / 60
                        </span>
                    </div>
                </div>

                <!-- Barre de progression globale -->
                <div style="margin-bottom:1.5rem">
                    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:0.35rem">
                        <span style="font-size:0.75rem; color:var(--pt-text-muted)">Progression du parcours</span>
                        <span style="font-size:0.75rem; font-weight:600; color:var(--pt-navy)">{{ progressPct }}%</span>
                    </div>
                    <div style="height:6px; border-radius:999px; background:var(--pt-cream, #f8f5ef); overflow:hidden">
                        <div
                            :style="{
                                width: progressPct + '%',
                                height: '100%',
                                borderRadius: '999px',
                                background: phaseColor(currentPhase),
                                transition: 'width 1s ease'
                            }"
                        ></div>
                    </div>
                    <!-- Marqueurs de phase sous la barre -->
                    <div style="display:flex; margin-top:0.4rem; position:relative">
                        <div
                            v-for="(ph, phKey) in journeyPhases"
                            :key="phKey"
                            style="flex:1; text-align:center"
                        >
                            <span
                                style="font-size:0.6rem; text-transform:uppercase; letter-spacing:0.05em"
                                :style="{ color: currentPhase === phKey ? phaseColor(phKey) : 'var(--pt-text-muted)' }"
                            >{{ ph.label }}</span>
                        </div>
                    </div>
                </div>

                <!-- Grille 60 cases (10×6) -->
                <div style="margin-bottom:1.5rem">
                    <div
                        v-for="(rowDays, rowIdx) in gridRows"
                        :key="rowIdx"
                        style="display:grid; grid-template-columns: repeat(10, 1fr); gap:4px; margin-bottom:4px"
                    >
                        <div
                            v-for="day in rowDays"
                            :key="day"
                            :title="`Jour ${day}${journeyGrid[day] ? ' ✓ Complété' : day === currentDay ? ' ← Aujourd\'hui' : ''}`"
                            :style="{
                                aspectRatio: '1',
                                borderRadius: '4px',
                                background: cellColor(day),
                                opacity: cellOpacity(day),
                                border: day === currentDay ? '2px solid var(--pt-gold, #F59E0B)' : '2px solid transparent',
                                cursor: day <= currentDay ? 'pointer' : 'default',
                                transition: 'opacity 0.3s, background 0.3s',
                                display: 'flex',
                                alignItems: 'center',
                                justifyContent: 'center',
                                fontSize: '0.55rem',
                                fontWeight: '700',
                                color: journeyGrid[day] ? '#fff' : day === currentDay ? 'var(--pt-gold)' : 'var(--pt-text-muted)',
                            }"
                        >{{ day }}</div>
                    </div>
                </div>

                <!-- Légende grille -->
                <div style="display:flex; gap:1.2rem; flex-wrap:wrap; margin-bottom:1.75rem">
                    <div style="display:flex; align-items:center; gap:0.35rem">
                        <div style="width:12px; height:12px; border-radius:2px; background:var(--pt-phase-discovery, #6366F1)"></div>
                        <span style="font-size:0.7rem; color:var(--pt-text-muted)">Découverte J1-15</span>
                    </div>
                    <div style="display:flex; align-items:center; gap:0.35rem">
                        <div style="width:12px; height:12px; border-radius:2px; background:var(--pt-phase-install, #8B5CF6)"></div>
                        <span style="font-size:0.7rem; color:var(--pt-text-muted)">Installation J16-30</span>
                    </div>
                    <div style="display:flex; align-items:center; gap:0.35rem">
                        <div style="width:12px; height:12px; border-radius:2px; background:var(--pt-phase-reinforce, #EC4899)"></div>
                        <span style="font-size:0.7rem; color:var(--pt-text-muted)">Renforcement J31-45</span>
                    </div>
                    <div style="display:flex; align-items:center; gap:0.35rem">
                        <div style="width:12px; height:12px; border-radius:2px; background:var(--pt-gold, #F59E0B)"></div>
                        <span style="font-size:0.7rem; color:var(--pt-text-muted)">Maîtrise J46-60</span>
                    </div>
                </div>

                <!-- Exercice du jour -->
                <div
                    v-if="journeyToday"
                    style="border-radius:12px; padding:1.25rem 1.5rem; background:var(--pt-cream, #f8f5ef)"
                >
                    <div style="display:flex; align-items:flex-start; gap:1rem">

                        <!-- Numéro du jour -->
                        <div
                            style="flex-shrink:0; width:44px; height:44px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-weight:700; font-size:1rem; color:#fff"
                            :style="{ background: phaseColor(currentPhase) }"
                        >
                            {{ currentDay }}
                        </div>

                        <div style="flex:1; min-width:0">
                            <p style="font-size:0.7rem; text-transform:uppercase; letter-spacing:0.1em; color:var(--pt-text-muted); margin:0 0 0.25rem">
                                Exercice du jour · {{ journeyToday.duration_minutes }} min
                            </p>
                            <h3 style="font-size:1.05rem; font-weight:600; color:var(--pt-navy); margin:0 0 0.5rem">
                                {{ journeyToday.title }}
                            </h3>

                            <!-- Intention + micro-habitude -->
                            <p style="font-size:0.85rem; font-style:italic; color:var(--pt-navy); margin:0 0 0.75rem; opacity:0.8">
                                "{{ journeyToday.intention }}"
                            </p>

                            <div style="display:flex; flex-wrap:wrap; gap:0.5rem; margin-bottom:0.75rem">
                                <span class="pt-badge">
                                    ⚓ {{ journeyToday.anchor }}
                                </span>
                                <span class="pt-badge">
                                    ✦ {{ journeyToday.weekly_theme }}
                                </span>
                            </div>

                            <!-- Micro-habit + reward -->
                            <div style="display:grid; gap:0.5rem; margin-bottom:1rem">
                                <div style="display:flex; gap:0.5rem; align-items:flex-start">
                                    <span style="font-size:0.85rem; color:var(--pt-navy); opacity:0.6; flex-shrink:0">▸</span>
                                    <span style="font-size:0.85rem; color:var(--pt-navy)">{{ journeyToday.micro_habit }}</span>
                                </div>
                                <div style="display:flex; gap:0.5rem; align-items:flex-start">
                                    <span style="font-size:0.85rem; color:var(--pt-gold, #F59E0B); flex-shrink:0">★</span>
                                    <span style="font-size:0.85rem; color:var(--pt-text-muted)">{{ journeyToday.reward }}</span>
                                </div>
                            </div>

                            <!-- Tip science -->
                            <p
                                v-if="journeyToday.tip_science"
                                style="font-size:0.75rem; font-style:italic; color:var(--pt-text-muted); padding-top:0.75rem; border-top:1px solid rgba(0,0,0,0.07); margin:0"
                            >
                                📚 {{ journeyToday.tip_science }}
                            </p>

                            <!-- CTA -->
                            <div style="margin-top:1rem">
                                <button
                                    class="pt-btn-primary"
                                    :style="{ background: phaseColor(currentPhase), borderColor: phaseColor(currentPhase) }"
                                    style="font-size:0.9rem; padding:0.6rem 1.5rem"
                                >
                                    Commencer l'exercice du jour →
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Fallback si pas de données journey -->
                <div
                    v-else
                    style="text-align:center; padding:2rem; color:var(--pt-text-muted)"
                >
                    <p style="font-size:0.9rem">Ton parcours démarre dès aujourd'hui.</p>
                    <button class="pt-btn-primary" style="margin-top:1rem; font-size:0.9rem">
                        Commencer le Jour 1 →
                    </button>
                </div>

            </section>

            <!-- Synthèse IA ──────────────────────────────────────────── -->
            <div v-if="result?.ai_synthesis" class="mt-6 pt-4 border-t border-amber-200">
                <h3 class="font-semibold mb-2">Synthèse personnalisée</h3>
                <MarkdownText :source="result.ai_synthesis" />
                <p style="margin-top:0.85rem;padding-top:0.7rem;border-top:1px solid rgba(217,119,6,0.25);font-size:11.5px;line-height:1.55;color:#9a8866">
                    <strong style="font-weight:600;color:#57534e">Outil d'auto-évaluation et de développement personnel.</strong>
                    Cette synthèse est générée par IA, à titre informatif. Elle ne constitue pas un avis
                    professionnel et ne remplace pas un psychologue, un médecin ou un coach.
                </p>
            </div>

            <!-- Actions ──────────────────────────────────────────────── -->
            <div class="flex flex-wrap gap-4 justify-center mt-8 mb-12">
                <ResultPdfButton :attempt-id="attempt.id" />
            </div>

        </div>
    </CandidateLayout>
</template>
