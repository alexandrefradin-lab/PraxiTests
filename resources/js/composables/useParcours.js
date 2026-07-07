import { computed, watch } from 'vue'
import { usePage, router } from '@inertiajs/vue3'

// ─── Parcours visuels ────────────────────────────────────────────────────────
// 'medieval'  : univers Parchemin/Or — quête, épreuves, grimoire, éclats.
// 'corporate' : cabinet de conseil — évaluations, synthèse, indicateurs.
// Le thème CSS est porté par data-theme sur <html> (posé côté serveur dans
// app.blade.php, mis à jour ici à chaud). Les libellés ci-dessous couvrent
// le chrome commun (layout candidat + en-têtes des pages principales).

const LABELS = {
    medieval: {
        tagline:           'Voyage intérieur',
        navTests:          "L'Armurerie",
        navGrimoire:       'Le Grimoire',
        navHistory:        'Chroniques',
        navTreasure:       'Le Trésor',
        iconTests:         'ti-sword',
        iconGrimoire:      'ti-book-2',
        iconHistory:       'ti-scroll',
        iconTreasure:      'ti-stars',
        logout:            'Quitter la Quête',
        password:          'Changer mon sceau secret',
        xpUnit:            '✦',
        xpName:            'Éclats',
        achievementKicker: 'Épreuve déverrouillée',
        titleTests:        "L'Armurerie",
        subtitleTests:     "Chaque Épreuve est une étape de ta cartographie intérieure. Passe-les dans l'ordre qui te convient — tes résultats s'accumulent dans ton Grimoire pour forger une synthèse IA et dévoiler tes voies d'avenir. À tout moment, l'Oracle (en bas à droite) est là pour répondre à tes questions et t'orienter.",
        countTests:        'Épreuves disponibles',
        titleGrimoire:     'Le Grimoire',
        titleHistory:      'Chroniques du Héros',
        subtitleHistory:   'Toutes tes expéditions — accomplies et en cours.',
        titleTreasure:     'La Salle du Trésor',
        subtitleTreasure:  "Tes Éclats ouvrent des modules d'entraînement offerts — révélés pour toujours.",
        countTreasure:     'trésors révélés',
        countTreasureShort: 'révélés',
        btnToTests:        "Entrer dans l'Armurerie",
    },
    corporate: {
        tagline:           'Excellence & Décision',
        navTests:          'Évaluations',
        navGrimoire:       'Synthèse',
        navHistory:        'Historique',
        navTreasure:       'Ressources',
        iconTests:         'ti-clipboard-check',
        iconGrimoire:      'ti-report-analytics',
        iconHistory:       'ti-history',
        iconTreasure:      'ti-briefcase',
        logout:            'Se déconnecter',
        password:          'Mot de passe',
        xpUnit:            'pts',
        xpName:            'Points',
        achievementKicker: 'Objectif atteint',
        titleTests:        'Vos évaluations',
        subtitleTests:     "Complétez les évaluations à votre rythme — chaque résultat alimente votre dossier de synthèse et affine les recommandations de carrière établies par l'IA. Votre conseiller virtuel (en bas à droite) reste disponible pour toute question.",
        countTests:        'évaluations disponibles',
        titleGrimoire:     'Dossier de synthèse',
        titleHistory:      'Historique des évaluations',
        subtitleHistory:   "L'ensemble de vos évaluations — terminées et en cours.",
        titleTreasure:     'Ressources de développement',
        subtitleTreasure:  'Vos points débloquent des modules de développement professionnel — accessibles définitivement.',
        countTreasure:     'modules débloqués',
        countTreasureShort: 'débloqués',
        btnToTests:        'Accéder aux évaluations',
    },
}

export function useParcours() {
    const page = usePage()

    const theme = computed(() =>
        page.props.auth?.user?.ui_theme === 'corporate' ? 'corporate' : 'medieval'
    )
    const isCorporate = computed(() => theme.value === 'corporate')
    const L = computed(() => LABELS[theme.value])

    // Synchronise <html data-theme> à chaud (changement sans rechargement).
    watch(theme, (t) => { document.documentElement.dataset.theme = t }, { immediate: true })

    function setParcours(t) {
        if (t !== 'medieval' && t !== 'corporate') return
        document.documentElement.dataset.theme = t // feedback visuel immédiat
        router.patch(route('ui-theme.update'), { ui_theme: t }, {
            preserveScroll: true,
            preserveState: true,
        })
    }

    return { theme, isCorporate, L, setParcours }
}
