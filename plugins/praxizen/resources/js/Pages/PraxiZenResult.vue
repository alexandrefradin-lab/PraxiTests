<script setup>
import { computed, ref } from 'vue'
import CandidateLayout from '@/Layouts/CandidateLayout.vue'
import ScoreGauge from '@/Components/ScoreGauge.vue'
import RadarChart from '@/Components/RadarChart.vue'
import SynthesisCard from '@/Components/SynthesisCard.vue'

const props = defineProps({
    attempt:         Object,
    result:          Object,
    journeyProgress: { type: Array,  default: () => [] }, // [{ day, completed_at, felt_score }]
    journeyDays:     { type: Array,  default: () => [] }, // Journey::all() — les 60 entrées
    currentDay:      { type: Number, default: 1 },
    currentStreak:   { type: Number, default: 0 },
})

const scoring      = computed(() => props.result?.scoring ?? {})
const dimensions   = computed(() => scoring.value.dimensions ?? {})
const meta         = computed(() => scoring.value.meta ?? {})
const globalScore  = computed(() => scoring.value.global_score ?? 0)
const stressLevel  = computed(() => scoring.value.stress_level ?? 0)
const wellnessLabel= computed(() => scoring.value.wellness_label ?? '')
const recommended  = computed(() => scoring.value.recommended ?? [])
const weakDims     = computed(() => scoring.value.weak_dimensions ?? [])
const strongDims   = computed(() => scoring.value.strong_dimensions ?? [])

// Axes de la toile d'araignée — dimensions normalisées 0..100, ordre du meta conservé
const radarAxes = computed(() =>
    Object.entries(dimensions.value).map(([key, value]) => {
        const axis = { label: meta.value[key]?.label ?? key, value: value ?? 0 }
        if (meta.value[key]?.color) axis.color = meta.value[key].color
        return axis
    })
)

// Exercice du jour — le premier recommandé
const exerciceduJour = computed(() => recommended.value[0] ?? null)

// Contrôle des accordéons exercices
const openExercise = ref(null)
const toggleExercise = (id) => {
    openExercise.value = openExercise.value === id ? null : id
}

// Couleur de la jauge globale selon le score — teintes patinées (palette AC)
const gaugeColor = computed(() => {
    if (globalScore.value >= 80) return '#3A6B48'   // Vert Eagle Vision
    if (globalScore.value >= 65) return '#0A7FA0'   // Bleu Animus
    if (globalScore.value >= 50) return '#A67520'   // Or de la Fraternité
    if (globalScore.value >= 35) return '#B5781C'   // Or brûlé / ambre
    return '#B03020'                                // Rouge sang
})

const categoryLabel = (cat) => {
    const map = {
        respiration:  'Respiration',
        mindfulness:  'Pleine conscience',
        cognitif:     'Cognitif',
        corporel:     'Corporel',
    }
    return map[cat] ?? cat
}

const categoryIcon = (cat) => {
    const map = {
        respiration:  '💨',
        mindfulness:  '🧘',
        cognitif:     '💡',
        corporel:     '💪',
    }
    return map[cat] ?? '⚡'
}

const difficultyLabel = (d) => {
    if (d === 1) return 'Débutant'
    if (d === 2) return 'Intermédiaire'
    return 'Avancé'
}

const difficultyColor = (d) => {
    if (d === 1) return 'var(--pt-success, #059669)'
    if (d === 2) return 'var(--pt-gold)'
    return '#EA580C'
}

// ─── Parcours 60 jours ────────────────────────────────────────────────

// Set des jours déjà complétés
const completedDays = computed(() =>
    new Set(props.journeyProgress.map(p => p.day))
)

// Jour actuel (1-60), plafonné à 60
const safeCurrentDay = computed(() => Math.min(props.currentDay, 60))

// Données du jour actuel depuis journeyDays
const todayEntry = computed(() =>
    props.journeyDays.find(d => d.day === safeCurrentDay.value) ?? null
)

// Phase actuelle
const PHASES = {
    decouverte:   { label: 'Découverte',   range: [1,  15], color: '#7C3AED' },
    installation: { label: 'Installation', range: [16, 30], color: '#0284C7' },
    renforcement: { label: 'Renforcement', range: [31, 45], color: '#059669' },
    maitrise:     { label: 'Maîtrise',     range: [46, 60], color: '#EA580C' },
}

const currentPhaseKey = computed(() => {
    const d = safeCurrentDay.value
    if (d <= 15)  return 'decouverte'
    if (d <= 30)  return 'installation'
    if (d <= 45)  return 'renforcement'
    return 'maitrise'
})

const currentPhase = computed(() => PHASES[currentPhaseKey.value])

// Progression dans la phase courante
const phaseProgress = computed(() => {
    const [start, end] = currentPhase.value.range
    const total   = end - start + 1
    const done    = Math.max(0, safeCurrentDay.value - start)
    return { done, total, pct: Math.round((done / total) * 100) }
})

// Parcours terminé
const journeyDone = computed(() => props.currentDay > 60)

// Couleur d'une case de la grille
const cellColor = (day) => {
    if (completedDays.value.has(day)) {
        // couleur de la phase
        if (day <= 15)  return '#7C3AED'
        if (day <= 30)  return '#0284C7'
        if (day <= 45)  return '#059669'
        return '#EA580C'
    }
    if (day === safeCurrentDay.value) return 'var(--pt-gold)'
    return '#E5E7EB'
}

// Libellé de la phase pour affichage
const phaseLabel = (key) => PHASES[key]?.label ?? key
</script>

<template>
    <CandidateLayout>
        <Head title="PraxiZen — Résultats gestion du stress" />

        <div class="max-w-4xl mx-auto px-4 py-8">

            <!-- ── En-tête ──────────────────────────────────────────────── -->
            <div class="text-center mb-10">
                <span class="pt-badge">Gestion du stress · PraxiZen</span>
                <h1 class="text-4xl font-semibold tracking-tight mt-4" style="color: var(--pt-navy)">
                    Ton bilan bien-être
                </h1>
                <p class="mt-3 text-base" style="color: var(--pt-text-muted)">
                    Voici une carte complète de tes ressources face au stress professionnel.
                </p>
            </div>

            <!-- ── Jauge circulaire + score global ─────────────────────── -->
            <div class="pt-card p-8 mb-8 flex flex-col md:flex-row items-center gap-10">

                <!-- Jauge de score partagée -->
                <div class="relative flex-shrink-0">
                    <ScoreGauge :score="globalScore" :color="gaugeColor" />
                </div>

                <!-- Texte synthèse -->
                <div class="flex-1 text-center md:text-left">
                    <div class="text-2xl font-bold mb-2" :style="{ color: gaugeColor }">
                        {{ wellnessLabel }}
                    </div>
                    <p class="text-base mb-4" style="color: var(--pt-text-muted)">
                        Ton score de bien-être est de <strong>{{ globalScore }}/100</strong>
                        et ton niveau de stress estimé de <strong>{{ stressLevel }}/100</strong>.
                    </p>

                    <!-- Points forts / axes de travail -->
                    <div class="grid grid-cols-2 gap-3">
                        <div v-if="strongDims.length" class="rounded-xl p-3" style="background: var(--pt-cream)">
                            <p class="text-xs font-semibold uppercase tracking-wide mb-1" style="color: var(--pt-text-muted)">Tes points forts</p>
                            <p v-for="dim in strongDims" :key="dim" class="text-sm font-medium" style="color: var(--pt-navy)">
                                {{ meta[dim]?.icon ?? '✓' }} {{ meta[dim]?.label ?? dim }}
                            </p>
                        </div>
                        <div v-if="weakDims.length" class="rounded-xl p-3" style="background: #FFF7ED">
                            <p class="text-xs font-semibold uppercase tracking-wide mb-1" style="color: var(--pt-text-muted)">À renforcer</p>
                            <p v-for="dim in weakDims" :key="dim" class="text-sm font-medium" style="color: #EA580C">
                                {{ meta[dim]?.icon ?? '→' }} {{ meta[dim]?.label ?? dim }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ── Profil en un coup d'œil — toile d'araignée ───────────── -->
            <section v-if="radarAxes.length >= 3" class="pt-card p-8 mb-8">
                <h2 class="text-xl font-semibold mb-1" style="color: var(--pt-navy)">Ton profil en un coup d'œil</h2>
                <p class="text-sm mb-6" style="color: var(--pt-text-muted)">
                    Tes 5 dimensions de bien-être, d'un seul regard.
                </p>
                <div class="flex justify-center">
                    <RadarChart :axes="radarAxes" />
                </div>
            </section>

            <!-- ── 5 jauges par dimension ───────────────────────────────── -->
            <section class="pt-card p-8 mb-8">
                <h2 class="text-xl font-semibold mb-6" style="color: var(--pt-navy)">Tes 5 dimensions</h2>
                <div class="space-y-6">
                    <div v-for="(score, key) in dimensions" :key="key">
                        <div class="flex items-start justify-between gap-4 mb-2">
                            <div class="flex-1">
                                <div class="flex items-center gap-2">
                                    <span class="text-lg">{{ meta[key]?.icon ?? '' }}</span>
                                    <span class="font-semibold" :style="{ color: meta[key]?.color ?? 'var(--pt-navy)' }">
                                        {{ meta[key]?.label ?? key }}
                                    </span>
                                    <!-- Badge point faible -->
                                    <span v-if="weakDims.includes(key)"
                                          class="text-xs px-2 py-0.5 rounded-full font-medium"
                                          style="background: #FFF7ED; color: #EA580C">
                                        À renforcer
                                    </span>
                                </div>
                                <p class="text-xs mt-1" style="color: var(--pt-text-muted)">
                                    {{ meta[key]?.description ?? '' }}
                                </p>
                            </div>
                            <span class="text-lg font-bold flex-shrink-0" :style="{ color: meta[key]?.color ?? 'var(--pt-navy)' }">
                                {{ score }}%
                            </span>
                        </div>

                        <!-- Barre de progression animée -->
                        <div class="w-full rounded-full h-3" style="background: #E5E7EB">
                            <div
                                class="h-3 rounded-full"
                                :style="{
                                    width: score + '%',
                                    backgroundColor: meta[key]?.color ?? 'var(--pt-navy)',
                                    transition: 'width 1s ease',
                                }"
                            ></div>
                        </div>

                        <!-- Conseil si dimension faible -->
                        <p v-if="weakDims.includes(key)" class="text-xs mt-2 italic" style="color: var(--pt-text-muted)">
                            {{ meta[key]?.low_advice ?? '' }}
                        </p>
                    </div>
                </div>
            </section>

            <!-- ── Exercice du jour ─────────────────────────────────────── -->
            <section v-if="exerciceduJour" class="mb-8">
                <div class="pt-card p-8 border-l-4" :style="{ borderLeftColor: 'var(--pt-gold)' }">
                    <div class="flex items-center gap-3 mb-4">
                        <span class="text-2xl">{{ categoryIcon(exerciceduJour.category) }}</span>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide" style="color: var(--pt-gold)">
                                Exercice recommandé du jour
                            </p>
                            <h2 class="text-xl font-bold" style="color: var(--pt-navy)">
                                {{ exerciceduJour.title }}
                            </h2>
                        </div>
                        <div class="ml-auto flex items-center gap-3">
                            <span class="text-sm px-3 py-1 rounded-full font-medium"
                                  style="background: var(--pt-cream); color: var(--pt-text-muted)">
                                ⏱ {{ exerciceduJour.duration_minutes }} min
                            </span>
                            <span class="text-xs px-2 py-1 rounded-full font-medium"
                                  :style="{ backgroundColor: '#F3F4F6', color: difficultyColor(exerciceduJour.difficulty) }">
                                {{ difficultyLabel(exerciceduJour.difficulty) }}
                            </span>
                        </div>
                    </div>

                    <p class="text-xs mb-4 italic" style="color: var(--pt-text-muted)">
                        📚 {{ exerciceduJour.scientific_basis }}
                    </p>

                    <!-- Instructions étape par étape -->
                    <ol class="space-y-3">
                        <li
                            v-for="(step, i) in exerciceduJour.instructions"
                            :key="i"
                            class="flex gap-3 items-start"
                        >
                            <span
                                class="flex-shrink-0 w-7 h-7 rounded-full flex items-center justify-center text-sm font-bold text-white"
                                :style="{ backgroundColor: 'var(--pt-gold)' }"
                            >{{ i + 1 }}</span>
                            <p class="text-sm leading-relaxed pt-1" style="color: var(--pt-navy)">{{ step }}</p>
                        </li>
                    </ol>
                </div>
            </section>

            <!-- ── Programme personnalisé (3 exercices) ────────────────── -->
            <section v-if="recommended.length > 1" class="mb-8">
                <h2 class="text-xl font-semibold mb-4" style="color: var(--pt-navy)">Ton programme personnalisé</h2>
                <p class="text-sm mb-6" style="color: var(--pt-text-muted)">
                    Ces exercices ont été sélectionnés pour renforcer tes dimensions les plus fragiles.
                </p>

                <div class="space-y-4">
                    <div
                        v-for="(ex, idx) in recommended.slice(1)"
                        :key="ex.id"
                        class="pt-card overflow-hidden"
                    >
                        <!-- En-tête accordéon -->
                        <button
                            class="w-full flex items-center gap-4 p-6 text-left transition-colors"
                            style="background: transparent"
                            @mouseover="$event.currentTarget.style.background='var(--pt-cream)'"
                            @mouseleave="$event.currentTarget.style.background='transparent'"
                            @click="toggleExercise(ex.id)"
                            :aria-expanded="openExercise === ex.id"
                        >
                            <span class="text-xl flex-shrink-0">{{ categoryIcon(ex.category) }}</span>
                            <div class="flex-1">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span class="font-semibold" style="color: var(--pt-navy)">{{ ex.title }}</span>
                                    <span
                                        class="text-xs px-2 py-0.5 rounded-full"
                                        style="background: var(--pt-cream); color: var(--pt-text-muted)"
                                    >{{ categoryLabel(ex.category) }}</span>
                                </div>
                                <p class="text-xs mt-1" style="color: var(--pt-text-muted)">
                                    ⏱ {{ ex.duration_minutes }} min &nbsp;·&nbsp;
                                    <span :style="{ color: difficultyColor(ex.difficulty) }">
                                        {{ difficultyLabel(ex.difficulty) }}
                                    </span>
                                </p>
                            </div>
                            <svg
                                class="flex-shrink-0 transition-transform"
                                :class="{ 'rotate-180': openExercise === ex.id }"
                                width="20" height="20" viewBox="0 0 20 20" fill="none"
                                stroke="currentColor" stroke-width="2"
                                style="color: var(--pt-text-muted)"
                            >
                                <path d="M5 7.5l5 5 5-5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </button>

                        <!-- Corps accordéon -->
                        <div v-if="openExercise === ex.id" class="px-6 pb-6">
                            <p class="text-xs italic mb-4" style="color: var(--pt-text-muted)">
                                📚 {{ ex.scientific_basis }}
                            </p>
                            <ol class="space-y-3">
                                <li v-for="(step, i) in ex.instructions" :key="i" class="flex gap-3 items-start">
                                    <span
                                        class="flex-shrink-0 w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold text-white"
                                        style="background: var(--pt-navy)"
                                    >{{ i + 1 }}</span>
                                    <p class="text-sm leading-relaxed pt-0.5" style="color: var(--pt-navy)">{{ step }}</p>
                                </li>
                            </ol>
                        </div>
                    </div>
                </div>
            </section>

            <!-- ── Badge de progression ────────────────────────────────── -->
            <section class="pt-card p-8 mb-8">
                <div class="flex items-center gap-6">
                    <!-- Badge SVG -->
                    <div class="flex-shrink-0">
                        <svg viewBox="0 0 80 80" width="80" height="80" aria-label="Badge">
                            <circle cx="40" cy="40" r="38" :fill="gaugeColor" opacity="0.15"/>
                            <circle cx="40" cy="40" r="30" :fill="gaugeColor" opacity="0.25"/>
                            <text x="40" y="46" text-anchor="middle" font-size="26">
                                {{ globalScore >= 65 ? '🌟' : globalScore >= 50 ? '🌱' : '🔥' }}
                            </text>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <h3 class="font-bold text-lg mb-1" style="color: var(--pt-navy)">
                            <template v-if="globalScore >= 80">Badge Maître Zen débloqué</template>
                            <template v-else-if="globalScore >= 65">Badge Explorateur Zen débloqué</template>
                            <template v-else-if="globalScore >= 50">Badge Apprenti Zen débloqué</template>
                            <template v-else>Badge Démarrage débloqué — Continue !</template>
                        </h3>
                        <p class="text-sm" style="color: var(--pt-text-muted)">
                            <template v-if="globalScore >= 80">
                                Tu maîtrises les ressources face au stress professionnel. Continue à entretenir ces pratiques quotidiennes.
                            </template>
                            <template v-else-if="globalScore >= 65">
                                Tu as de bonnes bases. Pratique les exercices recommandés 3 à 5 fois par semaine pour consolider ton équilibre.
                            </template>
                            <template v-else-if="globalScore >= 50">
                                Tu as identifié tes axes de progrès. Engage-toi sur 21 jours avec ton programme personnalisé.
                            </template>
                            <template v-else>
                                Tu viens de faire le premier pas, le plus important. Commence par l'exercice du jour, 5 minutes suffisent.
                            </template>
                        </p>
                    </div>
                </div>
            </section>

            <!-- ── Synthèse IA si disponible ───────────────────────────── -->
            <SynthesisCard :source="attempt.result?.ai_synthesis" title="Analyse personnalisée" />

            <!-- ── Parcours 60 jours ───────────────────────────────────── -->
            <section class="pt-card pt-8 pb-8 pl-8 pr-8 mb-8">

                <!-- En-tête section -->
                <div class="flex items-center justify-between flex-wrap gap-4 mb-6">
                    <div>
                        <h2 class="text-xl font-semibold" style="color: var(--pt-navy)">
                            Mon parcours 60 jours
                        </h2>
                        <p class="text-sm mt-1" style="color: var(--pt-text-muted)">
                            Science des habitudes · Phillippa Lally (UCL, 2010) · BJ Fogg · James Clear
                        </p>
                    </div>

                    <!-- Streak -->
                    <div v-if="currentStreak > 0"
                         class="flex items-center gap-2 rounded-xl pt-3 pb-3 pl-4 pr-4"
                         style="background: #FFF7ED">
                        <span class="text-xl">🔥</span>
                        <span class="font-bold text-base" style="color: #EA580C">
                            {{ currentStreak }} jour{{ currentStreak > 1 ? 's' : '' }} consécutif{{ currentStreak > 1 ? 's' : '' }}
                        </span>
                    </div>
                </div>

                <!-- Phase + barre de progression -->
                <div class="rounded-xl pt-4 pb-4 pl-5 pr-5 mb-6"
                     :style="{ background: currentPhase.color + '15', border: '1px solid ' + currentPhase.color + '40' }">
                    <div class="flex items-center justify-between mb-2 flex-wrap gap-2">
                        <span class="font-semibold text-sm"
                              :style="{ color: currentPhase.color }">
                            Phase {{ Object.keys(PHASES).indexOf(currentPhaseKey) + 1 }} — {{ currentPhase.label }}
                        </span>
                        <span class="text-sm" style="color: var(--pt-text-muted)">
                            Jour {{ phaseProgress.done }}/{{ phaseProgress.total }}
                            ({{ phaseProgress.pct }} %)
                        </span>
                    </div>
                    <div class="w-full rounded-full h-2" style="background: #E5E7EB">
                        <div
                            class="h-2 rounded-full"
                            :style="{
                                width: phaseProgress.pct + '%',
                                backgroundColor: currentPhase.color,
                                transition: 'width 0.8s ease',
                            }"
                        ></div>
                    </div>
                </div>

                <!-- Grille 60 cases — 10 colonnes × 6 lignes -->
                <div
                    class="mb-6"
                    style="
                        display: grid;
                        grid-template-columns: repeat(10, 1fr);
                        gap: 6px;
                    "
                    role="list"
                    aria-label="Grille de progression 60 jours"
                >
                    <div
                        v-for="day in 60"
                        :key="day"
                        role="listitem"
                        :aria-label="'Jour ' + day + (completedDays.has(day) ? ' — complété' : (day === safeCurrentDay ? ' — aujourd\'hui' : ''))"
                        :title="'Jour ' + day"
                        class="rounded-lg flex items-center justify-center text-xs font-semibold select-none"
                        :style="{
                            aspectRatio: '1',
                            backgroundColor: cellColor(day),
                            color: (completedDays.has(day) || day === safeCurrentDay) ? 'white' : '#9CA3AF',
                            boxShadow: day === safeCurrentDay ? '0 0 0 2px white, 0 0 0 4px ' + currentPhase.color : 'none',
                            animation: day === safeCurrentDay ? 'zen-pulse 2s ease-in-out infinite' : 'none',
                            transition: 'background-color 0.3s ease',
                        }"
                    >
                        {{ completedDays.has(day) ? '✓' : day }}
                    </div>
                </div>

                <!-- Légende phases -->
                <div class="flex flex-wrap gap-3 mb-6">
                    <div v-for="(phase, key) in PHASES" :key="key"
                         class="flex items-center gap-1.5 text-xs"
                         style="color: var(--pt-text-muted)">
                        <span class="inline-block w-3 h-3 rounded-sm flex-shrink-0"
                              :style="{ backgroundColor: phase.color }"></span>
                        J{{ phase.range[0] }}-{{ phase.range[1] }} {{ phase.label }}
                    </div>
                    <div class="flex items-center gap-1.5 text-xs" style="color: var(--pt-text-muted)">
                        <span class="inline-block w-3 h-3 rounded-sm flex-shrink-0"
                              style="background: var(--pt-gold)"></span>
                        Aujourd'hui
                    </div>
                </div>

                <!-- Exercice du jour (depuis le parcours) -->
                <div v-if="todayEntry && !journeyDone"
                     class="rounded-xl pt-5 pb-5 pl-6 pr-6"
                     style="border: 1px solid var(--pt-cream); background: var(--pt-cream)">

                    <div class="flex items-start justify-between gap-4 flex-wrap mb-3">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide mb-1"
                               :style="{ color: currentPhase.color }">
                                Jour {{ safeCurrentDay }} · {{ todayEntry.weekly_theme }}
                            </p>
                            <h3 class="text-lg font-bold" style="color: var(--pt-navy)">
                                {{ todayEntry.title }}
                            </h3>
                        </div>
                        <span class="text-sm rounded-full pt-1.5 pb-1.5 pl-3 pr-3 font-medium flex-shrink-0"
                              style="background: white; color: var(--pt-text-muted)">
                            ⏱ {{ todayEntry.duration_minutes }} min
                        </span>
                    </div>

                    <!-- Ancrage et intention -->
                    <div class="grid gap-2 mb-4"
                         style="grid-template-columns: repeat(auto-fit, minmax(220px, 1fr))">
                        <div class="rounded-lg pt-3 pb-3 pl-4 pr-4" style="background: white">
                            <p class="text-xs font-semibold mb-1" style="color: var(--pt-text-muted)">
                                ⚓ Ancrage (BJ Fogg)
                            </p>
                            <p class="text-sm" style="color: var(--pt-navy)">{{ todayEntry.anchor }}</p>
                        </div>
                        <div class="rounded-lg pt-3 pb-3 pl-4 pr-4" style="background: white">
                            <p class="text-xs font-semibold mb-1" style="color: var(--pt-text-muted)">
                                💬 Intention du jour
                            </p>
                            <p class="text-sm italic" style="color: var(--pt-navy)">{{ todayEntry.intention }}</p>
                        </div>
                    </div>

                    <!-- Micro-habitude et récompense -->
                    <div class="grid gap-2 mb-4"
                         style="grid-template-columns: repeat(auto-fit, minmax(220px, 1fr))">
                        <div class="rounded-lg pt-3 pb-3 pl-4 pr-4" style="background: white">
                            <p class="text-xs font-semibold mb-1" style="color: var(--pt-text-muted)">
                                🌱 Micro-habitude (Tiny Habits)
                            </p>
                            <p class="text-sm" style="color: var(--pt-navy)">{{ todayEntry.micro_habit }}</p>
                        </div>
                        <div class="rounded-lg pt-3 pb-3 pl-4 pr-4" style="background: white">
                            <p class="text-xs font-semibold mb-1" style="color: var(--pt-text-muted)">
                                🏅 Récompense
                            </p>
                            <p class="text-sm" style="color: var(--pt-navy)">{{ todayEntry.reward }}</p>
                        </div>
                    </div>

                    <!-- Science tip -->
                    <p class="text-xs italic mb-4" style="color: var(--pt-text-muted)">
                        📚 {{ todayEntry.tip_science }}
                    </p>

                    <!-- Bouton CTA -->
                    <button
                        type="button"
                        class="pt-btn-primary w-full"
                        :style="{ '--btn-bg': currentPhase.color }"
                        style="
                            display: block;
                            width: 100%;
                            text-align: center;
                            padding: 12px;
                            border-radius: 10px;
                            font-weight: 600;
                            font-size: 15px;
                            color: white;
                            cursor: pointer;
                            border: none;
                            background-color: var(--pt-navy);
                            transition: opacity 0.2s;
                        "
                        @mouseover="$event.currentTarget.style.opacity='0.85'"
                        @mouseleave="$event.currentTarget.style.opacity='1'"
                    >
                        Commencer l'exercice du Jour {{ safeCurrentDay }}
                        ({{ todayEntry.duration_minutes }} min)
                    </button>
                </div>

                <!-- Message parcours terminé -->
                <div v-else-if="journeyDone"
                     class="rounded-xl pt-6 pb-6 pl-6 pr-6 text-center"
                     style="background: linear-gradient(135deg, #7C3AED15, #EA580C15); border: 1px solid #EA580C40">
                    <p class="text-3xl mb-2">🏆</p>
                    <h3 class="text-xl font-bold mb-2" style="color: var(--pt-navy)">
                        Parcours 60 jours terminé !
                    </h3>
                    <p class="text-sm" style="color: var(--pt-text-muted)">
                        Vous avez ancré une habitude durable de gestion du stress.
                        À J60, la probabilité de maintien à 1 an dépasse 80 % (Lally, UCL, 2010).
                    </p>
                </div>

            </section>

            <!-- ── Actions ─────────────────────────────────────────────── -->
            <div class="flex flex-wrap gap-4 justify-center mt-10">
                <a :href="route('results.pdf', attempt.id)" class="pt-btn-primary">
                    Télécharger mon bilan PDF
                </a>
            </div>

        </div>
    </CandidateLayout>
</template>

<style scoped>
@keyframes zen-pulse {
    0%, 100% { box-shadow: 0 0 0 2px white, 0 0 0 4px var(--pt-gold, #F59E0B); }
    50%       { box-shadow: 0 0 0 2px white, 0 0 0 7px var(--pt-gold, #F59E0B); }
}
</style>
