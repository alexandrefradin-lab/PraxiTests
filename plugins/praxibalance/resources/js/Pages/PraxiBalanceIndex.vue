<script setup>
/**
 * La Balance — apprentissage de l'arbitrage par cartes glissées.
 *
 * Le déroulé d'une session vit ici : le geste doit rester immédiat, un
 * aller-retour serveur par carte fausserait la mesure du temps de réaction.
 * Le serveur reste maître de l'ancrage, de la validation des niveaux et des
 * Éclats : on lui transmet le résultat en fin de session, il recalcule tout.
 */
import { ref, computed, onMounted, onBeforeUnmount, nextTick } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import CandidateLayout from '@/Layouts/CandidateLayout.vue'
import { useParcours } from '@/composables/useParcours'

const { isCorporate, vouvoyer } = useParcours()
const appName = computed(() => (isCorporate.value ? 'Priorités & arbitrage' : 'La Balance'))

const props = defineProps({
    appDescription: { type: String, default: null },
    levels:  { type: Array,  default: () => [] },
    notions: { type: Array,  default: () => [] },
    prompts: { type: Object, default: () => ({ power: {}, resets: [] }) },
    profile: { type: Object, default: () => ({}) },
    tasks:   { type: Array,  default: () => [] },
    config:  { type: Object, default: () => ({ max_review: 4, max_box: 4 }) },
})

/* ─── État d'écran ──────────────────────────────────────────────────────── */
const view  = ref('home')          // home | play | result
const run   = ref(null)            // session en cours
const inter = ref(null)            // interstitiel affiché par-dessus la session
const fb    = ref(null)            // panneau de règle, après une carte de connaissance
const result = ref(null)
const saving = ref(false)

const BLOCK_FAILS = 2              // échecs sur un thème avant la question puissante
const MAX_BOOST   = 2              // cartes de déblocage par session

/* ─── Tableau de bord ───────────────────────────────────────────────────── */
const anchorPct = computed(() => props.profile.anchor_pct ?? 0)
const ringDash  = computed(() => 238.8 * (1 - anchorPct.value / 100))
const meanRt    = computed(() => props.profile.mean_rt_ms)

/* ─── Construction d'une session ────────────────────────────────────────── */
const shuffle = (a) => {
    const out = a.slice()
    for (let i = out.length - 1; i > 0; i--) {
        const j = Math.floor(Math.random() * (i + 1))
        ;[out[i], out[j]] = [out[j], out[i]]
    }
    return out
}

/** Sert la formulation suivante d'une notion : jamais deux fois la même phrase. */
const variantOf = (notion, isReview) => {
    const vi = (notion.variant + 1) % notion.variants.length
    const [text, answer] = notion.variants[vi]
    return {
        nid: notion.id,
        theme: notion.theme,
        explanation: notion.explanation,
        text,
        answer,
        variant: vi,
        review: !!isReview,
    }
}

/** Une tâche satisfait-elle tous les critères demandés ? */
const matches = (task, criteria) => criteria.every(c => task[c] === true)

/**
 * Tire la série d'une épreuve chronométrée dans la banque de tâches.
 *
 * On vise la proportion de cibles annoncée, sans jamais servir deux fois la
 * même tâche tant que le vivier n'est pas épuisé : revoir une carte déjà vue
 * transformerait l'arbitrage en exercice de mémoire.
 */
const buildStimuli = (cfg) => {
    // Le niveau 4 change de critère en cours de route : une carte doit rester
    // pertinente pour les deux consignes, on tire donc sur le critère initial.
    const cibles  = shuffle(props.tasks.filter(t => matches(t, cfg.criteria)))
    const autres  = shuffle(props.tasks.filter(t => !matches(t, cfg.criteria)))
    const voulues = Math.round(cfg.count * cfg.target_ratio)

    const pioche = (source, combien) => {
        const out = []
        while (out.length < combien && source.length) out.push(source.pop())
        return out
    }

    const serie = [
        ...pioche(cibles, voulues),
        ...pioche(autres, cfg.count - voulues),
    ]

    // Vivier trop court pour la longueur demandée : on complète en repassant
    // sur des tâches déjà tirées plutôt que de raccourcir la série.
    while (serie.length < cfg.count) {
        serie.push(props.tasks[Math.floor(Math.random() * props.tasks.length)])
    }

    return shuffle(serie).map(task => ({ task }))
}

const startLevel = (level) => {
    if (!level.unlocked) return

    const isKnowledge = level.type === 'knowledge'
    let queue = []

    if (isKnowledge) {
        // Ouverture par les notions dues des autres blocs, reformulées.
        const own    = props.notions.filter(n => n.level === level.id)
        const review = props.notions
            .filter(n => n.due && n.level !== level.id)
            .slice(0, props.config.max_review)
            .map(n => variantOf(n, true))

        queue = [...review, ...shuffle(own).map(n => variantOf(n, false))]
    } else {
        queue = buildStimuli(level.training)
    }

    run.value = {
        level,
        isKnowledge,
        queue,
        fresh: queue.length,          // dénominateur du score : les 1res présentations
        i: 0,
        right: 0,
        firstTry: 0,
        combo: 0,
        bestCombo: 0,
        inverted: false,
        locked: false,
        seen: [],                     // notions gradées, transmises au serveur
        rts: [],
        errors: 0,
        themeFails: {},
        boosted: {},
        boosts: 0,
        pending: null,
        last6: [],
        drag: 0,
        stamp: null,
    }

    fb.value = null
    result.value = null
    view.value = 'play'

    if (isKnowledge) nextCard()
    else showInter({ ...level.training.intro, lab: 'Entraînement', cta: "C'est parti" }, nextCard)
}

/* ─── Déroulé ───────────────────────────────────────────────────────────── */
const card = computed(() => (run.value ? run.value.queue[run.value.i] ?? null : null))

const labels = computed(() => {
    if (!run.value) return ['VRAI', 'FAUX']
    if (run.value.isKnowledge) return ['VRAI', 'FAUX']
    const t = run.value.level.training
    return run.value.inverted ? (t.labels_inverted ?? t.labels) : t.labels
})

const ruleText = computed(() => {
    if (!run.value) return ''
    if (run.value.isKnowledge) return 'Cette affirmation est-elle <b>vraie</b> ou <b>fausse</b> ?'
    const t = run.value.level.training
    return run.value.inverted ? t.rule_inverted : t.rule
})

let timerId = null
const timerPct = ref(100)

const clearTimer = () => {
    if (timerId) { cancelAnimationFrame(timerId); timerId = null }
}

const startTimer = (ms) => {
    const end = performance.now() + ms
    const loop = () => {
        const left = end - performance.now()
        timerPct.value = Math.max(0, (left / ms) * 100)
        if (left <= 0) { run.value.errors++; answer(null); return }
        timerId = requestAnimationFrame(loop)
    }
    timerId = requestAnimationFrame(loop)
}

let shownAt = 0

const nextCard = () => {
    clearTimer()
    const r = run.value
    if (!r) return

    // Une carte de déblocage attend : elle passe entre deux swipes.
    if (r.pending) {
        const p = r.pending
        r.pending = null
        showInter(p, nextCard)
        return
    }

    if (r.i >= r.queue.length) { finish(); return }

    // Inversion de consigne en cours de série.
    const t = r.level.training
    if (!r.isKnowledge && t?.invert_at && r.i === t.invert_at && !r.inverted) {
        r.inverted = true
        showInter({
            lab: 'Consigne', icon: '🔄', title: 'La règle change',
            text: 'À partir de maintenant : glisse à droite sur tout SAUF les disques.',
            cta: 'Compris',
        }, nextCard)
        return
    }

    r.locked = false
    r.drag = 0
    r.stamp = null
    shownAt = performance.now()

    if (!r.isKnowledge) startTimer(t.time_ms)
}

const answer = (dir) => {
    const r = run.value
    if (!r || r.locked) return
    r.locked = true
    clearTimer()

    const item = r.queue[r.i]
    let ok

    if (r.isKnowledge) {
        ok = dir === item.answer
        r.seen.push({ id: item.nid, correct: ok, variant: item.variant })

        if (ok) {
            if (!item.requeued) r.firstTry++
        } else {
            checkBlocked(item.theme)
            // La notion ratée revient plus loin, sous une autre formulation.
            if (!item.requeued) {
                const src = props.notions.find(n => n.id === item.nid)
                const again = variantOf({ ...src, variant: item.variant }, true)
                again.requeued = true
                r.queue.splice(Math.min(r.i + 3, r.queue.length), 0, again)
            }
        }
    } else {
        const t = r.level.training
        // Apres bascule, le niveau 4 trie sur un autre critere — et non sur
        // l'inverse du precedent : urgent puis important, ce sont deux questions.
        const criteres = r.inverted ? (t.criteria_after ?? t.criteria) : t.criteria
        const expected = matches(item.task, criteres)

        ok = dir === expected
        if (dir !== null) {
            r.rts.push(Math.round(performance.now() - shownAt))
            if (!ok) r.errors++
        }
        r.last6.push(ok ? 1 : 0)
        if (r.last6.length > 6) r.last6.shift()
        checkDrifting()
    }

    if (ok) {
        r.right++
        r.combo++
        r.bestCombo = Math.max(r.bestCombo, r.combo)
    } else {
        r.combo = 0
    }

    r.stamp = { side: dir === null ? (Math.random() < 0.5) : dir, ok }

    if (r.isKnowledge) {
        setTimeout(() => { fb.value = { ok, item } }, 220)
    } else {
        setTimeout(() => { r.i++; nextCard() }, 300)
    }
}

const closeFeedback = () => {
    fb.value = null
    run.value.i++
    nextCard()
}

/* ─── Déblocage ─────────────────────────────────────────────────────────── */
const checkBlocked = (theme) => {
    const r = run.value
    r.themeFails[theme] = (r.themeFails[theme] || 0) + 1
    if (r.themeFails[theme] < BLOCK_FAILS) return
    if (r.boosted[theme] || r.boosts >= MAX_BOOST) return

    const p = props.prompts.power?.[theme]
    if (!p) return

    r.boosted[theme] = true
    r.boosts++
    r.pending = {
        lab: 'Question puissante', icon: '💭',
        title: p.question, text: p.relance,
        cta: "J'y ai réfléchi", power: true,
    }
}

const checkDrifting = () => {
    const r = run.value
    if (r.last6.length < 6 || r.boosts >= MAX_BOOST) return
    if (r.last6.filter(v => !v).length < 3) return

    const list = props.prompts.resets ?? []
    if (!list.length) return

    const reset = list[r.boosts % list.length]
    r.boosts++
    r.last6 = []
    r.pending = {
        lab: 'Recentrage', icon: '🌬️',
        title: reset.question, text: reset.relance,
        cta: 'Je repars', power: true,
    }
}

/* ─── Interstitiels ─────────────────────────────────────────────────────── */
let interCb = null
const showInter = (cfg, cb) => { clearTimer(); inter.value = cfg; interCb = cb }
const closeInter = () => { inter.value = null; const cb = interCb; interCb = null; if (cb) cb() }

/* ─── Fin de session ────────────────────────────────────────────────────── */
const finish = () => {
    clearTimer()
    const r = run.value

    const score = r.isKnowledge
        ? Math.round((r.firstTry / r.fresh) * 100)
        : Math.round((r.right / r.queue.length) * 100)

    const passed = score >= r.level.pass
    const rt = r.rts.length ? Math.round(r.rts.reduce((a, b) => a + b, 0) / r.rts.length) : null

    result.value = { level: r.level, score, passed, rt, bestCombo: r.bestCombo, errors: r.errors }
    view.value = 'result'

    saving.value = true
    router.post('/la-balance/session', {
        level: r.level.id,
        score,
        notions: r.seen,
        reaction_times: r.rts,
    }, {
        preserveScroll: true,
        onFinish: () => { saving.value = false },
    })
}

/* ─── Glisser / clavier ─────────────────────────────────────────────────── */
const cardEl = ref(null)
let x0 = 0, down = false, viaTouch = false

const startDrag = (x) => {
    if (!run.value || run.value.locked) return
    down = true
    x0 = x
    run.value.drag = 0
}
const moveDrag = (x) => {
    if (!down) return
    run.value.drag = x - x0
}

/* Tactile : événements natifs, avec preventDefault sur le déplacement (le
   modificateur .prevent du template). Les Pointer Events seuls ne suffisent
   pas sur mobile — selon le navigateur, le geste peut être requalifié en
   défilement et le pointeur annulé en cours de route. */
const onTouchStart = (e) => { viaTouch = true; startDrag(e.touches[0].clientX) }
const onTouchMove  = (e) => { if (down) moveDrag(e.touches[0].clientX) }

/* Souris et stylet. Ignorés quand le geste vient du doigt : certains
   navigateurs émettent les deux familles d'événements pour un même contact. */
const onDown = (e) => {
    if (viaTouch || e.pointerType === 'touch') return
    startDrag(e.clientX)
    try { e.currentTarget.setPointerCapture(e.pointerId) } catch (err) { /* pointeur déjà relâché */ }
}
const onMove = (e) => {
    if (viaTouch || e.pointerType === 'touch') return
    moveDrag(e.clientX)
}
const onUp = () => {
    if (!down) return
    down = false
    const dx = run.value.drag
    if (Math.abs(dx) > 90) answer(dx > 0)
    else run.value.drag = 0
}

const cardStyle = computed(() => {
    const r = run.value
    if (!r) return {}
    if (r.stamp) {
        return {
            transform: `translateX(${r.stamp.side ? 520 : -520}px) rotate(${r.stamp.side ? 24 : -24}deg)`,
            opacity: 0,
            transition: 'transform .32s ease-out, opacity .32s',
        }
    }
    return {
        transform: `translateX(${r.drag}px) rotate(${r.drag * 0.055}deg)`,
        transition: down ? 'none' : 'transform .25s cubic-bezier(.2,1.2,.4,1)',
    }
})

const onKey = (e) => {
    if (view.value !== 'play') return
    if (inter.value) { if (e.key === 'Enter' || e.key === ' ') closeInter(); return }
    if (fb.value)    { if (e.key === 'Enter' || e.key === ' ') closeFeedback(); return }
    if (e.key === 'ArrowRight') answer(true)
    if (e.key === 'ArrowLeft')  answer(false)
}

onMounted(() => document.addEventListener('keydown', onKey))
onBeforeUnmount(() => { document.removeEventListener('keydown', onKey); clearTimer() })

/* ─── Retour à l'accueil : on recharge la progression serveur ───────────── */
const goHome = () => {
    view.value = 'home'
    run.value = null
    router.reload({ only: ['notions', 'profile', 'levels'] })
}

</script>

<template>
    <Head :title="appName" />

    <CandidateLayout>
        <div class="guet">

            <!-- ═══════════ ACCUEIL ═══════════ -->
            <section v-if="view === 'home'" class="ac-fade-in">
                <header class="bal-head">
                    <div>
                        <span class="ac-section-label">{{ isCorporate ? 'Module' : 'Quête' }}</span>
                        <h1>{{ appName }}</h1>
                        <p v-if="appDescription" class="bal-lede">{{ vouvoyer(appDescription) }}</p>
                    </div>
                </header>

                <div class="bal-dash ac-card">
                    <div class="bal-ring">
                        <svg viewBox="0 0 88 88" aria-hidden="true">
                            <circle cx="44" cy="44" r="38" fill="none" stroke="var(--bg-elevated)" stroke-width="7" />
                            <circle cx="44" cy="44" r="38" fill="none" stroke="var(--color-primary)" stroke-width="7"
                                    stroke-linecap="round" stroke-dasharray="238.8" :stroke-dashoffset="ringDash"
                                    transform="rotate(-90 44 44)" style="transition:stroke-dashoffset .7s" />
                        </svg>
                        <div class="bal-ring-val">
                            {{ anchorPct }}%
                            <small>ancrage</small>
                        </div>
                    </div>
                    <dl class="bal-kpis">
                        <div><dt>Sessions</dt><dd>{{ profile.sessions ?? 0 }}</dd></div>
                        <div><dt>Rang</dt><dd>{{ profile.rank }}</dd></div>
                        <div><dt>Réflexe</dt><dd>{{ meanRt ? meanRt + ' ms' : '—' }}</dd></div>
                        <div><dt>À réancrer</dt><dd>{{ profile.due_count ?? 0 }}</dd></div>
                    </dl>
                </div>

                <p v-if="profile.due_count" class="bal-recall">
                    <i class="ti ti-refresh" aria-hidden="true"></i>
                    {{ profile.due_count }} notion{{ profile.due_count > 1 ? 's' : '' }} à réancrer —
                    {{ vouvoyer("elles ouvriront ta prochaine session, reformulées.") }}
                </p>

                <ul class="bal-levels">
                    <li v-for="lvl in levels" :key="lvl.id">
                        <button type="button" class="bal-level ac-card"
                                :class="{ 'is-locked': !lvl.unlocked, 'is-done': lvl.completed, 'is-training': lvl.type === 'training' }"
                                :disabled="!lvl.unlocked" @click="startLevel(lvl)">
                            <span class="bal-level-num">{{ lvl.completed ? '✓' : lvl.id }}</span>
                            <span class="bal-level-txt">
                                <b>{{ lvl.title }}</b>
                                <small>{{ lvl.desc }}</small>
                            </span>
                            <span v-if="!lvl.unlocked" class="bal-level-lock"><i class="ti ti-lock" aria-hidden="true"></i></span>
                            <span v-else-if="lvl.best_score" class="bal-level-score">{{ lvl.best_score }}%</span>
                        </button>
                    </li>
                </ul>

                <p class="bal-disclaimer">
                    Module ludique de sensibilisation. Le « réflexe » et les scores sont des indicateurs
                    de jeu, pas une mesure clinique ni un test psychométrique étalonné.
                </p>
            </section>

            <!-- ═══════════ SESSION ═══════════ -->
            <section v-else-if="view === 'play'" class="bal-play">
                <div class="bal-play-top">
                    <button type="button" class="ac-btn-ghost" @click="goHome">← Quitter</button>
                    <span class="ac-section-label">Niveau {{ run.level.id }} · {{ run.level.title }}</span>
                </div>

                <div class="ac-progress-track">
                    <div class="ac-progress-fill" :style="{ width: (run.i / run.queue.length * 100) + '%' }"></div>
                </div>
                <p class="bal-meta">
                    <span>{{ Math.min(run.i + 1, run.queue.length) }} / {{ run.queue.length }}</span>
                    <span v-if="run.combo >= 3" class="bal-combo">série de {{ run.combo }}</span>
                </p>

                <p class="bal-rule" v-html="ruleText"></p>

                <div class="bal-stage">
                    <article v-if="card" ref="cardEl" class="bal-card ac-card-ornate" :style="cardStyle"
                             @touchstart.passive="onTouchStart" @touchmove.prevent="onTouchMove"
                             @touchend="onUp" @touchcancel="onUp"
                             @pointerdown="onDown" @pointermove="onMove" @pointerup="onUp" @pointercancel="onUp">
                        <span v-if="card.review" class="bal-tag">Ancrage</span>

                        <span class="bal-stamp is-yes" :style="{ opacity: run.drag > 25 ? Math.min(1, (run.drag - 25) / 70) : 0 }">
                            {{ labels[0] }}
                        </span>
                        <span class="bal-stamp is-no" :style="{ opacity: run.drag < -25 ? Math.min(1, (-run.drag - 25) / 70) : 0 }">
                            {{ labels[1] }}
                        </span>

                        <p v-if="run.isKnowledge" class="bal-q">{{ vouvoyer(card.text) }}</p>

                        <p v-else class="bal-task">{{ vouvoyer(card.task.text) }}</p>
                    </article>
                </div>

                <div v-if="!run.isKnowledge" class="bal-chrono">
                    <i :style="{ width: timerPct + '%' }"></i>
                </div>

                <div class="bal-actions">
                    <button type="button" class="bal-act is-no" :aria-label="'Glisser à gauche : ' + labels[1]" @click="answer(false)">
                        <i class="ti ti-x" aria-hidden="true"></i>
                        <small>{{ labels[1] }}</small>
                    </button>
                    <button type="button" class="bal-act is-yes" :aria-label="'Glisser à droite : ' + labels[0]" @click="answer(true)">
                        <i class="ti ti-check" aria-hidden="true"></i>
                        <small>{{ labels[0] }}</small>
                    </button>
                </div>
                <p class="bal-hint">Glisse la carte · ou utilise ← et →</p>

                <!-- Règle expliquée après chaque carte, et interstitiels.
                     Téléportés dans <body> : en position fixe à l'intérieur du
                     gabarit candidat, ils se retrouvaient sous ses barres (z-index
                     jusqu'à 10000) ou repositionnés par un ancêtre transformé —
                     invisibles sur mobile, donc plus moyen de continuer. -->
                <Teleport to="body">
                    <div v-if="fb" class="bal-fb ac-card" role="status" aria-live="polite">
                        <h2 :class="fb.ok ? 'is-ok' : 'is-ko'">{{ fb.ok ? '✓ Exact' : '✕ Raté' }}</h2>
                        <p>
                            <b>{{ fb.item.answer ? 'VRAI.' : 'FAUX.' }}</b>
                            <span v-html="vouvoyer(fb.item.explanation)"></span>
                        </p>
                        <button type="button" class="ac-btn-primary" @click="closeFeedback">Continuer</button>
                    </div>

                    <div v-if="inter" class="bal-inter" :class="{ 'is-power': inter.power }">
                        <span class="bal-inter-lab">{{ inter.lab }}</span>
                        <span class="bal-inter-icon" aria-hidden="true">{{ inter.icon }}</span>
                        <h2>{{ vouvoyer(inter.title) }}</h2>
                        <p>{{ vouvoyer(inter.text) }}</p>
                        <button type="button" class="ac-btn-primary" @click="closeInter">{{ inter.cta }}</button>
                    </div>
                </Teleport>
            </section>

            <!-- ═══════════ RÉSULTAT ═══════════ -->
            <section v-else class="bal-result ac-fade-in">
                <div class="bal-seal" :class="{ 'is-fail': !result.passed }" aria-hidden="true">
                    {{ result.passed ? (result.level.type === 'knowledge' ? '🧠' : '⚡') : '↻' }}
                </div>

                <span class="ac-section-label">
                    {{ result.passed ? 'Rang débloqué · ' + result.level.rank : 'Niveau non validé' }}
                </span>
                <h1>{{ result.passed ? 'Niveau validé' : 'Presque' }}</h1>

                <p class="bal-result-sub">
                    <template v-if="result.passed && result.level.type === 'knowledge'">
                        {{ vouvoyer("Les notions ratées reviendront dans une prochaine session, sous une autre formulation.") }}
                    </template>
                    <template v-else-if="result.passed">
                        Réflexe et inhibition tenus. Le niveau suivant est ouvert.
                    </template>
                    <template v-else>
                        Il faut {{ result.level.pass }} % pour valider.
                        {{ vouvoyer("Relance le niveau : les notions ratées sont déjà programmées pour revenir.") }}
                    </template>
                </p>

                <dl class="bal-stats">
                    <div><dt>{{ result.level.type === 'knowledge' ? 'Du 1er coup' : 'Justesse' }}</dt><dd>{{ result.score }}%</dd></div>
                    <div v-if="result.level.type === 'knowledge'"><dt>Meilleure série</dt><dd>{{ result.bestCombo }}</dd></div>
                    <div v-else><dt>Réflexe</dt><dd>{{ result.rt ? result.rt + ' ms' : '—' }}</dd></div>
                    <div><dt>Erreurs</dt><dd>{{ result.errors }}</dd></div>
                </dl>

                <p v-if="saving" class="bal-saving">Enregistrement de la progression…</p>

                <div class="bal-result-actions">
                    <button type="button" class="ac-btn-primary" @click="goHome">Retour aux niveaux</button>
                </div>
            </section>

        </div>
    </CandidateLayout>
</template>

<style scoped>
/* overflow-x clip : la carte est ejectee a 520px hors du cadre. Sur un
   ecran etroit cela creait un debordement horizontal qui decalait la vue,
   et le panneau de reponse partait hors champ. clip plutot que hidden :
   hidden ferait de ce bloc un conteneur de defilement. */
.guet { max-width: 560px; margin: 0 auto; padding: 8px 0 40px; overflow-x: clip; }

/* ── accueil ── */
.bal-head h1 { font-size: 27px; margin: 3px 0 0; letter-spacing: -.02em; text-wrap: balance; }
.bal-lede {
    color: var(--text-secondary); font-size: 14.5px; margin: 9px 0 0; line-height: 1.65;
    max-width: 58ch; text-wrap: pretty;
}

/* Tableau de bord : l'anneau et les quatre indicateurs forment un seul cadran.
   Les filets separent les cellules — sans eux les valeurs courtes (« 13 »)
   flottent dans le vide et la carte parait bancale. */
.bal-dash { display: grid; grid-template-columns: auto 1fr; align-items: center; gap: 18px; padding: 14px 16px; margin-top: 22px; }
.bal-ring { position: relative; width: 88px; height: 88px; flex: none; }
.bal-ring svg { display: block; width: 100%; height: 100%; }
.bal-ring-val {
    position: absolute; inset: 0; display: flex; flex-direction: column;
    align-items: center; justify-content: center; line-height: 1;
    font-family: var(--font-display); font-size: 21px; font-weight: 700; font-variant-numeric: tabular-nums;
}
.bal-ring-val small {
    font-family: var(--font-data); font-size: 8px; letter-spacing: .1em;
    text-transform: uppercase; color: var(--text-muted); margin-top: 4px; font-weight: 600;
}
.bal-kpis { display: grid; grid-template-columns: 1fr 1fr; gap: 0; margin: 0; border-left: 1px solid var(--border-light); }
.bal-kpis > div { padding: 9px 12px 9px 16px; min-width: 0; }
.bal-kpis > div:nth-child(even) { border-left: 1px solid var(--border-light); }
.bal-kpis > div:nth-child(n+3) { border-top: 1px solid var(--border-light); }
.bal-kpis dt {
    font-family: var(--font-data); font-size: 9px; letter-spacing: .1em; text-transform: uppercase;
    color: var(--text-muted); font-weight: 600;
}
.bal-kpis dd {
    margin: 2px 0 0; font-family: var(--font-display); font-size: 17px; font-weight: 700;
    line-height: 1.25; font-variant-numeric: tabular-nums; text-wrap: balance;
}

.bal-recall {
    display: flex; gap: 8px; align-items: baseline;
    margin: 14px 0 0; padding: 11px 14px; border-radius: var(--r-lg);
    background: rgba(166, 117, 32, .10); border: 1px solid var(--border-mid);
    font-size: 13px; line-height: 1.5; color: var(--text-secondary);
}
.bal-recall i { color: var(--color-primary-dark); flex: none; }

/* Ecran etroit : l'anneau passe au-dessus, les indicateurs prennent
   toute la largeur — sinon le rang (« Maitre de l'Ancrage ») se casse
   sur trois lignes dans une colonne de 70 px. */
@media (max-width: 430px) {
    .bal-dash { grid-template-columns: 1fr; justify-items: center; gap: 12px; }
    .bal-ring { width: 72px; height: 72px; }
    .bal-ring-val { font-size: 19px; }
    .bal-kpis { width: 100%; border-left: 0; border-top: 1px solid var(--border-light); }
    .bal-kpis > div { padding: 9px 12px; }
    .bal-kpis dd { font-size: 16px; }
}

.bal-levels { list-style: none; margin: 18px 0 0; padding: 0; display: flex; flex-direction: column; gap: 9px; }
.bal-level {
    display: flex; align-items: center; gap: 13px; width: 100%; padding: 12px 14px;
    text-align: left; font-family: inherit; color: inherit; cursor: pointer; transition: .16s;
}
.bal-level:hover:not(:disabled) { border-color: var(--border-strong); transform: translateY(-1px); }
.bal-level.is-locked { opacity: .5; cursor: not-allowed; }
.bal-level-num {
    width: 38px; height: 38px; border-radius: var(--r); display: grid; place-items: center; flex: none;
    font-family: var(--font-display); font-weight: 700; font-size: 15px;
    background: rgba(166, 117, 32, .13); color: var(--color-primary-dark); border: 1px solid var(--border-mid);
}
.bal-level.is-training .bal-level-num {
    background: rgba(123, 21, 21, .09); color: var(--color-secondary); border-color: rgba(123, 21, 21, .24);
}
.bal-level.is-done .bal-level-num { background: var(--color-success); color: #F0E8D4; border-color: transparent; }
.bal-level-txt { flex: 1; min-width: 0; }
.bal-level-txt b { display: block; font-family: var(--font-display); font-size: 14px; }
.bal-level-txt small { font-size: 11.5px; color: var(--text-muted); }
.bal-level-score { font-family: var(--font-data); font-size: 11px; font-weight: 700; color: var(--color-success); }
.bal-level-lock { color: var(--text-ghost); }

.bal-disclaimer { margin-top: 20px; font-size: 11px; color: var(--text-muted); line-height: 1.55; text-align: center; }

/* ── session ── */
.bal-play { position: relative; min-height: 70vh; display: flex; flex-direction: column; }
.bal-play-top { display: flex; align-items: center; gap: 12px; margin-bottom: 14px; }
.bal-meta {
    display: flex; justify-content: space-between; font-family: var(--font-data);
    font-size: 11px; color: var(--text-muted); margin: 7px 0 12px;
}
.bal-combo { color: var(--color-primary-dark); font-weight: 700; }
.bal-rule {
    border: 1px solid var(--border-light); background: var(--bg-elevated); border-radius: var(--r);
    padding: 9px 12px; margin: 0 0 14px; font-size: 13px; text-align: center; color: var(--text-secondary);
}

.bal-stage { flex: 1; display: grid; place-items: center; min-height: 300px; overflow: hidden; }
.bal-card {
    position: relative; width: 100%; max-width: 330px; min-height: 300px;
    border-radius: var(--r-lg); border: 1px solid var(--border-mid);
    box-shadow: var(--shadow-card); padding: 30px 26px;
    display: flex; flex-direction: column; justify-content: center; align-items: center; text-align: center;
    user-select: none; touch-action: none; cursor: grab;
}
.bal-card:active { cursor: grabbing; }
.bal-q { font-family: var(--font-display); font-size: 18px; line-height: 1.4; font-weight: 500; margin: 0; }

/* La tâche à trier : un peu plus petite que les affirmations, souvent plus
   longue, et centrée sur sa propre mesure de ligne. */
.bal-task {
    font-family: var(--font-display); font-size: 16.5px; line-height: 1.45;
    font-weight: 500; margin: 0; max-width: 26ch; text-wrap: balance;
}
.bal-tag {
    position: absolute; top: 14px; left: 14px; font-family: var(--font-data); font-size: 9px;
    letter-spacing: .1em; text-transform: uppercase; font-weight: 700; padding: 4px 9px; border-radius: 99px;
    background: rgba(166, 117, 32, .13); color: var(--color-primary-dark); border: 1px solid var(--border-mid);
}
.bal-stamp {
    position: absolute; top: 20px; font-family: var(--font-display); font-size: 21px; font-weight: 700;
    letter-spacing: .05em; padding: 5px 12px; border-radius: var(--r-sm); border: 2.5px solid;
    background: var(--bg-base); transition: opacity .1s; white-space: nowrap;
}
.bal-stamp.is-yes { right: 16px; color: var(--color-success); border-color: var(--color-success); transform: rotate(10deg); }
.bal-stamp.is-no  { left: 16px;  color: var(--color-danger);  border-color: var(--color-danger);  transform: rotate(-10deg); }

.bal-chrono { height: 4px; border-radius: 99px; background: var(--bg-elevated); overflow: hidden; margin-top: 12px; }
.bal-chrono i { display: block; height: 100%; background: var(--color-secondary); }

.bal-actions { display: flex; gap: 16px; justify-content: center; margin-top: 18px; }
.bal-act {
    width: 58px; height: 58px; border-radius: 50%; border: 2px solid; background: var(--bg-base);
    display: grid; place-items: center; cursor: pointer; position: relative; font-size: 20px;
    box-shadow: var(--shadow-xs); transition: .15s;
}
.bal-act:hover { transform: scale(1.07); }
.bal-act.is-no  { border-color: rgba(176, 48, 32, .45); color: var(--color-danger); }
.bal-act.is-yes { border-color: rgba(58, 107, 72, .45); color: var(--color-success); }
.bal-act small {
    position: absolute; top: 60px; font-family: var(--font-data); font-size: 8.5px;
    letter-spacing: .08em; font-weight: 700; color: var(--text-muted); white-space: nowrap;
}
.bal-hint { text-align: center; font-size: 11px; color: var(--text-ghost); margin-top: 24px; }

.bal-fb {
    position: fixed; left: 50%; bottom: 0; transform: translateX(-50%);
    width: min(560px, 100%); padding: 20px; border-radius: var(--r-xl) var(--r-xl) 0 0; z-index: 10050;
}
.bal-fb h2 { font-size: 16px; margin: 0 0 8px; }
.bal-fb h2.is-ok { color: var(--color-success); }
.bal-fb h2.is-ko { color: var(--color-danger); }
.bal-fb p { font-size: 13.5px; line-height: 1.6; color: var(--text-secondary); margin: 0 0 16px; }
.bal-fb p b { color: var(--text-primary); }
.bal-fb .ac-btn-primary { width: 100%; }

.bal-inter {
    position: fixed; inset: 0; z-index: 10051; background: var(--bg-base);
    display: flex; flex-direction: column; align-items: center; justify-content: center;
    text-align: center; padding: 32px; gap: 10px;
}
.bal-inter-lab {
    font-family: var(--font-data); font-size: 9.5px; letter-spacing: .16em; text-transform: uppercase;
    font-weight: 700; color: var(--color-primary-dark); padding: 5px 12px; border-radius: 99px;
    background: rgba(166, 117, 32, .12); border: 1px solid var(--border-mid);
}
.bal-inter.is-power .bal-inter-lab {
    color: var(--color-secondary); background: rgba(123, 21, 21, .09); border-color: rgba(123, 21, 21, .24);
}
.bal-inter-icon { font-size: 32px; }
.bal-inter h2 { font-size: 21px; margin: 4px 0 0; max-width: 22ch; }
.bal-inter p { font-size: 14px; color: var(--text-secondary); max-width: 34ch; margin: 0 0 14px; line-height: 1.6; }

/* ── résultat ── */
.bal-result { display: flex; flex-direction: column; align-items: center; text-align: center; padding-top: 24px; }
.bal-seal {
    width: 104px; height: 104px; border-radius: 50%; display: grid; place-items: center; font-size: 40px;
    background: linear-gradient(180deg, var(--color-primary-light), var(--color-primary));
    border: 2px solid var(--color-primary-dark); box-shadow: var(--shadow-primary); margin-bottom: 16px;
}
.bal-seal.is-fail { background: var(--bg-elevated); border-color: var(--border-mid); box-shadow: none; }
.bal-result h1 { font-size: 25px; margin: 4px 0 0; }
.bal-result-sub { font-size: 13.5px; color: var(--text-secondary); max-width: 34ch; line-height: 1.6; margin: 10px 0 0; }
.bal-stats { display: grid; grid-template-columns: repeat(3, 1fr); gap: 9px; width: 100%; margin: 22px 0 0; }
.bal-stats div {
    background: var(--bg-surface); border: 1px solid var(--border-light); border-radius: var(--r-lg); padding: 11px 6px;
}
.bal-stats dt {
    font-family: var(--font-data); font-size: 8.5px; letter-spacing: .08em; text-transform: uppercase;
    color: var(--text-muted); font-weight: 600;
}
.bal-stats dd { margin: 2px 0 0; font-family: var(--font-display); font-size: 19px; font-weight: 700; }
.bal-saving { font-size: 12px; color: var(--text-muted); margin-top: 14px; }
.bal-result-actions { margin-top: 22px; width: 100%; }
.bal-result-actions .ac-btn-primary { width: 100%; }

@media (prefers-reduced-motion: reduce) {
    .bal-card, .bal-level, .bal-act { transition: none !important; }
}
</style>
