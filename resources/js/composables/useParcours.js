import { computed, ref, watch } from 'vue'
import { usePage, router } from '@inertiajs/vue3'

// Préférence des VISITEURS (non connectés) : portée par localStorage pour que
// le choix fait sur /register survive à la navigation (login, CGU…) et soit
// repris à l'inscription. Les connectés suivent users.ui_theme (partagé Inertia).
const GUEST_KEY = 'pq_parcours_pref'
const guestTheme = ref(null)

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
        authTitle:         'Créer mon Identité de Héros',
        authSubtitle:      'La première Épreuve est offerte. Rejoins la Quête.',
        authQuestLabel:    'Choisis ton titre de Héros',
        authName:          'Ton nom dans la Quête',
        authEmail:         'Adresse du Héros',
        authPassword:      'Sceau secret',
        authPasswordConfirm: 'Confirmer le Sceau',
        authSubmit:        'Commencer la Quête',
        authHaveAccount:   'Déjà un Héros ?',
        authLoginLink:     '→ Entrer dans la Quête',
        authLoginTitle:    'Entrer dans la Quête',
        authLoginSubtitle: 'Reprenez là où vous en étiez.',
        authForgot:        'Sceau oublié ?',
        authRemember:      'Rester dans la Quête',
        authLoginSubmit:   'Entrer dans la Quête',
        authRegisterLink:  '→ Créer mon Identité',
        ctaTest:           "Entrer dans l'Épreuve →",
        badgeDone:         'Accomplie',
        typeFallback:      'Épreuve',
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
        authTitle:         'Créer mon compte',
        authSubtitle:      'La première évaluation est offerte. Deux minutes suffisent.',
        authQuestLabel:    'Votre profil dominant',
        authName:          'Nom complet',
        authEmail:         'Adresse email',
        authPassword:      'Mot de passe',
        authPasswordConfirm: 'Confirmation du mot de passe',
        authSubmit:        'Créer mon compte',
        authHaveAccount:   'Déjà un compte ?',
        authLoginLink:     '→ Se connecter',
        authLoginTitle:    'Connexion',
        authLoginSubtitle: 'Reprenez là où vous en étiez.',
        authForgot:        'Mot de passe oublié ?',
        authRemember:      'Rester connecté',
        authLoginSubmit:   'Se connecter',
        authRegisterLink:  '→ Créer mon compte',
        ctaTest:           "Commencer l'évaluation →",
        badgeDone:         'Terminée',
        typeFallback:      'Évaluation',
    },
}

export function useParcours() {
    const page = usePage()

    if (guestTheme.value === null) {
        try {
            guestTheme.value = localStorage.getItem(GUEST_KEY) === 'corporate' ? 'corporate' : 'medieval'
        } catch (e) {
            guestTheme.value = 'medieval'
        }
    }

    const theme = computed(() => {
        const authed = page.props.auth?.user?.ui_theme
        if (authed) return authed === 'corporate' ? 'corporate' : 'medieval'
        return guestTheme.value === 'corporate' ? 'corporate' : 'medieval'
    })
    const isCorporate = computed(() => theme.value === 'corporate')
    const L = computed(() => LABELS[theme.value])

    // Synchronise <html data-theme> à chaud (changement sans rechargement).
    watch(theme, (t) => { document.documentElement.dataset.theme = t }, { immediate: true })

    function setParcours(t) {
        if (t !== 'medieval' && t !== 'corporate') return
        document.documentElement.dataset.theme = t // feedback visuel immédiat
        if (page.props.auth?.user) {
            router.patch(route('ui-theme.update'), { ui_theme: t }, {
                preserveScroll: true,
                preserveState: true,
            })
        } else {
            guestTheme.value = t
            try { localStorage.setItem(GUEST_KEY, t) } catch (e) { /* mode privé */ }
        }
    }

    return { theme, isCorporate, L, setParcours }
}
