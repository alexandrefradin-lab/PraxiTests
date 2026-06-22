<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import CandidateLayout from '@/Layouts/CandidateLayout.vue'
import PathTier from '@/Components/PathTier.vue'

const props = defineProps({
    grimoire:   Object,
    tests:      Array,
    ai_pending: Boolean,
    is_empty:   Boolean,
    pistes:       { type: Object,  default: () => ({ accessible: [], ptp: [], horizon: [] }) },
    ptp_eligible: { type: Boolean, default: false },
})

const voies = computed(() => props.grimoire?.voies ?? [])

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

// Aere la synthese en paragraphes lisibles, meme si l'IA renvoie un seul bloc.
const synthParagraphs = computed(() => {
    // Normalise un \n\n echappe ("\\n\\n") qui aurait survecu jusqu'au front.
    const raw = (props.grimoire?.synthesis || '').replace(/\\n/g, '\n').trim()
    if (!raw) return []

    // 1) Si l'IA a fourni de vrais sauts de ligne, on respecte son decoupage.
    const parts = raw.split(/\n{1,}/).map(p => p.trim()).filter(Boolean)
    if (parts.length > 1) return parts

    // 2) Bloc unique : on repartit les phrases en paragraphes EQUILIBRES
    //    (2 a 4 selon la longueur), pour ne jamais laisser un mur de texte.
    const sentences = (parts[0].match(/[^.!?]+[.!?]+["»')\]]*\s*/g) || [parts[0]])
        .map(s => s.trim())
        .filter(Boolean)
    if (sentences.length <= 2) return sentences.length ? [sentences.join(' ')] : []

    const target = sentences.length <= 5 ? 2 : sentences.length <= 9 ? 3 : 4
    const per = Math.ceil(sentences.length / target)
    const out = []
    for (let i = 0; i < sentences.length; i += per) {
        out.push(sentences.slice(i, i + per).join(' ').trim())
    }
    return out.filter(Boolean)
})

// Polling de l'état du Grimoire pendant la (re)génération IA.
// IMPORTANT : démarré de façon RÉACTIVE (watch) et pas seulement dans onMounted.
// Après un clic "Régénérer", Inertia recharge la MÊME page sans remonter le
// composant : onMounted ne se rejoue pas. Sans le watch, le polling ne démarrait
// jamais et la page restait figée sur "L'oracle relit tes épreuves…".
let timer = null
function startPolling() {
    if (timer) return
    timer = setInterval(async () => {
        try {
            const r = await fetch(route('grimoire.status'), { headers: { Accept: 'application/json' } })
            const data = await r.json()
            if (data.ready || data.failed) {
                stopPolling()
                // Recharge complète : synthèse, voies, tests et pistes d'un coup.
                router.reload()
            }
        } catch (e) { /* retry au prochain tick */ }
    }, 5000)
}
function stopPolling() {
    if (timer) { clearInterval(timer); timer = null }
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
    router.post(route('grimoire.refresh'), {}, {
        preserveScroll: true,
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
            </div>

            <div v-else class="grim-content">

                <header class="grim-header">
                    <div class="grim-flourish">&#10087;&nbsp;&nbsp;&#10022;&nbsp;&nbsp;&#10087;</div>
                    <span class="grim-badge">Relecture globale</span>
                    <h1 class="grim-title">Le Grimoire</h1>
                    <div class="grim-rule"><span>&#10022;</span></div>
                    <p class="grim-sub">
                        Ce que révèle le croisement de
                        <strong>{{ tests.length }}</strong> de tes épreuves.
                    </p>
                    <div class="grim-tests-chips">
                        <span v-for="t in tests" :key="t.attempt_id" class="grim-chip">{{ t.name }}</span>
                    </div>
                </header>

                <div v-if="grimoire?.status === 'failed'" class="grim-alert">
                    {{ grimoire.synthesis }}
                </div>

                <section v-else class="grim-synthesis">
                    <div class="grim-scroll">
                        <h2 class="grim-scroll-title">Le fil conducteur</h2>
                        <p v-for="(para, i) in synthParagraphs" :key="i" class="grim-para">{{ para }}</p>
                    </div>
                </section>

                <section v-if="voies.length" class="grim-voies">
                    <div class="grim-section-head">
                        <h2 class="grim-section-title">Tes Voies Possibles</h2>
                        <p class="grim-voies-intro">
                            {{ voies.length }} pistes tracées en croisant l'ensemble de tes résultats.
                        </p>
                    </div>

                    <div class="grim-voies-grid">
                        <article v-for="(v, i) in voies" :key="i" class="grim-voie-card">
                            <div class="grim-voie-head">
                                <span class="grim-voie-rank">{{ i + 1 }}</span>
                                <span v-if="v.fit_score != null" class="grim-voie-fit" :class="fitClass(v.fit_score)">
                                    {{ v.fit_score }}%
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

                <!-- ── Pistes de transition (PTP) ─────────────────────────────
                     Masqué côté front : remplacé par l'Oracle (chat d'orientation).
                     Le calcul backend (PtpPathService) et les données restent en place ;
                     seul l'affichage est désactivé. Réactiver en retirant `false &&`. -->
                <section v-if="false && pistesTotal" class="grim-voies" style="margin-top:1rem">
                    <div class="grim-section-head">
                        <h2 class="grim-section-title">Tes pistes de transition</h2>
                        <p class="grim-voies-intro">
                            Des métiers cibles classés par opportunité, en croisant ton profil, l'écart de
                            formation et le marché de l'emploi. Le score de tes tests ne change pas — ce sont
                            les pistes ouvertes qui évoluent quand tu déclares une formation.
                        </p>
                        <p v-if="!ptp_eligible" class="grim-voies-intro" style="font-style:italic;margin-top:.4rem">
                            Le financement via un PTP (Projet de Transition Professionnelle) concerne les
                            salariés. Selon ton statut, d'autres dispositifs (CPF, AIF…) peuvent s'appliquer.
                        </p>
                    </div>

                    <PathTier tier="accessible" :paths="pistes.accessible" @unlock="declarePiste" />
                    <PathTier tier="ptp"        :paths="pistes.ptp"        @unlock="declarePiste" />
                    <PathTier tier="horizon"    :paths="pistes.horizon"    @unlock="declarePiste" />

                    <p class="grim-voies-intro" style="font-size:12px;margin-top:.75rem">
                        Données marché indicatives (estimations par famille de métiers, {{ new Date().getFullYear() }})
                        — à affiner avec un conseiller en évolution professionnelle.
                    </p>
                </section>

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
.grim-scroll .grim-para:first-of-type::first-letter {
    font-family: var(--font-display, 'Space Grotesk', sans-serif);
    font-weight: 700;
    font-size: 3.1rem;
    line-height: .82;
    float: left;
    padding: .08em .12em 0 0;
    margin-right: .04em;
    color: var(--grim-red);
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
    .grim-para { text-align: left; }
    .grim-test-actions { flex-direction: row; width: 100%; }
    .grim-test-actions > * { flex: 1; }
}
</style>
