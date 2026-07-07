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

// ─── Noms corporate des tests / mini-apps ────────────────────────────────────
// Les noms « quête » vivent en base (manifests plugins + seeders). En parcours
// corporate on les remplace à l'affichage : d'abord par slug (fiable), sinon
// par motif sur le nom (pages qui n'exposent que le nom, ex. Grimoire/History).
const CORPORATE_TEST_NAMES = {
    'orientation-express':           'Orientation Express',
    'praximet-riasec':               'Intérêts professionnels — RIASEC',
    'praximum':                      'Personnalité — Big Five',
    'praxiemo':                      'Intelligence émotionnelle',
    'praxivaleurs':                  'Valeurs professionnelles — Schwartz',
    'praxicare':                     'Bien-être et risques psychosociaux',
    'praxibiais':                    'Biais cognitifs professionnels',
    'praxifocus':                    'Attention et concentration — repères TDAH',
    'praxisens':                     'Sensibilité sensorielle',
    'praxitempo':                    'Gestion du temps',
    'praxis360':                     'Feedback 360°',
    'competences-entrepreneuriales': 'Compétences entrepreneuriales — EntreComp',
    'praxiself':                     'Affirmation de soi',
    'praxiself-affirmation':         'Affirmation de soi',
    'praxispeak':                    'Prise de parole en public',
    'praxiflow':                     'Productivité et organisation',
    'praxiflow-productivite':        'Productivité et organisation',
    'praxizen':                      'Gestion du stress',
    'praxizen-stress':               'Gestion du stress',
    'praxilink':                     'Assertivité et relations',
    'praxilink-assertivite':         'Assertivité et relations',
    'praxiboost':                    'Motivation et confiance',
    'praxilead':                     'Management — programme 60 jours',
    'praximiroir':                   'Identité professionnelle — programme 30 jours',
    'praxivision':                   'Leadership — programme 60 jours',
    'praxizenith':                   'Concentration — programme d\'entraînement',
}

const CORPORATE_NAME_PATTERNS = [
    [/qu[êe]te de la voie/i,        'Intérêts professionnels — RIASEC'],
    [/grande cartographie/i,        'Personnalité — Big Five'],
    [/boussole des [ée]motions/i,   'Intelligence émotionnelle'],
    [/sentinelle int[ée]rieure/i,   'Bien-être et risques psychosociaux'],
    [/source des valeurs/i,         'Valeurs professionnelles — Schwartz'],
    [/cartographe mental/i,         'Biais cognitifs professionnels'],
    [/boussole de l'attention/i,    'Attention et concentration — repères TDAH'],
    [/radar des sens/i,             'Sensibilité sensorielle'],
    [/ma[îi]tre du temps/i,         'Gestion du temps'],
    [/constellation des talents/i,  'Feedback 360°'],
    [/[ée]toffe du b[âa]tisseur/i,  'Compétences entrepreneuriales — EntreComp'],
    [/forge du soi/i,               'Affirmation de soi'],
    [/voix du h[ée]ros/i,           'Prise de parole en public'],
    [/refuge int[ée]rieur/i,        'Gestion du stress'],
    [/art des liens/i,              'Assertivité et relations'],
    [/[ée]tincelle/i,               'Motivation et confiance'],
    [/forge de l'identit[ée]/i,     'Identité professionnelle — programme 30 jours'],
    [/60 jours de management/i,     'Management — programme 60 jours'],
    [/[ée]veilleur/i,               'Leadership — programme 60 jours'],
    [/sanctuaire de l'attention/i,  'Concentration — programme d\'entraînement'],
]

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

    // Nom d'affichage d'un test/mini-app selon le parcours. Accepte un objet
    // ({ slug, name } / { test_name }) ou une chaîne. En médiéval : inchangé.
    function testLabel(input) {
        const name = typeof input === 'string' ? input : (input?.name ?? input?.test_name ?? '')
        if (!isCorporate.value) return name
        const slug = (input && typeof input === 'object')
            ? (input.slug ?? input.test_slug ?? input.plugin_slug ?? '')
            : ''
        if (slug && CORPORATE_TEST_NAMES[slug]) return CORPORATE_TEST_NAMES[slug]
        for (const [re, label] of CORPORATE_NAME_PATTERNS) {
            if (re.test(name)) return label
        }
        return name
    }

    // Vouvoie un texte catalogue écrit au tutoiement (descriptions de tests
    // stockées en base). Transformation lexicale limitée aux possessifs et
    // pronoms — suffisante pour des textes descriptifs ; le contenu généré
    // par IA est traité à la source, dans les prompts (PromptBuilder).
    function vouvoyer(text) {
        if (!isCorporate.value || !text) return text
        return text
            .replace(/\bTes\b/g, 'Vos').replace(/\btes\b/g, 'vos')
            .replace(/\bTon\b/g, 'Votre').replace(/\bton\b/g, 'votre')
            .replace(/\bTa\b/g, 'Votre').replace(/\bta\b/g, 'votre')
            .replace(/\bT'/g, 'Vous ').replace(/\bt'/g, 'vous ')
            .replace(/\bTe\b/g, 'Vous').replace(/\bte\b/g, 'vous')
            .replace(/\bTu\b/g, 'Vous').replace(/\btu\b/g, 'vous')
            .replace(/\bToi\b/g, 'Vous').replace(/\btoi\b/g, 'vous')
    }

    return { theme, isCorporate, L, setParcours, testLabel, vouvoyer }
}
