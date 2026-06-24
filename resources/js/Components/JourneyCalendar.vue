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

// Semaines (lignes de 7)
const weeks = computed(() => {
    const rows = []
    for (let i = 0; i < cells.value.length; i += 7) {
        rows.push(cells.value.slice(i, i + 7))
    }
    return rows
})
</script>

<template>
    <div>
        <p style="font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.08em; color: var(--text-secondary); margin-bottom: 0.75rem;">
            {{ label }}
        </p>

        <div style="background: var(--surface, #fff); border: 1px solid var(--border, #e5e7eb); border-radius: var(--r-md, 14px); padding: 1rem 1.1rem;">

            <!-- En-tête jours de la semaine -->
            <div style="display: grid; grid-template-columns: repeat(7, 1fr); gap: 4px; margin-bottom: 6px;">
                <div
                    v-for="(d, di) in ['L','M','M','J','V','S','D']"
                    :key="di"
                    style="text-align: center; font-size: 0.65rem; font-weight: 700; text-transform: uppercase; color: var(--text-secondary); letter-spacing: 0.04em; padding-bottom: 2px;"
                >{{ d }}</div>
            </div>

            <!-- Grille semaines -->
            <div style="display: flex; flex-direction: column; gap: 4px;">
                <div
                    v-for="(week, wi) in weeks"
                    :key="wi"
                    style="display: grid; grid-template-columns: repeat(7, 1fr); gap: 4px;"
                >
                    <div
                        v-for="cell in week"
                        :key="cell.day"
                        :title="`Jour ${cell.day}`"
                        :style="{
                            position: 'relative',
                            aspectRatio: '1',
                            borderRadius: '6px',
                            display: 'flex',
                            alignItems: 'center',
                            justifyContent: 'center',
                            fontSize: '0.7rem',
                            fontFamily: 'var(--font-display)',
                            fontWeight: cell.is_today ? 800 : 600,
                            cursor: 'default',
                            border: cell.is_today
                                ? '2px solid var(--primary, #A67520)'
                                : '1px solid transparent',
                            background: cell.completed
                                ? '#10B981'
                                : cell.is_today
                                    ? 'rgba(166,117,32,.08)'
                                    : cell.unlocked
                                        ? 'rgba(166,117,32,.12)'
                                        : 'var(--border, #f1f1f0)',
                            color: cell.completed
                                ? '#fff'
                                : cell.is_today
                                    ? 'var(--primary, #A67520)'
                                    : cell.unlocked
                                        ? 'var(--text-primary)'
                                        : 'var(--text-secondary)',
                            opacity: cell.unlocked || cell.completed ? 1 : 0.45,
                            transition: 'background .25s, transform .2s',
                            transform: cell.is_today ? 'scale(1.08)' : 'scale(1)',
                        }"
                    >
                        <!-- Coche si complété -->
                        <span v-if="cell.completed" style="font-size: 0.75rem; line-height: 1;">✓</span>
                        <!-- Numéro sinon -->
                        <span v-else style="line-height: 1;">{{ cell.day }}</span>

                        <!-- Point « aujourd'hui » sous le numéro -->
                        <span
                            v-if="cell.is_today && !cell.completed"
                            style="position: absolute; bottom: 2px; left: 50%; transform: translateX(-50%); width: 4px; height: 4px; border-radius: 50%; background: var(--primary, #A67520);"
                        ></span>
                    </div>
                </div>
            </div>

            <!-- Légende -->
            <div style="display: flex; gap: 1.2rem; margin-top: 0.9rem; flex-wrap: wrap;">
                <span style="display: flex; align-items: center; gap: 5px; font-size: 0.72rem; color: var(--text-secondary);">
                    <span style="width: 12px; height: 12px; border-radius: 3px; background: #10B981; display: inline-block;"></span>
                    Fait
                </span>
                <span style="display: flex; align-items: center; gap: 5px; font-size: 0.72rem; color: var(--text-secondary);">
                    <span style="width: 12px; height: 12px; border-radius: 3px; background: rgba(166,117,32,.12); border: 2px solid var(--primary, #A67520); display: inline-block;"></span>
                    Aujourd'hui
                </span>
                <span style="display: flex; align-items: center; gap: 5px; font-size: 0.72rem; color: var(--text-secondary);">
                    <span style="width: 12px; height: 12px; border-radius: 3px; background: var(--border, #f1f1f0); display: inline-block;"></span>
                    À venir
                </span>
            </div>
        </div>
    </div>
</template>
