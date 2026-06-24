<script setup>
/**
 * JourneyCalendar — Grille visuelle 60 jours pour les mini-apps à parcours journalier.
 * Chaque case se coche automatiquement quand l'action du jour est accomplie.
 *
 * Props :
 *   items      — tableau {day, completed, unlocked, is_today} (ex: practices ou exercises)
 *   totalDays  — durée du parcours (défaut 60)
 *   label      — libellé optionnel au-dessus (ex: "Calendrier du parcours")
 */
import { computed } from 'vue'

const props = defineProps({
    items:     { type: Array,  default: () => [] },
    totalDays: { type: Number, default: 60 },
    label:     { type: String, default: 'Calendrier du parcours' },
})

// Indexation rapide par numéro de jour
const byDay = computed(() => {
    const m = {}
    for (const item of props.items) m[item.day] = item
    return m
})

// Grille de totalDays cases
const cells = computed(() =>
    Array.from({ length: props.totalDays }, (_, i) => {
        const day = i + 1
        const item = byDay.value[day] ?? null
        return {
            day,
            completed: item?.completed ?? false,
            is_today:  item?.is_today  ?? false,
            unlocked:  item?.unlocked  ?? false,
        }
    })
)

// Lignes de 10
const weeks = computed(() => {
    const rows = []
    for (let i = 0; i < cells.value.length; i += 10) {
        rows.push(cells.value.slice(i, i + 10))
    }
    return rows
})
</script>

<template>
    <div>
        <p style="font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.08em; color: var(--text-secondary); margin-bottom: 0.75rem;">
            {{ label }}
        </p>

        <div style="background: var(--bg-elevated); border: 1px solid var(--glass-border); border-radius: 10px; padding: 0.9rem 1rem;">

            <!-- Grille 10 colonnes × N lignes -->
            <div style="display: flex; flex-direction: column; gap: 3px;">
                <div
                    v-for="(week, wi) in weeks"
                    :key="wi"
                    style="display: grid; grid-template-columns: repeat(10, 1fr); gap: 3px;"
                >
                    <div
                        v-for="cell in week"
                        :key="cell.day"
                        :title="`Jour ${cell.day}`"
                        :style="{
                            height: '28px',
                            borderRadius: '4px',
                            display: 'flex',
                            alignItems: 'center',
                            justifyContent: 'center',
                            fontSize: '0.6rem',
                            fontFamily: 'var(--font-data)',
                            fontWeight: cell.is_today ? 700 : 500,
                            cursor: 'default',
                            border: cell.is_today ? '1.5px solid var(--color-primary)' : 'none',
                            background: cell.completed
                                ? 'var(--color-primary)'
                                : cell.is_today
                                    ? 'rgba(166,117,32,.12)'
                                    : cell.unlocked
                                        ? 'rgba(166,117,32,.1)'
                                        : 'rgba(166,117,32,.04)',
                            color: cell.completed
                                ? '#F0E8D4'
                                : cell.is_today
                                    ? 'var(--color-primary)'
                                    : cell.unlocked
                                        ? 'var(--text-secondary)'
                                        : 'var(--text-muted)',
                            opacity: cell.unlocked || cell.completed || cell.is_today ? 1 : 0.55,
                        }"
                    >
                        <span v-if="cell.completed" style="font-size: 0.65rem; line-height: 1;">✓</span>
                        <span v-else style="line-height: 1;">{{ cell.day }}</span>
                    </div>
                </div>
            </div>

            <!-- Légende -->
            <div style="display: flex; gap: 1rem; margin-top: 0.65rem; flex-wrap: wrap;">
                <span style="display: flex; align-items: center; gap: 4px; font-size: 0.68rem; color: var(--text-muted);">
                    <span style="width: 10px; height: 10px; border-radius: 2px; background: var(--color-primary); display: inline-block;"></span>
                    Fait
                </span>
                <span style="display: flex; align-items: center; gap: 4px; font-size: 0.68rem; color: var(--text-muted);">
                    <span style="width: 10px; height: 10px; border-radius: 2px; background: rgba(166,117,32,.12); border: 1.5px solid var(--color-primary); display: inline-block;"></span>
                    Aujourd'hui
                </span>
                <span style="display: flex; align-items: center; gap: 4px; font-size: 0.68rem; color: var(--text-muted);">
                    <span style="width: 10px; height: 10px; border-radius: 2px; background: rgba(166,117,32,.04); display: inline-block;"></span>
                    À venir
                </span>
            </div>
        </div>
    </div>
</template>
