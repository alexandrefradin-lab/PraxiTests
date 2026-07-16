import { computed } from 'vue'

// ─── Icônes tabler des parcours quotidiens ──────────────────────────────────
// Carte standard partagée par Le Cap (praxilead), L'Eveilleur (praxivision),
// Le Sanctuaire (praxizenith)… À surcharger via options.icons quand la
// mini-app possède son propre vocabulaire (ex. La Forge de l'Identité).
export const JOURNEY_ICONS = {
    compass: 'ti-compass', target: 'ti-target', ear: 'ti-ear', message: 'ti-message',
    handshake: 'ti-handshake', gift: 'ti-gift', flame: 'ti-flame', shield: 'ti-shield',
    scale: 'ti-scale', users: 'ti-users', clock: 'ti-clock', book: 'ti-book',
    heart: 'ti-heart', rocket: 'ti-rocket', eye: 'ti-eye', seedling: 'ti-plant',
    anchor: 'ti-anchor', map: 'ti-map', lightbulb: 'ti-bulb', sun: 'ti-sun',
}

// Résolution d'une clé d'icône serveur vers une classe tabler.
export const journeyIconFor = (name, icons = JOURNEY_ICONS) => icons[name] ?? 'ti-sparkles'

/**
 * Logique commune des tableaux de bord « un jour = une pratique »
 * (PraxiLeadIndex, PraxiVisionIndex, PraxiZenithIndex, PraxiMiroirIndex…).
 *
 * Attend les props Inertia du contrôleur de parcours quotidien :
 *   { practices|exercises, currentDay, totalDays, completed, streak }
 *
 * options :
 *   - itemsKey : nom de la prop liste ('practices' par défaut, 'exercises'…)
 *   - groupKey : clé de regroupement des jours ('theme' par défaut, 'bloc'…)
 *   - icons    : carte d'icônes spécifique au plugin (JOURNEY_ICONS par défaut)
 *
 * Retourne exactement les computed historiques des pages Index, à
 * destructurer (et renommer au besoin : todayItem → todayPractice…).
 */
export function useJourney(props, options = {}) {
    const itemsKey = options.itemsKey ?? 'practices'
    const groupKey = options.groupKey ?? 'theme'
    const icons    = options.icons ?? JOURNEY_ICONS

    const iconFor = (name) => icons[name] ?? 'ti-sparkles'

    const items = computed(() => props[itemsKey] ?? [])

    // Le jour « du jour » (is_today posé par le contrôleur).
    const todayItem = computed(() => items.value.find(i => i.is_today) ?? null)

    // Progression globale en pourcentage entier.
    const donePercent = computed(() => Math.round((props.completed / props.totalDays) * 100))

    // Bandeau de 7 jours centré sur le jour courant.
    const dayStrip = computed(() => {
        const center = props.currentDay
        const start  = Math.max(1, center - 3)
        const end    = Math.min(props.totalDays, start + 6)
        return items.value.filter(i => i.day >= start && i.day <= end)
    })

    // Les 3 prochains jours verrouillés (aperçu « à venir »).
    const upcomingDays = computed(() =>
        items.value.filter(i => !i.is_today && !i.completed && i.day > props.currentDay).slice(0, 3)
    )

    // Thème / bloc du jour courant.
    const currentBlock = computed(() => todayItem.value?.[groupKey] ?? '')

    // Jours regroupés par thème/bloc, dans l'ordre d'apparition.
    // Chaque groupe expose la clé de regroupement sous son nom d'origine
    // (block.theme ou block.bloc) pour ne rien changer dans les templates.
    const blocks = computed(() => {
        const out = []
        for (const i of items.value) {
            let b = out.find(x => x[groupKey] === i[groupKey])
            if (!b) { b = { [groupKey]: i[groupKey], items: [] }; out.push(b) }
            b.items.push(i)
        }
        return out
    })

    return { iconFor, items, todayItem, donePercent, dayStrip, upcomingDays, currentBlock, blocks }
}

/**
 * Logique commune de la grille de restitution 60 jours des pages Result
 * (PraxiFlowResult, PraxiZenResult, PraxiSpeakResult, PraxiLinkResult…) :
 * phases, couleurs, statut des cases.
 *
 * Chaque plugin garde ses bornes et couleurs HISTORIQUES via `phases` :
 *   { decouverte: { label: 'Découverte', range: [1, 15], color: '#7C3AED' }, … }
 * (ex. PraxiZen coupe à 15/30/45, PraxiFlow à 14/28/42 — ne pas uniformiser
 * sans décision produit.)
 *
 * options :
 *   - phases        : carte des phases (requise)
 *   - currentDay    : Ref/computed du jour courant (1..totalDays)
 *   - completedDays : Ref/computed d'un Set des jours complétés
 *   - totalDays     : durée du parcours (60 par défaut)
 *   - todayColor    : couleur de la case du jour ('var(--pt-gold)' par défaut)
 *   - futureColor   : couleur des cases à venir ('#E5E7EB' par défaut)
 */
export function useJourneyGrid(options) {
    const phases        = options.phases
    const currentDay    = options.currentDay
    const completedDays = options.completedDays
    const totalDays     = options.totalDays ?? 60
    const todayColor    = options.todayColor ?? 'var(--pt-gold)'
    const futureColor   = options.futureColor ?? '#E5E7EB'

    // Jour courant plafonné à la durée du parcours.
    const safeCurrentDay = computed(() => Math.min(currentDay.value, totalDays))

    // Phase d'un jour donné, d'après les bornes du plugin.
    const phaseKeyFor = (day) => {
        for (const [key, p] of Object.entries(phases)) {
            if (day >= p.range[0] && day <= p.range[1]) return key
        }
        return Object.keys(phases).pop()
    }

    const phaseColor = (key) => phases[key]?.color
    const phaseLabel = (key) => phases[key]?.label ?? key

    const currentPhaseKey = computed(() => phaseKeyFor(safeCurrentDay.value))
    const currentPhase    = computed(() => phases[currentPhaseKey.value])

    // Progression dans la phase courante.
    const phaseProgress = computed(() => {
        const [start, end] = currentPhase.value.range
        const total = end - start + 1
        const done  = Math.max(0, safeCurrentDay.value - start)
        return { done, total, pct: Math.round((done / total) * 100) }
    })

    // Parcours terminé (jour courant au-delà de la durée).
    const journeyDone = computed(() => currentDay.value > totalDays)

    // Statut d'une case : done | today | missed | future.
    const dayStatus = (day) => {
        if (completedDays.value.has(day)) return 'done'
        if (day === safeCurrentDay.value) return 'today'
        if (day < safeCurrentDay.value) return 'missed'
        return 'future'
    }

    // Couleur d'une case de la grille (fait → couleur de phase).
    const cellColor = (day) => {
        if (completedDays.value.has(day)) return phases[phaseKeyFor(day)].color
        if (day === safeCurrentDay.value) return todayColor
        return futureColor
    }

    return {
        safeCurrentDay,
        phaseKeyFor,
        phaseColor,
        phaseLabel,
        currentPhaseKey,
        currentPhase,
        phaseProgress,
        journeyDone,
        dayStatus,
        cellColor,
    }
}
