<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import CandidateLayout from '@/Layouts/CandidateLayout.vue'
import PathTier from '@/Components/PathTier.vue'
import MarkdownText from '@/Components/MarkdownText.vue'

const props = defineProps({
    grimoire:   Object,
    tests:      Array,
    ai_pending: Boolean,
    is_empty:   Boolean,
    pistes:       { type: Object,  default: () => ({ accessible: [], ptp: [], horizon: [] }) },
    ptp_eligible: { type: Boolean, default: false },
})

const voies = computed(() => props.grimoire?.voies ?? [])

// ── Onglets ───────────────────────────────────────────────────────────────
const tabs = [
    { key: 'synthese', label: 'Relecture globale' },
    { key: 'tests',    label: 'Résultats des tests' },
    { key: 'pistes',   label: 'Les 15 pistes' },
]
const activeTab = ref('synthese')

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
const rankedVoies = computed(() => {
    const list = voies.value.map((v, i) => ({ v, i }))
    if (!customized.value || !hasAxes.value) return list.map(x => x.v)
    return list
        .sort((a, b) => prefScore(b.v) - prefScore(a.v) || a.i - b.i)
        .map(x => x.v)
})

// Pistes de transition (PTP) — nombre total tous paliers confondus.
const pistesTotal = computed(() => {
    const p = props.pistes ?? {}
    return (p.accessible?.length ?? 0) + (p.ptp?.length ?? 0) + (p.horizon?.length ?? 0)
})

// Déblocage déclaratif d'une piste (la personne vise/possède la formation).
const declarePiste = (piste) => {
    if (!piste?.id) return
    router.post(route('grimoire.piste.declare', piste.id), {}, {
        preserveScroll: true,
        preserveState: false,
    })
}

// Synthèse normalisée pour MarkdownText.
// Miroir du normalizeSynthesisParagraphs PHP pour couvrir les anciens grimoires.
const synthSource = computed(() => {
    let raw = (props.grimoire?.synthesis || '').trim()
    if (!raw) return ''

    // Dé-escape les séquences littérales "\\n" résiduelles (double-encodage JSON rare).
    raw = raw.replace(/\\n/g, '\n')

    // Cas 1 : \n\n déjà présents → parfait.
    if (raw.includes('\n\n')) return raw

    // Cas 2 : sauts simples → on double.
    if (raw.includes('\n')) {
        raw = raw.replace(/\n{3,}/g, '\n\n')
        raw = raw.replace(/\n(?!\n)/g, '\n\n')
        return raw
    }

    // Cas 3 : bloc monolithique (anciens grimoires sans aucun retour à la ligne).
    // On découpe par phrases puis on regroupe en 3–4 paragraphes équilibrés,
    // comme le fait normalizeSynthesisParagraphs côté PHP.
    const sentences = raw
        .split(/(?<=[.!?])\s+(?=[A-ZÀÂÄÉÈÊËÎÏÔÙÛÜÇ])/)
        .map(s => s.trim())
        .filter(s => s.length >= 40)

    if (sentences.length <= 1) return raw

    const count  = sentences.length
    const target = count <= 5 ? 2 : count <= 9 ? 3 : 4
    const per    = Math.ceil(count / target)
    const paras  = []
    for (let i = 0; i < count; i += per) {
        paras.push(sentences.slice(i, i + per).join(' '))
    }
    return paras.join('\n\n')
})

// Polling de l'état du Grimoire pendant la (re)génération IA.
// IMPORTANT : démarré de façon RÉACTIVE (watch) et pas seulement dans onMounted.
// Après un clic "Régénérer", Inertia recharge la MÊME page sans remonter le
// composant : onMounted ne se rejoue pas. Sans le watch, le polling ne démarrait
// jamais et la page restait figée sur "L'oracle relit tes épreuves…".
let timer = null
let pollCount = ref(0)  // nombre de polls depuis le démarrage

// Affiche un bouton "Réessayer" dans l'écran pending si > 3 min sans réponse.
// Permet à l'utilisateur de sortir manuellement si le job OVH a été tué.
const pendingTooLong = computed(() => pollCount.value >= 36) // 36 × 5s = 3 min

function startPolling() {
    if (timer) return
    pollCount.value = 0
    timer = setInterval(async () => {
        try {
            const r = await fetch(route('grimoire.status'), { headers: { Accept: 'application/json' } })
            const data = await r.json()
            pollCount.value++
            if (data.ready || data.failed || data.stuck) {
                stopPolling()
                // Recharge complète : synthèse, voies, tests et pistes d'un coup.
                // Si stuck, le contrôleur purge le verrou et redispatch automatiquement.
                router.reload()
            }
        } catch (e) { /* retry au prochain tick */ }
    }, 5000)
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

onMounted(() => { if (props.ai_pending) startPolling() })
watch(() => props.ai_pending, (pending) => {
    if (pending) startPolling()
    else stopPolling()
})
onUnmounted(stopPolling)

const refreshing = ref(false)
function regenerate() {
    refreshing.value = true
    window.scrollTo({ top: 0, behavior: 'smooth' })
    router.post(route('grimoire.refresh'), {}, {
        preserveScroll: false,
        onFinish: () => { refreshing.value = false },
    })
}

function fitClass(score) {
    if (score >= 80) return 'voie-fit-high'
    if (score >= 60) return 'voie-fit-mid'
    return 'voie-fit-low'
}
</script>

<template>
    <CandidateLayout>
        <Head title="Le Grimoire" />

        <div class="grim-shell">

            <div v-if="is_empty" class="grim-empty">
                <div class="grim-flourish">&#10087;&nbsp;&nbsp;&#10022;&nbsp;&nbsp;&#10087;</div>
                <h1 class="grim-title">Le Grimoire</h1>
                <div class="grim-rule"><span>&#10022;</span></div>
                <p class="grim-empty-text">
                    Ton Grimoire se remplira au fil de tes épreuves. Passe une première épreuve
                    pour que l'oracle commence à relire ton profil.
                </p>
                <Link :href="route('tests.index')" class="ac-btn-primary">Entrer dans l'Armurerie</Link>
            </div>

            <div v-else-if="ai_pending" class="grim-pending">
                <div class="grim-flourish">&#10087;&nbsp;&nbsp;&#10022;&nbsp;&nbsp;&#10087;</div>
                <div class="grim-pulse-dots"><span></span><span></span><span></span></div>
                <h1 class="grim-title">L'oracle relit tes épreuves…</h1>
                <p class="grim-pending-sub">
                    Croisement de tes {{ tests.length }} épreuve{{ tests.length > 1 ? 's' : '' }} · 1 à 2 minutes
                </p>
                <div class="grim-rule"><span>&#10022;</span></div>
                <!-- Bouton de secours si le job IA a été interrompu côté serveur -->
                <div v-if="pendingTooLong" class="grim-stuck-notice">
                    <p class="grim-stuck-text">La relecture prend plus de temps que prévu.</p>
                    <button class="ac-btn-secondary" @click="retryFromPending">
                        Vérifier et relancer
                    </button>
                </div>
            </div>

            <div v-else class="grim-content">

                <header class="grim-header">
                    <div class="grim-flourish">&#10087;&nbsp;&nbsp;&#10022;&nbsp;&nbsp;&#10087;</div>
                    <h1 class="grim-title">Le Grimoire</h1>
                    <div class="grim-rule"><span>&#10022;</span></div>
                    <p class="grim-sub">
                        Ce que révèle le croisement de
                        <strong>{{ tests.length }}</strong> de tes épreuves.
                    </p>

                </header>

                <!-- ── Barre d'onglets ──────────────────────────────────── -->
                <nav class="grim-tabs" role="tablist">
                    <button
                        v-for="tab in tabs"
                        :key="tab.key"
                        role="tab"
                        :aria-selected="activeTab === tab.key"
                        class="grim-tab"
                        :class="{ 'grim-tab--active': activeTab === tab.key }"
                        @click="activeTab = tab.key"
                    >
                        {{ tab.label }}
                    </button>
                </nav>

                <!-- ── Onglet 1 : Relecture globale ───────────────────── -->
                <div v-show="activeTab === 'synthese'" role="tabpanel">
                    <div v-if="grimoire?.status === 'failed'" class="grim-alert">
                        {{ grimoire.synthesis }}
                    </div>
                    <section v-else class="grim-synthesis">
                        <div class="grim-scroll">
                            <h2 class="grim-scroll-title">Le fil conducteur</h2>
                            <MarkdownText :source="synthSource" />
                        </div>
                    </section>
                </div>

                <!-- ── Onglet 2 : Résultats des tests ────────────────── -->
                <div v-show="activeTab === 'tests'" role="tabpanel">
                    <section v-if="tests.length" class="grim-tests">
                        <div class="grim-section-head">
                            <h2 class="grim-section-title">Tes épreuves relues</h2>
                            <p class="grim-voies-intro">
                                Le résumé de chacune de tes épreuves. Télécharge le détail complet en PDF.
                            </p>
                        </div>
                        <div class="grim-tests-list">
                            <article v-for="t in tests" :key="t.attempt_id" class="grim-test-card">
                                <div class="grim-test-main">
                                    <h3 class="grim-test-name">{{ t.name }}</h3>
                                    <p v-if="t.summary" class="grim-test-summary">{{ t.summary }}</p>
                                    <p v-else class="grim-test-summary grim-test-pending">
                                        Synthèse en cours de génération…
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

                <!-- ── Onglet 3 : Les 15 pistes ──────────────────────── -->
                <div v-show="activeTab === 'pistes'" role="tabpanel">
                    <section v-if="voies.length" class="grim-voies">
                        <div class="grim-section-head">
                            <h2 class="grim-section-title">Tes Voies Possibles</h2>
                            <p class="grim-voies-intro">
                                {{ voies.length }} pistes tracées en croisant l'ensemble de tes résultats.
                            </p>
                        </div>

                        <!-- ── Curseurs de préférences : re-trie les voies en direct ── -->
                        <div v-if="hasAxes" class="grim-tuner">
                            <div class="grim-tuner-head">
                                <h3 class="grim-tuner-title">Ajuste selon ce qui compte pour toi</h3>
                                <button v-if="customized" type="button" class="grim-tuner-reset" @click="resetWeights">
                                    Réinitialiser
                                </button>
                            </div>
                            <p class="grim-tuner-sub">
                                Déplace les curseurs : tes voies se réordonnent instantanément selon tes priorités.
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
                            <article v-for="(v, i) in rankedVoies" :key="v.titre || i" class="grim-voie-card">
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
                                <p v-if="v.secteur" class="grim-voie-secteur">{{ v.secteur }}</p>
                                <p v-if="v.pourquoi" class="grim-voie-why">{{ v.pourquoi }}</p>

                                <div v-if="v.appui_tests?.length" class="grim-voie-appui">
                                    <span class="grim-voie-appui-label">Appuyé par</span>
                                    <span v-for="(t, j) in v.appui_tests" :key="j" class="grim-appui-tag">{{ t }}</span>
                                </div>

                                <p v-if="v.prochaine_etape" class="grim-voie-next">
                                    <span class="grim-voie-next-label">Prochaine étape</span>
                                    {{ v.prochaine_etape }}
                                </p>
                            </article>
                        </div>
                    </section>

                    <!-- Pistes de transition (PTP) — données dispo, affichage optionnel -->
                    <section v-if="pistesTotal" class="grim-voies" style="margin-top:1rem">
                        <div class="grim-section-head">
                            <h2 class="grim-section-title">Tes pistes de transition</h2>
                            <p class="grim-voies-intro">
                                Des métiers cibles classés par opportunité, en croisant ton profil, l'écart de
                                formation et le marché de l'emploi.
                            </p>
                            <p v-if="!ptp_eligible" class="grim-voies-intro" style="font-style:italic;margin-top:.4rem">
                                Le financement via un PTP concerne les salariés. Selon ton statut, d'autres
                                dispositifs (CPF, AIF…) peuvent s'appliquer.
                            </p>
                        </div>

                        <PathTier tier="accessible" :paths="pistes.accessible" @unlock="declarePiste" />
                        <PathTier tier="ptp"        :paths="pistes.ptp"        @unlock="declarePiste" />
                        <PathTier tier="horizon"    :paths="pistes.horizon"    @unlock="declarePiste" />

                        <p class="grim-voies-intro" style="font-size:12px;margin-top:.75rem">
                            Données marché indicatives ({{ new Date().getFullYear() }}) — à affiner avec un conseiller.
                        </p>
                    </section>
                </div>

                <footer class="grim-footer">
                    <div class="grim-rule"><span>&#10022;</span></div>
                    <div class="grim-actions">
                        <a v-if="grimoire?.status === 'ready'" :href="route('grimoire.pdf')" class="ac-btn-primary">
                            Télécharger en PDF
                        </a>
                        <button class="ac-btn-secondary" :disabled="refreshing" @click="regenerate">
                            {{ refreshing ? 'Relecture en cours…' : 'Régénérer le Grimoire' }}
                        </button>
                    </div>
                    <p v-if="grimoire?.disclaimer" class="grim-disclaimer">
                        {{ grimoire.disclaimer.disclaimer_text }}
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

/* ── Onglets ──────────────────────────────────────────────────────────── */
.grim-tabs {
    display: flex;
    gap: 0;
    border-bottom: 2px solid var(--border-mid, rgba(166,117,32,0.25));
    margin-bottom: 2.5rem;
    overflow-x: auto;
    scrollbar-width: none;
}
.grim-tabs::-webkit-scrollbar { display: none; }

.grim-tab {
    font-family: var(--font-display, 'Space Grotesk', sans-serif);
    font-size: .95rem;
    font-weight: 500;
    color: var(--text-secondary, #6B5A3E);
    background: transparent;
    border: none;
    border-bottom: 2px solid transparent;
    margin-bottom: -2px;
    padding: .75rem 1.5rem;
    cursor: pointer;
    white-space: nowrap;
    transition: color .15s, border-color .15s;
}
.grim-tab:hover { color: var(--grim-gold-dark); }
.grim-tab--active {
    color: var(--grim-gold-dark);
    font-weight: 700;
    border-bottom-color: var(--grim-gold);
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
.grim-test-name { font-family: var(--font-display, 'Space Grotesk', sans-serif); font-size: 1.08rem; font-weight: 600; color: var(--grim-ink); margin-bottom: .45rem; }
.grim-test-summary { font-family: var(--font-body, 'Inter', sans-serif); font-size: .98rem; line-height: 1.6; color: var(--text-secondary, #6B5A3E); }
.grim-test-pending { font-style: italic; color: var(--text-muted, #8C7A5E); }
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

@media (max-width: 640px) {
    .grim-scroll { padding: 1.8rem 1.4rem; }
    .grim-scroll :deep(.pt-md p) { text-align: left; }
    .grim-test-actions { flex-direction: row; width: 100%; }
    .grim-test-actions > * { flex: 1; }
    .grim-tuner-grid { grid-template-columns: 1fr; }
}
</style>
