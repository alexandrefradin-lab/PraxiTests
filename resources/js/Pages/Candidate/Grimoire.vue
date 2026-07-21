<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import CandidateLayout from '@/Layouts/CandidateLayout.vue'
import MarkdownText from '@/Components/MarkdownText.vue'
import { useParcours } from '@/composables/useParcours'

const { L, isCorporate, testLabel, vouvoyer } = useParcours()

const props = defineProps({
    grimoire:      Object,
    tests:         Array,
    ai_pending:    Boolean,
    voies_pending: Boolean,   // synthèse affichée, mais pistes encore en génération
    is_empty:      Boolean,
    marginalia_unlocked: { type: Boolean, default: false },
})

const voies = computed(() => props.grimoire?.voies ?? [])

// Onglet « Ton métier face à l'IA » : analyse Markdown générée avec les voies.
const iaImpact  = computed(() => (props.grimoire?.ia_impact ?? '').trim())
// En cours : l'analyse arrive en même temps que les pistes (étape 2 du job).
const iaPending = computed(() => props.voies_pending && !iaImpact.value)

// ── Onglets ───────────────────────────────────────────────────────────────
const pistesCount = ref(Math.min(50, props.grimoire?.requested_voies_count ?? 30))
// Sommaire : chiffres romains + « épreuves/Voies » en médiéval, numérotation
// 01/02 + « évaluations/Pistes » en corporate.
const tabs = computed(() => {
    const corp = isCorporate.value
    const base = [
        { key: 'synthese', label: corp ? 'Synthèse globale' : 'Relecture globale', tocLabel: 'Synthèse', pg: corp ? '—' : '✦' },
        { key: 'tests',    label: 'Résultats des tests', tocLabel: corp ? 'Mes évaluations' : 'Mes épreuves', pg: String(props.tests?.length ?? 0) },
        { key: 'ia',       label: corp ? 'Votre métier face à l\'IA' : 'Ton métier face à l\'IA', tocLabel: 'Métier & IA', pg: 'IA' },
        { key: 'pistes',   label: `${voies.value.length || pistesCount.value} Pistes métiers`, tocLabel: corp ? 'Pistes' : 'Voies', pg: String(voies.value.length || pistesCount.value) },
    ]
    const romans = ['I', 'II', 'III', 'IV']
    const mapped = base.map((t, i) => ({ ...t, roman: corp ? String(i + 1).padStart(2, '0') : romans[i] }))

    // Page apocryphe — n'apparaît au sommaire qu'une fois le secret trouvé.
    if (marginaliaUnlocked.value) {
        mapped.push({
            key: 'marginalia', label: 'Marginalia', tocLabel: 'Marginalia',
            pg: '✒', roman: corp ? '05' : '✦',
        })
    }
    return mapped
})
const activeTab = ref('synthese')

// ── Easter egg « Le Grimoire à l'envers » ────────────────────────────────
// Remonter les 4 sections dans l'ordre inverse, au clavier uniquement.
// Un clic souris sur le sommaire remet le compteur à zéro : c'est la
// contrainte qui rend la découverte volontaire plutôt qu'accidentelle.
const REVERSE_ORDER = ['pistes', 'ia', 'tests', 'synthese']
const marginaliaUnlocked = ref(props.marginalia_unlocked)
const showEgg = ref(false)
let keyTrail = []

function noteKeyboardTab(key) {
    if (marginaliaUnlocked.value) return
    keyTrail.push(key)
    if (keyTrail.length > REVERSE_ORDER.length) keyTrail.shift()
    if (keyTrail.length === REVERSE_ORDER.length
        && keyTrail.every((k, i) => k === REVERSE_ORDER[i])) {
        keyTrail = []
        showEgg.value = true
    }
}

function onTabClick(e, key) {
    activeTab.value = key
    // e.detail === 0 : clic synthétisé par le clavier (Entrée/Espace) — on ne
    // pénalise pas la navigation clavier, seulement la souris.
    if (e.detail > 0) keyTrail = []
}

function onEggClosed() {
    showEgg.value = false
    if (marginaliaUnlocked.value) activeTab.value = 'marginalia'
}

// Notes de copiste — le contenu de la page apocryphe.
const marginalia = [
    {
        note: 'En marge du folio des Voies',
        text: "Un métier n'est pas une destination, c'est une hypothèse. On l'écrit au crayon, on la teste, on la rature. Les bilans qui ne raturent rien n'ont rien testé.",
    },
    {
        note: 'En marge du folio des Épreuves',
        text: "Aucun test ne dit qui quelqu'un est. Il dit comment cette personne a répondu, un jour, dans un certain état d'esprit. C'est déjà beaucoup — à condition de ne pas confondre les deux.",
    },
    {
        note: 'En bas de page, d\'une autre main',
        text: "Le copiste qui relit à l'envers ne cherche pas le sens : il cherche les fautes. C'est pour ça qu'il les trouve. Relire son parcours à rebours fonctionne pareil.",
    },
]

// ── Ajustement des voies par préférences (curseurs) ──────────────────────
// Re-tri 100 % côté front, non sauvegardé : on pondère 5 axes décrivant chaque
// métier (renvoyés par l'IA dans v.axes) selon ce qui compte pour la personne.
const axisDefs = [
    { key: 'remuneration', label: 'Rémunération',   hint: 'Potentiel de salaire' },
    { key: 'accessibilite', label: 'Accès rapide',  hint: 'Formation courte, vite opérationnel' },
    { key: 'stabilite',     label: 'Stabilité',     hint: 'Sécurité de l\'emploi, demande durable' },
    { key: 'autonomie',     label: 'Autonomie',     hint: 'Indépendance, freelance, entreprendre' },
    { key: 'sens',          label: 'Sens & impact', hint: 'Utilité, contribution' },
]

// Poids 0–100, neutre = 50. Réactif pour le re-tri live.
const weights = ref(Object.fromEntries(axisDefs.map(a => [a.key, 50])))
// Tant que la personne n'a pas touché un curseur, on respecte l'ordre IA.
const customized = ref(false)

// Vrai seulement si au moins une voie porte des axes (anciens Grimoires : non).
const hasAxes = computed(() => voies.value.some(v => v && v.axes && typeof v.axes === 'object'))

function clamp100(n) { n = Number(n); return Number.isFinite(n) ? Math.max(0, Math.min(100, n)) : 50 }

// Valeur d'un axe pour une voie, avec repli : axe IA → fit_score → 50.
function axisValue(v, key) {
    const a = v?.axes
    if (a && a[key] != null && Number.isFinite(Number(a[key]))) return clamp100(a[key])
    return clamp100(v?.fit_score ?? 50)
}

// Score de préférence 0–100 = moyenne des axes pondérée par les curseurs.
function prefScore(v) {
    let num = 0, den = 0
    for (const a of axisDefs) {
        const w = clamp100(weights.value[a.key])
        num += w * axisValue(v, a.key)
        den += w
    }
    if (den === 0) return clamp100(v?.fit_score ?? 50) // tous les curseurs à 0 → repli fit
    return Math.round(num / den)
}

function onWeightInput() { customized.value = true }

function resetWeights() {
    for (const a of axisDefs) weights.value[a.key] = 50
    customized.value = false
}

// Voies affichées : ordre IA par défaut, re-trié par préférence dès interaction.
// Chaque voie porte _idx = sa position dans le tableau d'ORIGINE (grimoire->voies),
// nécessaire pour cibler la bonne piste côté serveur après re-tri.
const rankedVoies = computed(() => {
    const list = voies.value.map((v, i) => ({ ...v, _idx: i }))
    if (!customized.value || !hasAxes.value) return list
    return list
        .slice()
        .sort((a, b) => prefScore(b) - prefScore(a) || a._idx - b._idx)
})

// ── Plan d'action « 10 étapes » par piste (génération IA à la demande) ────
const planLoadingIdx = ref(null)
const planErrorIdx = ref(null)
function generatePlan(idx) {
    if (planLoadingIdx.value !== null) return
    planLoadingIdx.value = idx
    planErrorIdx.value = null
    router.post(route('grimoire.voie.plan', idx), {}, {
        preserveScroll: true,
        preserveState: true,
        only: ['grimoire', 'errors'],
        onError: () => { planErrorIdx.value = idx },
        onFinish: () => { planLoadingIdx.value = null },
    })
}

// ── Cartes dépliables : détail (axes + prochaine étape) au clic ──────────
// On garde un Set d'index dépliés. Cartes compactes par défaut (scannables),
// le détail s'ouvre à la demande — c'est le « détail au clic ».
const expanded = ref(new Set())
function toggleCard(i) {
    const s = new Set(expanded.value)
    s.has(i) ? s.delete(i) : s.add(i)
    expanded.value = s
}
function isExpanded(i) { return expanded.value.has(i) }

// Libellé lisible du modèle d'exercice (salariat / freelance / entrepreneuriat).
function modeleLabel(m) {
    return ({
        salariat: 'Salariat',
        freelance: 'Freelance',
        entrepreneuriat: 'Entrepreneuriat',
    })[String(m || '').toLowerCase()] || null
}

// Découpe la synthèse en paragraphes affichables.
const synthParagraphs = computed(() => {
    let raw = (props.grimoire?.synthesis || '').trim()
    if (!raw) return []

    raw = raw.replace(/\\n/g, '\n')

    // Découpe sur tout saut de ligne → paragraphes propres
    const paras = raw.split(/\n+/).map(p => p.trim()).filter(Boolean)
    if (paras.length > 1) return paras

    // Bloc monolithique : découpe par phrases
    const marked = raw.replace(/([.!?])\s+([A-ZÀÂÄÉÈÊËÎÏÔÙÛÜÇ«"])/g, '$1\x00$2')
    const sentences = marked.split('\x00').map(s => s.trim()).filter(s => s.length >= 30)
    if (sentences.length <= 1) return [raw]

    const count  = sentences.length
    const target = count <= 5 ? 2 : count <= 9 ? 3 : 4
    const per    = Math.ceil(count / target)
    const groups = []
    for (let i = 0; i < count; i += per) {
        groups.push(sentences.slice(i, i + per).join(' '))
    }
    return groups
})

// Polling de l'état du Grimoire pendant la (re)génération IA.
// IMPORTANT : démarré de façon RÉACTIVE (watch) et pas seulement dans onMounted.
// Après un clic "Régénérer", Inertia recharge la MÊME page sans remonter le
// composant : onMounted ne se rejoue pas. Sans le watch, le polling ne démarrait
// jamais et la page restait figée sur "L'oracle relit tes épreuves…".
let timer = null
let pollCount = ref(0)  // nombre de polls depuis le démarrage

// Affiche un bouton "Réessayer" dans l'écran pending si > 5 min sans réponse.
// Avec QUEUE_CONNECTION=database le job peut démarrer jusqu'à 1 min après le dispatch.
const pendingTooLong = computed(() => pollCount.value >= 60) // 60 × 5s = 5 min

function startPolling() {
    if (timer) return
    pollCount.value = 0
    timer = setInterval(async () => {
        try {
            const r = await fetch(route('grimoire.status'), { headers: { Accept: 'application/json' } })
            const data = await r.json()
            pollCount.value++

            // Deux jalons : on attend la SYNTHÈSE (écran pleine page) puis les VOIES
            // (loader dans l'onglet Pistes). On recharge dès que le jalon courant est
            // atteint — la synthèse s'affiche sans attendre la fin des pistes.
            const target = props.ai_pending
                ? (data.synthese_ready || data.failed || data.stuck)
                : (data.voies_ready || data.failed || data.stuck)

            if (target) {
                stopPolling()
                // Recharge partielle : on rafraîchit la page (synthèse + voies + flags).
                router.reload()
            }
        } catch (e) { /* retry au prochain tick */ }
    }, 4000)
}
function stopPolling() {
    if (timer) { clearInterval(timer); timer = null }
    pollCount.value = 0
}

// Relance manuelle depuis l'écran pending (cas "stuck" visible).
function retryFromPending() {
    stopPolling()
    router.reload()
}

// Démarre le polling tant qu'il reste quelque chose à attendre (synthèse OU voies).
function maybePoll() {
    if (props.ai_pending || props.voies_pending) startPolling()
    else stopPolling()
}
onMounted(maybePoll)
watch(() => props.ai_pending, maybePoll)
watch(() => props.voies_pending, maybePoll)
onUnmounted(stopPolling)

const refreshing = ref(false)
const regenError = ref('')
function regenerate() {
    refreshing.value = true
    regenError.value = ''
    window.scrollTo({ top: 0, behavior: 'smooth' })
    router.post(route('grimoire.refresh'), { count: pistesCount.value }, {
        preserveScroll: false,
        // Affiche le message du serveur (ex. garde-fou anti-spam « 1 régénération
        // / 10 min ») au lieu d'un échec silencieux où l'utilisateur croit que rien
        // ne marche.
        onError: (errors) => {
            regenError.value = errors?.regen || 'La régénération n\'a pas pu démarrer. Réessaie dans un instant.'
        },
        onFinish: () => { refreshing.value = false },
    })
}

// Keyboard navigation for tabs (ArrowLeft/ArrowRight)
function onTabKeydown(e) {
    if (!['ArrowLeft', 'ArrowRight'].includes(e.key)) return
    e.preventDefault()
    const keys = tabs.value.map(t => t.key)
    const current = keys.indexOf(activeTab.value)
    if (current === -1) return
    const next = e.key === 'ArrowRight'
        ? (current + 1) % keys.length
        : (current - 1 + keys.length) % keys.length
    activeTab.value = keys[next]
    noteKeyboardTab(keys[next])
    // Move focus to the newly active tab button
    const btn = document.getElementById('tab-' + keys[next])
    if (btn) btn.focus()
}

function fitClass(score) {
    if (score >= 80) return 'voie-fit-high'
    if (score >= 60) return 'voie-fit-mid'
    return 'voie-fit-low'
}
</script>

<template>
    <CandidateLayout>
        <Head :title="L.titleGrimoire" />

        <div class="grim-shell">

            <div v-if="is_empty" class="grim-empty">
                <div class="grim-flourish">&#10087;&nbsp;&nbsp;&#10022;&nbsp;&nbsp;&#10087;</div>
                <h1 class="grim-title">{{ L.titleGrimoire }}</h1>
                <div class="grim-rule"><span>&#10022;</span></div>
                <p class="grim-empty-text">
                    {{ isCorporate
                        ? 'Votre dossier de synthèse se construira au fil de vos évaluations. Complétez une première évaluation pour lancer l\'analyse.'
                        : 'Ton Grimoire se remplira au fil de tes épreuves. Passe une première épreuve pour que l\'oracle commence à relire ton profil.' }}
                </p>
                <Link :href="route('tests.index')" class="ac-btn-primary">{{ L.btnToTests }}</Link>
            </div>

            <div v-else-if="ai_pending" class="grim-pending">
                <div class="grim-flourish">&#10087;&nbsp;&nbsp;&#10022;&nbsp;&nbsp;&#10087;</div>
                <div class="grim-pulse-dots"><span></span><span></span><span></span></div>
                <h1 class="grim-title">{{ isCorporate ? 'Analyse en cours…' : 'L\'oracle relit tes épreuves…' }}</h1>
                <p class="grim-pending-sub">
                    Croisement de {{ isCorporate ? 'vos' : 'tes' }} {{ tests.length }} {{ isCorporate ? 'évaluation' : 'épreuve' }}{{ tests.length > 1 ? 's' : '' }} · 1 à 2 minutes
                </p>
                <div class="grim-rule"><span>&#10022;</span></div>
                <!-- Slider toujours visible en pending pour permettre de changer le nombre avant relance -->
                <div class="grim-pistes-picker grim-pistes-picker--pending">
                    <label for="pistes-count-slider-pending" class="grim-picker-label">
                        Pistes à générer : <strong>{{ pistesCount }}</strong>
                    </label>
                    <input
                        id="pistes-count-slider-pending"
                        type="range" min="5" max="50" step="5"
                        v-model.number="pistesCount"
                        class="grim-picker-range"
                    />
                    <div class="grim-picker-bounds"><span>5</span><span>50</span></div>
                </div>
                <!-- Bouton de secours si le job IA a été interrompu côté serveur -->
                <div v-if="pendingTooLong" class="grim-stuck-notice">
                    <p class="grim-stuck-text">{{ isCorporate ? 'L\'analyse prend plus de temps que prévu.' : 'La relecture prend plus de temps que prévu.' }}</p>
                    <button class="ac-btn-secondary" :disabled="refreshing" @click="regenerate">
                        {{ refreshing ? 'Relecture en cours…' : 'Relancer avec ' + pistesCount + ' pistes' }}
                    </button>
                </div>
            </div>

            <div v-else class="grim-content">

                <header class="grim-header">
                    <div class="grim-flourish">&#10087;&nbsp;&nbsp;&#10022;&nbsp;&nbsp;&#10087;</div>
                    <h1 class="grim-title">{{ L.titleGrimoire }}</h1>
                    <div class="grim-rule"><span>&#10022;</span></div>
                    <p class="grim-sub">
                        Ce que révèle le croisement de
                        <strong>{{ tests.length }}</strong> de {{ isCorporate ? 'vos évaluations' : 'tes épreuves' }}.
                    </p>

                </header>

                <div class="grim-body">

                <!-- ── Table des matières latérale ──────────────────────── -->
                <aside class="grim-toc" role="navigation" aria-label="Sommaire du Grimoire">
                    <div class="grim-toc-title">Sommaire</div>
                    <button
                        v-for="tab in tabs"
                        :key="tab.key"
                        :id="'tab-' + tab.key"
                        role="tab"
                        :aria-selected="activeTab === tab.key"
                        :aria-controls="'panel-' + tab.key"
                        :tabindex="activeTab === tab.key ? 0 : -1"
                        class="grim-toc-item"
                        :class="{ 'grim-toc-item--active': activeTab === tab.key }"
                        @click="activeTab = tab.key"
                        @keydown="onTabKeydown"
                    >
                        <span class="grim-toc-num">{{ tab.roman }}</span>
                        <span class="grim-toc-label">{{ tab.tocLabel }}</span>
                        <span class="grim-toc-dots" aria-hidden="true"></span>
                        <span class="grim-toc-pg">{{ tab.pg }}</span>
                    </button>
                </aside>

                <main class="grim-main">

                <!-- ── Panneau 1 : Relecture globale ─────────────────────── -->
                <div v-show="activeTab === 'synthese'" role="tabpanel" id="panel-synthese" aria-labelledby="tab-synthese">
                    <div v-if="grimoire?.status === 'failed'" class="grim-alert">
                        {{ grimoire.synthesis }}
                    </div>
                    <section v-else class="grim-synthesis">
                        <div class="grim-scroll">
                            <h2 class="grim-scroll-title">Le fil conducteur</h2>
                            <p v-for="(para, i) in synthParagraphs" :key="i" class="grim-para">{{ para }}</p>
                        </div>
                    </section>
                </div>

                <!-- ── Onglet 2 : Résultats des tests ────────────────── -->
                <div v-show="activeTab === 'tests'" role="tabpanel" id="panel-tests" aria-labelledby="tab-tests">
                    <section v-if="tests.length" class="grim-tests">
                        <div class="grim-section-head">
                            <h2 class="grim-section-title">{{ isCorporate ? 'Vos évaluations analysées' : 'Tes épreuves relues' }}</h2>
                            <p class="grim-voies-intro">
                                {{ isCorporate
                                    ? 'Ce que chaque évaluation révèle. Consultez le détail ou téléchargez le PDF.'
                                    : 'Ce que chaque épreuve te révèle. Ouvre le détail ou télécharge-le en PDF.' }}
                            </p>
                        </div>
                        <div class="grim-tests-list">
                            <article v-for="t in tests" :key="t.attempt_id" class="grim-test-card">
                                <div class="grim-test-main">
                                    <h3 class="grim-test-name">{{ testLabel(t) }}</h3>
                                    <p v-if="t.mesure" class="grim-test-measures">{{ vouvoyer(t.mesure) }}</p>
                                    <p v-if="t.detail_preview" class="grim-test-preview">
                                        <span class="grim-test-preview-label">Dans le détail</span>
                                        {{ vouvoyer(t.detail_preview) }}
                                    </p>
                                </div>
                                <div class="grim-test-actions">
                                    <Link :href="t.results_url" class="grim-test-link">Voir le détail</Link>
                                    <a v-if="t.pdf_url" :href="t.pdf_url" class="grim-test-pdf">
                                        Télécharger le PDF
                                    </a>
                                </div>
                            </article>
                        </div>
                    </section>
                </div>

                <!-- ── Onglet 3 : Ton métier face à l'IA ─────────────── -->
                <div v-show="activeTab === 'ia'" role="tabpanel" id="panel-ia" aria-labelledby="tab-ia">
                    <!-- Analyse en cours (elle arrive avec les pistes) -->
                    <div v-if="iaPending" class="grim-voies-loading">
                        <div class="grim-pulse-dots"><span></span><span></span><span></span></div>
                        <p class="grim-voies-loading-text">{{ isCorporate ? 'Analyse de l\'impact de l\'IA sur votre métier…' : 'L\'oracle sonde l\'avenir de ton métier face à l\'IA…' }}</p>
                        <p class="grim-voies-loading-sub">{{ isCorporate ? 'Quelques secondes — la synthèse est déjà disponible dans la première section.' : 'Quelques secondes — la relecture est déjà disponible dans le premier onglet.' }}</p>
                    </div>
                    <p v-else-if="!iaImpact" class="grim-voies-empty">
                        {{ isCorporate
                            ? 'Cette analyse n\'a pas encore été générée. Cliquez sur « Actualiser la synthèse » pour l\'obtenir.'
                            : 'Cette analyse n\'a pas encore été générée. Clique sur « Régénérer le Grimoire » pour l\'obtenir.' }}
                    </p>
                    <section v-else class="grim-ia">
                        <div class="grim-section-head">
                            <h2 class="grim-section-title">{{ isCorporate ? 'Votre métier face à l\'IA' : 'Ton métier face à l\'IA' }}</h2>
                            <p class="grim-voies-intro">
                                Comment l'intelligence artificielle est susceptible de transformer {{ isCorporate ? 'votre' : 'ton' }} métier — et comment en faire un atout.
                            </p>
                        </div>
                        <div class="grim-scroll grim-ia-scroll">
                            <MarkdownText :source="iaImpact" />
                        </div>
                    </section>
                </div>

                <!-- ── Onglet 4 : Les pistes ─────────────────────────── -->
                <div v-show="activeTab === 'pistes'" role="tabpanel" id="panel-pistes" aria-labelledby="tab-pistes">
                    <!-- Pistes en cours de génération (la synthèse, elle, est déjà là) -->
                    <div v-if="voies_pending && !voies.length" class="grim-voies-loading">
                        <div class="grim-pulse-dots"><span></span><span></span><span></span></div>
                        <p class="grim-voies-loading-text">
                            <template v-if="isCorporate">Génération de vos <strong>{{ pistesCount }}</strong> pistes de carrière…</template>
                            <template v-else>L'oracle trace tes <strong>{{ pistesCount }}</strong> pistes métiers…</template>
                        </p>
                        <p class="grim-voies-loading-sub">{{ isCorporate ? 'Quelques secondes — la synthèse est déjà disponible dans la section précédente.' : 'Quelques secondes — la relecture est déjà disponible dans l\'onglet précédent.' }}</p>
                    </div>
                    <p v-else-if="!voies.length" class="grim-voies-empty">
                        {{ isCorporate
                            ? 'Aucune piste générée pour l\'instant. Choisissez un nombre de pistes et cliquez sur « Actualiser la synthèse ».'
                            : 'Aucune piste générée pour l\'instant. Choisis un nombre de pistes et clique sur « Régénérer le Grimoire ».' }}
                    </p>
                    <section v-if="voies.length" class="grim-voies">
                        <div class="grim-section-head">
                            <h2 class="grim-section-title">{{ isCorporate ? 'Vos pistes de carrière' : 'Tes Voies Possibles' }}</h2>
                            <p class="grim-voies-intro">
                                {{ voies.length }} pistes {{ isCorporate ? 'établies en croisant l\'ensemble de vos résultats' : 'tracées en croisant l\'ensemble de tes résultats' }}.
                            </p>
                        </div>

                        <!-- ── Curseurs de préférences : re-trie les voies en direct ── -->
                        <div v-if="hasAxes" class="grim-tuner">
                            <div class="grim-tuner-head">
                                <h3 class="grim-tuner-title">{{ isCorporate ? 'Ajustez selon vos priorités' : 'Ajuste selon ce qui compte pour toi' }}</h3>
                                <button v-if="customized" type="button" class="grim-tuner-reset" @click="resetWeights">
                                    Réinitialiser
                                </button>
                            </div>
                            <p class="grim-tuner-sub">
                                {{ isCorporate
                                    ? 'Déplacez les curseurs : les pistes se réordonnent instantanément selon vos priorités.'
                                    : 'Déplace les curseurs : tes voies se réordonnent instantanément selon tes priorités.' }}
                                <em>(Ce réglage n'est pas enregistré.)</em>
                            </p>
                            <div class="grim-tuner-grid">
                                <label v-for="a in axisDefs" :key="a.key" class="grim-slider">
                                    <span class="grim-slider-label">{{ a.label }}</span>
                                    <input
                                        type="range" min="0" max="100" step="5"
                                        v-model.number="weights[a.key]"
                                        @input="onWeightInput"
                                        class="grim-slider-input"
                                        :title="a.hint"
                                    />
                                    <span class="grim-slider-val">{{ weights[a.key] }}</span>
                                </label>
                            </div>
                        </div>

                        <div class="grim-voies-grid">
                            <article
                                v-for="(v, i) in rankedVoies"
                                :key="v.titre || i"
                                class="grim-voie-card"
                                :class="{ 'grim-voie-card--open': isExpanded(i) }"
                            >
                                <div class="grim-voie-head">
                                    <span class="grim-voie-rank">{{ i + 1 }}</span>
                                    <span
                                        v-if="customized && hasAxes"
                                        class="grim-voie-fit"
                                        :class="fitClass(prefScore(v))"
                                        title="Correspondance à tes préférences"
                                    >
                                        {{ prefScore(v) }}%
                                    </span>
                                    <span v-else-if="v.fit_score != null" class="grim-voie-fit" :class="fitClass(clamp100(v.fit_score))">
                                        {{ clamp100(v.fit_score) }}%
                                    </span>
                                </div>
                                <h3 class="grim-voie-titre">{{ v.titre }}</h3>
                                <div class="grim-voie-meta">
                                    <span v-if="v.secteur" class="grim-voie-secteur">{{ v.secteur }}</span>
                                    <span v-if="modeleLabel(v.modele)" class="grim-voie-modele">{{ modeleLabel(v.modele) }}</span>
                                </div>
                                <p v-if="v.pourquoi" class="grim-voie-why">{{ v.pourquoi }}</p>

                                <!-- Détail au clic : axes + prochaine étape + plan d'action -->
                                <button
                                    type="button"
                                    class="grim-voie-toggle"
                                    :aria-expanded="isExpanded(i)"
                                    @click="toggleCard(i)"
                                >
                                    {{ isExpanded(i) ? 'Masquer le détail' : 'Voir le détail' }}
                                    <span class="grim-voie-toggle-caret" :class="{ 'is-open': isExpanded(i) }">▾</span>
                                </button>

                                <div v-show="isExpanded(i)" class="grim-voie-detail">
                                    <div v-if="v.axes && typeof v.axes === 'object'" class="grim-voie-axes">
                                        <div v-for="a in axisDefs" :key="a.key" class="grim-axis-row" :title="a.hint">
                                            <span class="grim-axis-label">{{ a.label }}</span>
                                            <span class="grim-axis-bar">
                                                <span class="grim-axis-fill" :style="{ width: axisValue(v, a.key) + '%' }"></span>
                                            </span>
                                            <span class="grim-axis-num">{{ axisValue(v, a.key) }}</span>
                                        </div>
                                    </div>
                                    <p v-if="v.prochaine_etape" class="grim-voie-next">
                                        <span class="grim-voie-next-label">Prochaine étape</span>
                                        {{ v.prochaine_etape }}
                                    </p>

                                    <!-- Plan d'action 10 étapes (généré à la demande, puis persistant) -->
                                    <div v-if="Array.isArray(v.plan) && v.plan.length" class="grim-voie-plan">
                                        <span class="grim-voie-next-label">Plan d'action — {{ v.plan.length }} étapes</span>
                                        <ol class="grim-plan-list">
                                            <li v-for="(step, si) in v.plan" :key="si">{{ step }}</li>
                                        </ol>
                                    </div>
                                    <template v-else>
                                        <button
                                            type="button"
                                            class="grim-plan-btn"
                                            :disabled="planLoadingIdx !== null"
                                            @click="generatePlan(v._idx)"
                                        >
                                            {{ planLoadingIdx === v._idx ? 'Génération du plan…' : "Générer le plan d'action (10 étapes)" }}
                                        </button>
                                        <p v-if="planErrorIdx === v._idx" class="grim-plan-error" role="alert">
                                            {{ isCorporate ? "Le plan n'a pas pu être généré. Réessayez dans un instant." : "Le plan n'a pas pu être généré. Réessaie dans un instant." }}
                                        </p>
                                    </template>
                                </div>
                            </article>
                        </div>
                    </section>

                </div>

                </main>
                </div><!-- /grim-body -->

                <footer class="grim-footer">
                    <div class="grim-rule"><span>&#10022;</span></div>
                    <div class="grim-pistes-picker">
                        <label for="pistes-count-slider" class="grim-picker-label">
                            Nombre de pistes métiers à générer :
                            <strong>{{ pistesCount }}</strong>
                        </label>
                        <input
                            id="pistes-count-slider"
                            type="range"
                            min="5" max="50" step="5"
                            v-model.number="pistesCount"
                            class="grim-picker-range"
                        />
                        <div class="grim-picker-bounds"><span>5</span><span>50</span></div>
                    </div>
                    <div class="grim-actions">
                        <a v-if="grimoire?.status === 'ready'" :href="route('grimoire.pdf')" class="ac-btn-primary">
                            Télécharger en PDF
                        </a>
                        <button class="ac-btn-secondary" :disabled="refreshing" @click="regenerate">
                            {{ refreshing ? (isCorporate ? 'Analyse en cours…' : 'Relecture en cours…') : (isCorporate ? 'Actualiser la synthèse' : 'Régénérer le Grimoire') }}
                        </button>
                    </div>
                    <p v-if="regenError" class="grim-regen-error" role="alert">{{ regenError }}</p>
                    <p v-if="grimoire?.disclaimer" class="grim-disclaimer">
                        {{ grimoire.disclaimer.disclaimer_text }}
                    </p>
                    <p class="grim-disclaimer" style="font-style:normal;font-size:12px;margin-top:0.85rem">
                        <strong>Outil d'auto-évaluation et de développement personnel.</strong>
                        Les contenus de ce {{ isCorporate ? 'dossier' : 'Grimoire' }} sont générés par IA, à titre informatif. Ils ne
                        constituent pas un avis professionnel et ne remplacent pas un psychologue, un
                        médecin ou un coach.
                    </p>
                </footer>
            </div>
        </div>
    </CandidateLayout>
</template>

<style scoped>
.grim-shell {
    max-width: 1040px;
    margin: 0 auto;
    padding: 1rem 1.25rem 4rem;
    --grim-gold: var(--color-primary, #A67520);
    --grim-gold-dark: var(--color-primary-dark, #7D5510);
    --grim-red: var(--color-secondary, #7B1515);
    --grim-ink: var(--text-primary, #2A1E08);
}

.grim-flourish {
    text-align: center;
    color: var(--grim-gold);
    font-size: 1.05rem;
    letter-spacing: .35em;
    opacity: .8;
    margin-bottom: 1.1rem;
}
.grim-rule {
    position: relative;
    height: 1px;
    max-width: 320px;
    margin: 1.1rem auto;
    background: linear-gradient(90deg, transparent, var(--grim-gold) 18%, var(--grim-gold) 82%, transparent);
    opacity: .55;
}
.grim-rule span {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background: var(--bg-base);
    padding: 0 .55rem;
    color: var(--grim-gold);
    font-size: .8rem;
}

.grim-title {
    font-family: var(--font-display, 'Space Grotesk', sans-serif);
    font-size: clamp(2.1rem, 5vw, 2.9rem);
    font-weight: 700;
    text-align: center;
    letter-spacing: .03em;
    margin: 0;
    color: var(--grim-ink);
    text-shadow: 0 1px 0 rgba(255,255,255,.5);
}
.grim-sub {
    text-align: center;
    font-family: var(--font-body, 'Inter', sans-serif);
    font-size: 1.05rem;
    color: var(--text-secondary, #6B5A3E);
    margin: 0 0 1.1rem;
}
.grim-sub strong { color: var(--grim-red); font-weight: 600; }

.grim-empty, .grim-pending { text-align: center; padding: 4.5rem 1rem; }
.grim-stuck-notice { margin-top: 1.5rem; }
.grim-stuck-text { font-family: var(--font-body, 'Inter', sans-serif); font-size: .95rem; color: var(--text-muted, #8C7A5E); margin-bottom: .85rem; }
.grim-empty-text, .grim-pending-sub {
    font-family: var(--font-body, 'Inter', sans-serif);
    font-size: 1.05rem;
    color: var(--text-secondary, #6B5A3E);
    max-width: 480px;
    margin: 0 auto 1.8rem;
    line-height: 1.65;
}
.grim-pulse-dots { display: flex; gap: 9px; justify-content: center; margin: 1.5rem 0; }
.grim-pulse-dots span { width: 9px; height: 9px; border-radius: 50%; background: var(--grim-gold); animation: grimPulse 1.2s infinite ease-in-out; }
.grim-pulse-dots span:nth-child(2) { animation-delay: .2s; }
.grim-pulse-dots span:nth-child(3) { animation-delay: .4s; }
@keyframes grimPulse { 0%, 80%, 100% { opacity: .25; transform: scale(.8); } 40% { opacity: 1; transform: scale(1); } }

/* ── Layout corps du Grimoire ─────────────────────────────────────────── */
.grim-body {
    display: grid;
    grid-template-columns: 185px 1fr;
    gap: 3rem;
    align-items: start;
}
.grim-main { min-width: 0; }

/* ── Table des matières latérale ───────────────────────────────────────── */
.grim-toc {
    position: sticky;
    top: 2rem;
    border-right: 1px solid rgba(166,117,32,0.28);
    padding-right: 1.4rem;
}
.grim-toc-title {
    font-family: var(--font-data, monospace);
    font-size: 10px;
    letter-spacing: .12em;
    text-transform: uppercase;
    color: rgba(166,117,32,0.55);
    margin-bottom: .9rem;
    padding-bottom: .5rem;
    border-bottom: 1px solid rgba(166,117,32,0.18);
}
.grim-toc-item {
    display: flex;
    align-items: baseline;
    width: 100%;
    background: transparent;
    border: none;
    border-right: 2px solid transparent;
    margin-right: -1px;
    padding: 6px 0;
    cursor: pointer;
    text-align: left;
    transition: color .15s;
}
.grim-toc-item:hover .grim-toc-num,
.grim-toc-item:hover .grim-toc-label { color: var(--grim-gold-dark); }
.grim-toc-item--active { border-right-color: var(--grim-gold); }
.grim-toc-item--active .grim-toc-num,
.grim-toc-item--active .grim-toc-label { color: var(--grim-ink); font-weight: 600; }
.grim-toc-item--active .grim-toc-pg { color: var(--grim-gold-dark); }
.grim-toc-num {
    font-family: Georgia, 'Times New Roman', serif;
    font-style: italic;
    font-size: 13px;
    color: rgba(166,117,32,0.65);
    min-width: 1.7rem;
    flex-shrink: 0;
}
.grim-toc-label {
    font-family: var(--font-body, 'Inter', sans-serif);
    font-size: 13px;
    color: var(--text-secondary, #6B5A3E);
    white-space: nowrap;
    flex-shrink: 1;
    overflow: hidden;
}
.grim-toc-dots {
    flex: 1;
    border-bottom: 1px dotted rgba(166,117,32,0.30);
    margin: 0 6px 3px;
    min-width: 4px;
}
.grim-toc-pg {
    font-family: var(--font-data, monospace);
    font-size: 11px;
    color: rgba(166,117,32,0.55);
    flex-shrink: 0;
}

.grim-header { text-align: center; margin-bottom: 2.75rem; }
.grim-badge {
    display: inline-block;
    font-family: var(--font-data, monospace);
    font-size: 10px;
    letter-spacing: .22em;
    text-transform: uppercase;
    color: var(--grim-gold-dark);
    border: 1px solid var(--grim-gold);
    border-radius: 2px;
    padding: 5px 14px;
    margin-bottom: .9rem;
    background: rgba(166,117,32,0.06);
}
.grim-tests-chips { display: flex; flex-wrap: wrap; gap: 8px; justify-content: center; margin-top: 1.1rem; }
.grim-chip {
    font-family: var(--font-body, 'Inter', sans-serif);
    font-size: 13px;
    font-weight: 500;
    background: linear-gradient(180deg, #F4EDDC, #E8DCC2);
    color: var(--grim-ink);
    padding: 5px 14px;
    border: 1px solid var(--border-mid, rgba(166,117,32,0.25));
    border-radius: 999px;
    box-shadow: inset 0 1px 0 rgba(255,255,255,.5), 0 1px 2px rgba(42,30,8,.06);
}

.grim-synthesis { max-width: 760px; margin: 0 auto 3.5rem; }

/* Onglet « Ton métier face à l'IA » : même parchemin que la synthèse. */
.grim-ia { max-width: 760px; margin: 0 auto 3.5rem; }
.grim-ia-scroll { margin-top: 0; }
.grim-scroll {
    position: relative;
    background:
        radial-gradient(120% 100% at 50% 0%, rgba(255,250,238,.7), transparent 60%),
        linear-gradient(180deg, #F7F0DF, #EDE2C8);
    border: 1px solid var(--grim-gold);
    border-radius: var(--r-lg, 12px);
    padding: 2.5rem 2.4rem 2.2rem;
    box-shadow: var(--shadow-elevated, 0 8px 32px rgba(42,30,8,0.15)), inset 0 0 0 1px rgba(255,255,255,.35);

    /* Remapping des variables --pt-* de MarkdownText vers le thème Grimoire */
    --pt-text:        var(--grim-ink);
    --pt-navy:        var(--grim-ink);
    --pt-gold:        var(--grim-gold);
    --pt-gold-hover:  var(--grim-gold-dark);
    --pt-gold-pale:   rgba(166,117,32,0.10);
    --pt-gold-border: var(--grim-gold);
    --pt-border:      rgba(166,117,32,0.30);
    --pt-text-muted:  var(--text-secondary, #6B5A3E);
}
.grim-scroll::before {
    content: '';
    position: absolute;
    inset: 9px;
    border: 1px solid rgba(166,117,32,0.35);
    border-radius: calc(var(--r-lg, 12px) - 5px);
    pointer-events: none;
}
.grim-scroll-title {
    font-family: var(--font-display, 'Space Grotesk', sans-serif);
    font-size: 1.05rem;
    font-weight: 600;
    letter-spacing: .14em;
    text-transform: uppercase;
    text-align: center;
    color: var(--grim-red);
    margin: 0 0 1.4rem;
}

/* Paragraphes directs (v-for synthParagraphs) */
.grim-para {
    font-family: var(--font-body, 'Inter', sans-serif);
    font-size: 1.05rem;
    line-height: 1.78;
    color: var(--grim-ink);
    margin: 0 0 1.35rem;
    text-align: justify;
    hyphens: auto;
}
.grim-para:last-child { margin-bottom: 0; }
.grim-para:first-of-type::first-letter {
    font-family: var(--font-display, 'Space Grotesk', sans-serif);
    font-weight: 700;
    font-size: 3.1rem;
    line-height: .82;
    float: left;
    padding: .08em .12em 0 0;
    margin-right: .04em;
    color: var(--grim-red);
}

/* MarkdownText dans le parchemin — surcharge du rendu par défaut */
.grim-scroll :deep(.pt-md) {
    font-family: var(--font-body, 'Inter', sans-serif);
    font-size: 1.05rem;
    line-height: 1.78;
    color: var(--grim-ink);
}
.grim-scroll :deep(.pt-md p) {
    margin: 0 0 1.35rem;
    text-align: justify;
    hyphens: auto;
}
.grim-scroll :deep(.pt-md p:last-child) { margin-bottom: 0; }
.grim-scroll :deep(.pt-md p:first-of-type::first-letter) {
    font-family: var(--font-display, 'Space Grotesk', sans-serif);
    font-weight: 700;
    font-size: 3.1rem;
    line-height: .82;
    float: left;
    padding: .08em .12em 0 0;
    margin-right: .04em;
    color: var(--grim-red);
}
.grim-scroll :deep(.pt-md h2) {
    font-family: var(--font-display, 'Space Grotesk', sans-serif);
    font-size: 1.15rem;
    font-weight: 700;
    color: var(--grim-red);
    letter-spacing: .06em;
    text-transform: uppercase;
    margin: 2rem 0 .6rem;
}
.grim-scroll :deep(.pt-md h2:first-child) { margin-top: 0; }
.grim-scroll :deep(.pt-md h3) {
    font-family: var(--font-display, 'Space Grotesk', sans-serif);
    font-size: 1rem;
    font-weight: 600;
    color: var(--grim-gold-dark);
    margin: 1.5rem 0 .4rem;
}
.grim-scroll :deep(.pt-md strong) { color: var(--grim-ink); font-weight: 700; }
.grim-scroll :deep(.pt-md ul),
.grim-scroll :deep(.pt-md ol) {
    margin: .4rem 0 1.2rem;
    padding-left: 1.4rem;
}
.grim-scroll :deep(.pt-md li) { margin: .35rem 0; }
.grim-scroll :deep(.pt-md ul li)::marker { color: var(--grim-gold); }
.grim-scroll :deep(.pt-md ol li)::marker { color: var(--grim-gold-dark); font-weight: 700; }
.grim-scroll :deep(.pt-md hr) {
    border: none;
    border-top: 1px solid rgba(166,117,32,0.40);
    margin: 1.6rem 0;
}
.grim-scroll :deep(.pt-md blockquote) {
    border-left: 2px solid var(--grim-gold);
    padding: .3rem 0 .3rem 1rem;
    margin: .9rem 0;
    color: var(--text-secondary, #6B5A3E);
    font-style: italic;
}

.grim-alert {
    font-family: var(--font-body, 'Inter', sans-serif);
    font-size: 1rem;
    max-width: 760px;
    margin: 0 auto 2.5rem;
    padding: 1.1rem 1.4rem;
    background: rgba(176,48,32,0.07);
    border: 1px solid rgba(176,48,32,0.3);
    border-left: 3px solid var(--grim-red);
    border-radius: var(--r, 8px);
    color: var(--grim-red);
}

.grim-section-head { text-align: center; margin-bottom: 1.8rem; }
.grim-section-title {
    font-family: var(--font-display, 'Space Grotesk', sans-serif);
    font-size: 1.5rem;
    font-weight: 700;
    letter-spacing: .02em;
    margin: 0 0 .4rem;
    color: var(--grim-ink);
}
.grim-voies-intro {
    font-family: var(--font-body, 'Inter', sans-serif);
    font-size: 1rem;
    color: var(--text-secondary, #6B5A3E);
    margin: 0;
}

.grim-voies { margin-bottom: 3.5rem; }

/* ── Curseurs de préférences ──────────────────────────────────────────── */
.grim-tuner {
    border: 1px solid var(--border-mid, rgba(166,117,32,0.25));
    border-top: 2px solid var(--grim-gold);
    border-radius: var(--r-lg, 12px);
    background: linear-gradient(180deg, #FBF6EA, #F1E7CF);
    box-shadow: var(--shadow-card, 0 2px 12px rgba(42,30,8,0.10));
    padding: 1.25rem 1.4rem 1.4rem;
    margin-bottom: 1.6rem;
}
.grim-tuner-head { display: flex; align-items: center; justify-content: space-between; gap: 1rem; }
.grim-tuner-title {
    font-family: var(--font-display, 'Space Grotesk', sans-serif);
    font-size: 1.05rem; font-weight: 600; color: var(--grim-ink); margin: 0;
}
.grim-tuner-reset {
    font-family: var(--font-data, monospace);
    font-size: 11px; letter-spacing: .06em; text-transform: uppercase;
    color: var(--grim-red); background: transparent;
    border: 1px solid var(--grim-red); border-radius: var(--r-sm, 6px);
    padding: 4px 11px; cursor: pointer; transition: background .15s;
}
.grim-tuner-reset:hover { background: rgba(123,21,21,0.08); }
.grim-tuner-sub {
    font-family: var(--font-body, 'Inter', sans-serif);
    font-size: .92rem; color: var(--text-secondary, #6B5A3E);
    margin: .35rem 0 1.1rem;
}
.grim-tuner-sub em { font-style: italic; opacity: .8; }
.grim-tuner-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: .55rem 1.6rem;
}
.grim-slider {
    display: grid;
    grid-template-columns: 1fr auto;
    align-items: center;
    column-gap: .6rem;
    padding: .25rem 0;
}
.grim-slider-label {
    font-family: var(--font-body, 'Inter', sans-serif);
    font-size: .9rem; font-weight: 500; color: var(--grim-ink);
    grid-column: 1 / 2;
}
.grim-slider-val {
    font-family: var(--font-data, monospace);
    font-size: 12px; color: var(--grim-gold-dark);
    min-width: 2.4ch; text-align: right;
    grid-column: 2 / 3;
}
.grim-slider-input {
    grid-column: 1 / 3;
    width: 100%;
    accent-color: var(--grim-gold);
    cursor: pointer;
    margin-top: .2rem;
}

/* ── Voies possibles : cartes ─────────────────────────────────────────── */
.grim-voies-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(290px, 1fr)); gap: 1.1rem; }
.grim-voie-card {
    position: relative;
    border: 1px solid var(--border-mid, rgba(166,117,32,0.25));
    border-top: 2px solid var(--grim-gold);
    border-radius: var(--r-lg, 12px);
    padding: 1.4rem 1.4rem 1.5rem;
    background: linear-gradient(180deg, #FBF6EA, #F1E7CF);
    box-shadow: var(--shadow-card, 0 2px 12px rgba(42,30,8,0.10));
    transition: box-shadow .18s ease, transform .18s ease, border-color .18s ease;
}
.grim-voie-card:hover {
    box-shadow: var(--shadow-elevated, 0 8px 32px rgba(42,30,8,0.15));
    transform: translateY(-3px);
    border-color: var(--grim-gold);
}
.grim-voie-head { display: flex; justify-content: space-between; align-items: center; margin-bottom: .7rem; }
.grim-voie-rank {
    font-family: var(--font-display, 'Space Grotesk', sans-serif);
    font-weight: 700;
    font-size: 1.25rem;
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--grim-gold-dark);
    border: 1px solid var(--grim-gold);
    border-radius: 50%;
    background: radial-gradient(circle at 35% 30%, #FBF3DF, #E9D9B4);
    box-shadow: inset 0 1px 2px rgba(255,255,255,.6);
}
.grim-voie-fit { font-family: var(--font-data, monospace); font-size: 12px; font-weight: 700; padding: 3px 10px; border-radius: 999px; }
.voie-fit-high { background: rgba(58,107,72,0.15); color: var(--color-success, #3A6B48); border: 1px solid rgba(58,107,72,0.4); }
.voie-fit-mid  { background: rgba(166,117,32,0.15); color: var(--grim-gold-dark); border: 1px solid rgba(166,117,32,0.4); }
.voie-fit-low  { background: rgba(140,122,94,0.12); color: var(--text-muted, #8C7A5E); border: 1px solid rgba(140,122,94,0.35); }
.grim-voie-titre {
    font-family: var(--font-display, 'Space Grotesk', sans-serif);
    font-size: 1.12rem;
    font-weight: 600;
    color: var(--grim-ink);
    line-height: 1.3;
    margin-bottom: .2rem;
}
.grim-voie-secteur { font-family: var(--font-data, monospace); font-size: 10px; color: var(--text-muted, #8C7A5E); text-transform: uppercase; letter-spacing: .1em; margin-bottom: .7rem; }
.grim-voie-why { font-family: var(--font-body, 'Inter', sans-serif); font-size: .98rem; line-height: 1.6; color: var(--text-secondary, #6B5A3E); margin-bottom: .9rem; }
.grim-voie-appui { display: flex; flex-wrap: wrap; align-items: center; gap: 6px; margin-bottom: .9rem; }
.grim-voie-appui-label { font-family: var(--font-data, monospace); font-size: 10px; text-transform: uppercase; letter-spacing: .08em; color: var(--text-muted, #8C7A5E); }
.grim-appui-tag { font-family: var(--font-body, 'Inter', sans-serif); font-size: 12px; background: rgba(166,117,32,0.1); color: var(--grim-gold-dark); padding: 2px 9px; border-radius: 4px; border: 1px solid rgba(166,117,32,0.25); }
.grim-voie-next { font-family: var(--font-body, 'Inter', sans-serif); font-size: .95rem; line-height: 1.55; color: var(--grim-ink); border-top: 1px solid rgba(166,117,32,0.25); padding-top: .75rem; }
.grim-voie-next-label { display: block; font-family: var(--font-data, monospace); font-size: 10px; text-transform: uppercase; letter-spacing: .1em; color: var(--grim-red); margin-bottom: .25rem; }

/* ── Plan d'action 10 étapes ── */
.grim-voie-plan {
    border-top: 1px solid rgba(166,117,32,0.25);
    padding-top: .75rem;
    margin-top: .75rem;
}
.grim-plan-list {
    margin: .3rem 0 0;
    padding-left: 1.3rem;
    list-style: decimal;
}
.grim-plan-list li {
    font-family: var(--font-body, 'Inter', sans-serif);
    font-size: .92rem;
    line-height: 1.5;
    color: var(--grim-ink);
    margin: .4rem 0;
    padding-left: .15rem;
}
.grim-plan-list li::marker {
    font-family: var(--font-data, monospace);
    font-weight: 700;
    color: var(--grim-gold-dark);
}
.grim-plan-btn {
    display: inline-flex;
    align-items: center;
    gap: .4rem;
    margin-top: .85rem;
    font-family: var(--font-data, monospace);
    font-size: 11px;
    letter-spacing: .06em;
    text-transform: uppercase;
    color: #FBF6EA;
    background: linear-gradient(180deg, var(--grim-gold), var(--grim-gold-dark));
    border: none;
    border-radius: 8px;
    padding: 8px 14px;
    cursor: pointer;
    transition: filter .15s, opacity .15s;
}
.grim-plan-btn:hover:not(:disabled) { filter: brightness(1.07); }
.grim-plan-btn:disabled { opacity: .55; cursor: wait; }
.grim-plan-error {
    font-family: var(--font-body, 'Inter', sans-serif);
    font-size: .85rem;
    color: var(--grim-red);
    margin: .5rem 0 0;
}

/* Secteur + modèle sur une ligne */
.grim-voie-meta { display: flex; flex-wrap: wrap; align-items: center; gap: 8px; margin-bottom: .65rem; }
.grim-voie-meta .grim-voie-secteur { margin-bottom: 0; }
.grim-voie-modele {
    font-family: var(--font-data, monospace); font-size: 10px; letter-spacing: .08em; text-transform: uppercase;
    color: var(--grim-gold-dark); background: rgba(166,117,32,0.10);
    border: 1px solid rgba(166,117,32,0.30); border-radius: 999px; padding: 2px 9px;
}

/* Bouton "Voir le détail" */
.grim-voie-toggle {
    display: inline-flex; align-items: center; gap: .35rem;
    font-family: var(--font-data, monospace); font-size: 11px; letter-spacing: .06em; text-transform: uppercase;
    color: var(--grim-gold-dark); background: transparent; border: none; cursor: pointer;
    padding: .2rem 0; margin-top: .1rem;
}
.grim-voie-toggle:hover { color: var(--grim-red); }
.grim-voie-toggle-caret { transition: transform .18s ease; display: inline-block; }
.grim-voie-toggle-caret.is-open { transform: rotate(180deg); }

/* Détail déplié : axes + prochaine étape */
.grim-voie-detail { margin-top: .65rem; }
.grim-voie-axes { display: flex; flex-direction: column; gap: .4rem; margin-bottom: .85rem; }
.grim-axis-row { display: grid; grid-template-columns: 5.5rem 1fr 2.2ch; align-items: center; gap: .55rem; }
.grim-axis-label { font-family: var(--font-body, 'Inter', sans-serif); font-size: 12px; color: var(--text-secondary, #6B5A3E); }
.grim-axis-bar { height: 6px; border-radius: 999px; background: rgba(166,117,32,0.16); overflow: hidden; }
.grim-axis-fill { display: block; height: 100%; border-radius: 999px; background: linear-gradient(90deg, var(--grim-gold), var(--grim-gold-dark)); }
.grim-axis-num { font-family: var(--font-data, monospace); font-size: 11px; color: var(--grim-gold-dark); text-align: right; }

/* Pistes en cours de génération (génération progressive) */
.grim-voies-loading { text-align: center; padding: 3.5rem 1rem; }
.grim-voies-loading-text { font-family: var(--font-body, 'Inter', sans-serif); font-size: 1.05rem; color: var(--grim-ink); margin: 1.2rem 0 .35rem; }
.grim-voies-loading-text strong { color: var(--grim-red); }
.grim-voies-loading-sub { font-family: var(--font-body, 'Inter', sans-serif); font-size: .92rem; color: var(--text-muted, #8C7A5E); margin: 0; }

/* ── Épreuves relues : cartes ─────────────────────────────────────────── */
.grim-tests { margin-bottom: 3rem; }
.grim-tests-list { display: flex; flex-direction: column; gap: .85rem; }
.grim-test-card {
    display: flex;
    flex-wrap: wrap;
    align-items: flex-start;
    justify-content: space-between;
    gap: 1.2rem;
    border: 1px solid var(--border-mid, rgba(166,117,32,0.25));
    border-left: 3px solid var(--grim-gold);
    border-radius: var(--r-lg, 12px);
    padding: 1.25rem 1.4rem;
    background: linear-gradient(180deg, #FBF6EA, #F2E8D1);
    box-shadow: var(--shadow-xs, 0 1px 3px rgba(42,30,8,0.06));
    transition: box-shadow .18s ease;
}
.grim-test-card:hover { box-shadow: var(--shadow-card, 0 2px 12px rgba(42,30,8,0.10)); }
.grim-test-main { flex: 1 1 320px; min-width: 0; }
.grim-test-measures { font-family: var(--font-body, 'Inter', sans-serif); font-size: .82rem; line-height: 1.45; color: var(--color-primary-dark, #7D5010); margin: .15rem 0 .55rem; }
.grim-test-preview {
    font-family: var(--font-body, 'Inter', sans-serif);
    font-size: .98rem;
    line-height: 1.6;
    color: var(--text-secondary, #6B5A3E);
    margin-top: .35rem;
}
.grim-test-preview-label {
    display: block;
    font-family: var(--font-data, monospace);
    font-size: 10px;
    text-transform: uppercase;
    letter-spacing: .1em;
    color: var(--grim-red);
    margin-bottom: .3rem;
}
.grim-test-actions { display: flex; flex-direction: column; gap: .5rem; flex: 0 0 auto; align-items: stretch; }
.grim-test-link {
    font-family: var(--font-display, sans-serif);
    font-size: 13px; font-weight: 500;
    text-align: center;
    color: var(--grim-gold-dark);
    text-decoration: none;
    padding: 7px 16px;
    border: 1px solid var(--grim-gold);
    border-radius: var(--r-sm, 6px);
    background: rgba(166,117,32,0.05);
    transition: background .15s;
}
.grim-test-link:hover { background: rgba(166,117,32,0.14); }
.grim-test-pdf {
    font-family: var(--font-display, sans-serif);
    font-size: 13px; font-weight: 600;
    text-align: center;
    color: #FBF6EA;
    background: linear-gradient(180deg, var(--grim-gold), var(--grim-gold-dark));
    text-decoration: none;
    padding: 8px 16px;
    border-radius: var(--r-sm, 6px);
    box-shadow: inset 0 1px 0 rgba(255,255,255,.25);
    transition: filter .15s;
}
.grim-test-pdf:hover { filter: brightness(1.09); }

.grim-footer { text-align: center; margin-top: 3rem; }
.grim-actions { display: flex; gap: .85rem; justify-content: center; flex-wrap: wrap; margin-top: 1.5rem; }
.grim-disclaimer { font-family: var(--font-body, 'Inter', sans-serif); font-size: 13px; font-style: italic; color: var(--text-muted, #8C7A5E); max-width: 580px; margin: 1.5rem auto 0; line-height: 1.55; }
.grim-regen-error {
    font-family: var(--font-body, 'Inter', sans-serif);
    font-size: .9rem;
    color: var(--grim-red);
    background: rgba(123,21,21,0.07);
    border: 1px solid rgba(123,21,21,0.30);
    border-radius: var(--r, 8px);
    max-width: 520px;
    margin: 1.1rem auto 0;
    padding: .7rem 1rem;
    line-height: 1.5;
}

/* ── Voies vides ─────────────────────────────────────────────────────── */
.grim-voies-empty { text-align: center; padding: 2.5rem 1rem; font-family: var(--font-body, 'Inter', sans-serif); font-size: .97rem; color: var(--text-muted, #8C7A5E); font-style: italic; }

/* ── Picker nombre de pistes ─────────────────────────────────────────── */
.grim-pistes-picker { margin: 2rem auto 0; max-width: 440px; text-align: center; }
.grim-picker-label { display: block; font-family: var(--font-body, 'Inter', sans-serif); font-size: .93rem; color: var(--text-secondary, #6B5A3E); margin-bottom: .75rem; }
.grim-picker-label strong { color: var(--grim-ink); font-size: 1.1rem; }
.grim-picker-range { width: 100%; accent-color: var(--grim-gold); cursor: pointer; }
.grim-picker-bounds { display: flex; justify-content: space-between; font-family: var(--font-data, monospace); font-size: 11px; color: var(--text-muted, #8C7A5E); margin-top: .3rem; }

@media (max-width: 768px) {
    /* Sidebar → bande horizontale compacte */
    .grim-body {
        grid-template-columns: 1fr;
        gap: 0;
    }
    .grim-toc {
        position: static;
        border-right: none;
        border-bottom: 1px solid rgba(166,117,32,0.28);
        padding-right: 0;
        padding-bottom: .75rem;
        margin-bottom: 1.75rem;
        display: flex;
        flex-wrap: wrap;
        gap: 4px;
    }
    .grim-toc-title { display: none; }
    .grim-toc-item {
        width: auto;
        padding: 5px 12px;
        border-right: none;
        border-bottom: 2px solid transparent;
        border-radius: 0;
    }
    .grim-toc-item--active { border-bottom-color: var(--grim-gold); }
    .grim-toc-dots, .grim-toc-pg { display: none; }
    .grim-toc-num { min-width: auto; margin-right: 4px; }

    .grim-scroll { padding: 1.8rem 1.4rem; }
    .grim-scroll :deep(.pt-md p) { text-align: left; }
    .grim-test-actions { flex-direction: row; width: 100%; }
    .grim-test-actions > * { flex: 1; }
    .grim-tuner-grid { grid-template-columns: 1fr; }
}

/* ── Parcours Corporate : sobriété executive ─────────────────────────────
   Fleurons et étoiles masqués, sommaire modernisé (numérotation mono),
   parchemin remplacé par une carte blanche à filet laiton supérieur. */
html[data-theme="corporate"] .grim-flourish { display: none; }
html[data-theme="corporate"] .grim-rule {
    background: var(--border-mid);
    opacity: 1;
}
html[data-theme="corporate"] .grim-rule span { display: none; }
html[data-theme="corporate"] .grim-title {
    letter-spacing: -0.03em;
    text-shadow: none;
}
html[data-theme="corporate"] .grim-toc { border-right-color: var(--border-light); }
html[data-theme="corporate"] .grim-toc-title {
    color: var(--text-muted);
    border-bottom-color: var(--border-light);
}
html[data-theme="corporate"] .grim-toc-num {
    font-family: var(--font-data, monospace);
    font-style: normal;
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 0.06em;
    color: var(--color-primary);
}
html[data-theme="corporate"] .grim-toc-dots { border-bottom-color: var(--border-light); }
html[data-theme="corporate"] .grim-toc-pg { color: var(--text-muted); }
html[data-theme="corporate"] .grim-scroll {
    background: #FFFFFF;
    border: 1px solid var(--border-light);
    border-top: 2px solid var(--color-primary);
    box-shadow: 0 1px 2px rgba(21,34,56,0.03), 0 16px 40px -20px rgba(21,34,56,0.22);
    --pt-gold-pale:   rgba(176,141,63,0.10);
    --pt-gold-border: var(--color-primary);
    --pt-border:      var(--border-light);
}
html[data-theme="corporate"] .grim-chip {
    background: #FFFFFF;
    box-shadow: none;
}
/* Cartes évaluations, tuner et pistes : blanc hairline + filet laiton */
html[data-theme="corporate"] .grim-test-card,
html[data-theme="corporate"] .grim-tuner,
html[data-theme="corporate"] .grim-voie-card {
    background: #FFFFFF;
    border-color: var(--border-light);
    box-shadow: 0 1px 2px rgba(21,34,56,0.03), 0 10px 26px -16px rgba(21,34,56,0.18);
}
html[data-theme="corporate"] .grim-tuner,
html[data-theme="corporate"] .grim-voie-card {
    border-top: 2px solid var(--color-primary);
}
html[data-theme="corporate"] .grim-test-card {
    border-left: 3px solid var(--color-primary);
}
html[data-theme="corporate"] .grim-voie-rank {
    background: var(--bg-surface);
    border: 1px solid var(--border-mid);
    color: var(--color-accent);
}
html[data-theme="corporate"] .grim-test-pdf {
    background: var(--color-accent);
    color: #F5F7FA;
}
html[data-theme="corporate"] .grim-test-link {
    border-color: var(--border-strong);
    color: var(--text-secondary);
}
html[data-theme="corporate"] .grim-test-preview-label,
html[data-theme="corporate"] .grim-voie-next-label {
    color: var(--color-primary-dark);
}
html[data-theme="corporate"] .grim-voie-next { border-top-color: var(--border-light); }
html[data-theme="corporate"] .grim-voie-modele {
    background: rgba(176,141,63,0.10);
}
html[data-theme="corporate"] .voie-fit-mid {
    background: rgba(176,141,63,0.12);
    border-color: rgba(176,141,63,0.35);
}
html[data-theme="corporate"] .grim-voie-plan { border-top-color: var(--border-light); }
html[data-theme="corporate"] .grim-plan-btn {
    background: var(--color-accent);
    color: #F5F7FA;
    border-radius: 6px;
}
html[data-theme="corporate"] .grim-plan-list li::marker { color: var(--color-primary-dark); }
html[data-theme="corporate"] .grim-badge { border-radius: var(--r-sm); }
@media (max-width: 860px) {
    html[data-theme="corporate"] .grim-toc { border-bottom-color: var(--border-light); }
    html[data-theme="corporate"] .grim-toc-item--active { border-bottom-color: var(--color-primary); }
}
</style>
