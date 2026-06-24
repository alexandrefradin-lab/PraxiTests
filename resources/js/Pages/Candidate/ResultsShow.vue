<script setup>
import { ref, onMounted, watch } from 'vue'
import { Link, Head } from '@inertiajs/vue3'
import CandidateLayout from '@/Layouts/CandidateLayout.vue'
import MarkdownText from '@/Components/MarkdownText.vue'

const props = defineProps({
    attempt: Object,
    result: Object,
    ai_pending: Boolean,
})

const revealed = ref(false)
const ctaVisible = ref(false)

// Respect de l'accessibilité : si l'utilisateur préfère moins d'animations,
// on affiche tout immédiatement (WCAG 2.3.3).
const prefersReducedMotion = typeof window !== 'undefined'
    && window.matchMedia
    && window.matchMedia('(prefers-reduced-motion: reduce)').matches

// ── Dimensions : libellés "parlants" + définitions au clic ──────────────
// Dictionnaire de secours côté front. Si le moteur de scoring renvoie déjà
// result.scoring.dimension_meta[key] = { label, description }, il a priorité.
const DIM_META = {
    // Dimensions génériques (écran actuel)
    role:    { label: 'Posture & Rôle',   description: "Ta capacité à prendre ta place et à assumer un rôle clair : te positionner, porter des responsabilités et agir avec légitimité dans un groupe ou un projet." },
    social:  { label: 'Aisance sociale',  description: "Ton aisance relationnelle : aller vers les autres, coopérer, écouter et créer du lien. Plus le score est élevé, plus tu es à l'aise dans les interactions humaines." },
    values:  { label: 'Valeurs',          description: "L'alignement avec tes valeurs profondes — ce qui compte vraiment pour toi et oriente tes choix. Un score élevé signale une boussole intérieure forte et cohérente." },
    context: { label: 'Lecture du contexte', description: "Ta capacité à capter les enjeux d'une situation, à t'adapter à ton environnement et à ajuster ton comportement selon les circonstances." },

    // RIASEC (Holland)
    r:            { label: 'Réaliste',      description: "Goût du concret, du manuel et du technique : construire, réparer, manipuler des objets ou des machines." },
    realiste:     { label: 'Réaliste',      description: "Goût du concret, du manuel et du technique : construire, réparer, manipuler des objets ou des machines." },
    i:            { label: 'Investigateur', description: "Curiosité intellectuelle et analyse : comprendre, expérimenter et résoudre des problèmes complexes." },
    investigateur:{ label: 'Investigateur', description: "Curiosité intellectuelle et analyse : comprendre, expérimenter et résoudre des problèmes complexes." },
    a:            { label: 'Artistique',    description: "Créativité, expression et originalité : imaginer, créer et sortir des cadres établis." },
    artistique:   { label: 'Artistique',    description: "Créativité, expression et originalité : imaginer, créer et sortir des cadres établis." },
    s:            { label: 'Social',        description: "Aime travailler avec les autres : informer, former, accompagner, soutenir. Profil empathique et coopératif." },
    e:            { label: 'Entreprenant',  description: "Leadership, persuasion et initiative : convaincre, diriger et entreprendre." },
    entreprenant: { label: 'Entreprenant',  description: "Leadership, persuasion et initiative : convaincre, diriger et entreprendre." },
    c:            { label: 'Conventionnel', description: "Organisation, rigueur et méthode : structurer, classer et gérer avec précision." },
    conventionnel:{ label: 'Conventionnel', description: "Organisation, rigueur et méthode : structurer, classer et gérer avec précision." },

    // Compétences relationnelles (PraxiLink)
    ecoute_active:          { label: 'Écoute active',          description: "Ta capacité à écouter vraiment : reformuler, clarifier et montrer à l'autre qu'il est compris." },
    expression_assertive:   { label: 'Expression assertive',   description: "Dire ce que tu penses avec clarté et respect, sans agressivité ni effacement." },
    empathie_relationnelle: { label: 'Empathie',               description: "Percevoir et prendre en compte les émotions et besoins des autres dans la relation." },
    gestion_conflits:       { label: 'Gestion des conflits',   description: "Aborder les désaccords de façon constructive pour chercher une solution gagnant-gagnant." },
    feedback_constructif:   { label: 'Feedback constructif',   description: "Donner un retour précis et bienveillant qui aide l'autre à progresser." },
}

const openDim = ref(null)
function toggleDim(key) {
    openDim.value = openDim.value === key ? null : key
}
function normKey(key) {
    return String(key).toLowerCase().trim().replace(/[\s-]+/g, '_')
}
function dimMeta(key) {
    // Le scoring peut exposer dimension_meta au niveau racine OU sous meta.
    const backend = props.result?.scoring?.dimension_meta?.[key]
        ?? props.result?.scoring?.meta?.dimension_meta?.[key]
    if (backend && (backend.label || backend.description)) return backend
    return DIM_META[normKey(key)] || null
}
function dimLabel(key) {
    const meta = dimMeta(key)
    if (meta?.label) return meta.label
    // Repli : « ecoute_active » → « Ecoute active »
    const pretty = String(key).replace(/[_-]+/g, ' ').trim()
    return pretty.charAt(0).toUpperCase() + pretty.slice(1)
}
function dimDef(key) {
    return dimMeta(key)?.description
        || "Cette dimension mesure une facette de ton profil. Plus la barre est remplie, plus elle te caractérise."
}

// Watch réactif : déclenche le reveal dès que ai_synthesis arrive (SPA + polling).
watch(() => props.result?.ai_synthesis, (val) => {
    if (val) revealed.value = true
}, { immediate: true })

onMounted(() => {
    if (props.ai_pending || !props.result?.ai_synthesis) return

    // Accessibilité / pas de rétention : tout afficher d'emblée.
    if (prefersReducedMotion) { revealed.value = true; ctaVisible.value = true; return }

    // Animation cinématique légère — le téléchargement du PDF est disponible
    // dès le reveal (plus de gating artificiel).
    setTimeout(() => { revealed.value = true; ctaVisible.value = true }, 800)
})
</script>

<template>
    <!-- ═══ CAS AI_PENDING ═══════════════════════════════════════════════ -->
    <div v-if="ai_pending" class="ac-pending-shell">
        <Head title="Révélation en cours…">
            <meta head-key="refresh" http-equiv="refresh" content="10">
        </Head>

        <div class="ac-pending-inner">
            <!-- Ligne décorative -->
            <div class="ac-deco-line"></div>

            <!-- Points pulsants -->
            <div class="ac-pulse-dots">
                <span></span>
                <span></span>
                <span></span>
            </div>

            <h1 class="ac-pending-title">Ton Grimoire se révèle…</h1>
            <p class="ac-pending-sub">L'IA analyse ton Épreuve · 1 à 2 minutes</p>

            <!-- Ligne décorative bas -->
            <div class="ac-deco-line" style="margin-top: 2rem;"></div>
        </div>
    </div>

    <!-- ═══ CAS RÉSULTATS DISPONIBLES ════════════════════════════════════ -->
    <CandidateLayout v-else>
        <Head title="Ta Révélation" />

        <div class="ac-results-shell">

            <!-- ── PHASE CINÉMATIQUE (avant reveal) ────────────────── -->
            <div v-if="!revealed" class="ac-reveal-phase">
                <div class="ac-deco-line"></div>
                <h2 class="ac-reveal-title fade-in">Ton Grimoire se révèle…</h2>
                <div class="ac-deco-line" style="margin-top: 1.5rem;"></div>
            </div>

            <!-- ── CONTENU PRINCIPAL (après reveal) ───────────────── -->
            <div v-show="revealed" class="ac-content fade-in">

                <!-- EN-TÊTE RÉSULTATS -->
                <header class="ac-results-header">
                    <span class="ac-revelation-badge">Révélation</span>
                    <h1 class="ac-results-h1">Voilà ce qui te ressemble.</h1>
                    <p class="ac-results-subtitle">Grimoire de Synthèse personnalisé par l'IA</p>
                </header>

                <!-- ── GRIMOIRE DE SYNTHÈSE ──────────────────────── -->
                <section class="ac-card ac-synthesis-card">
                    <h2 class="ac-card-title">Ton Grimoire de Synthèse</h2>

                    <!-- Synthèse IA rendue en Markdown (source unique) -->
                    <MarkdownText
                        v-if="result.ai_synthesis"
                        :source="result.ai_synthesis"
                        class="ac-synthesis-full"
                    />
                </section>

                <!-- ── DIMENSIONS SCORING ─────────────────────────── -->
                <section v-if="result.scoring?.dimensions" class="ac-card">
                    <h2 class="ac-card-title">Tes Dimensions</h2>
                    <p class="ac-card-hint">Clique sur une dimension pour découvrir ce qu'elle mesure.</p>
                    <div class="ac-dimensions">
                        <div
                            v-for="(dimValue, key) in result.scoring.dimensions"
                            :key="key"
                            class="ac-dimension-row"
                            :class="{ 'is-open': openDim === key }"
                        >
                            <button
                                type="button"
                                class="ac-dimension-header"
                                @click="toggleDim(key)"
                                :aria-expanded="openDim === key"
                            >
                                <span class="ac-dimension-name">
                                    {{ dimLabel(key) }}
                                    <span class="ac-dimension-info" aria-hidden="true">i</span>
                                </span>
                                <span class="ac-dimension-score">{{ dimValue }}/100</span>
                            </button>
                            <div class="ac-progress-track">
                                <div class="ac-progress-fill" :style="{ width: dimValue + '%' }"></div>
                            </div>
                            <transition name="ac-def">
                                <p v-if="openDim === key" class="ac-dimension-def">
                                    {{ dimDef(key) }}
                                </p>
                            </transition>
                        </div>
                    </div>
                </section>

                <!-- ── VOIES POSSIBLES (métiers) ───────────────────── -->
                <section v-if="result.suggested_jobs?.length" class="ac-card">
                    <h2 class="ac-card-title">{{ result.suggested_jobs.length }} Voies Possibles</h2>
                    <div class="ac-jobs-grid">
                        <article
                            v-for="(job, i) in result.suggested_jobs"
                            :key="i"
                            class="ac-job-card"
                        >
                            <!-- Numéro -->
                            <span class="ac-job-num">{{ String(i + 1).padStart(2, '0') }}</span>

                            <!-- Header métier -->
                            <div class="ac-job-header">
                                <h3 class="ac-job-title">{{ job.titre || job.title }}</h3>
                                <span class="ac-job-fit">{{ job.fit_score }}%</span>
                            </div>

                            <!-- Secteur -->
                            <p class="ac-job-sector">{{ job.secteur || job.sector }}</p>

                            <!-- Description -->
                            <p class="ac-job-desc">{{ job.pourquoi || job.why }}</p>

                            <!-- Prochaine étape -->
                            <p v-if="job.prochaine_étape || job.next_step" class="ac-job-step">
                                → {{ job.prochaine_étape || job.next_step }}
                            </p>
                        </article>
                    </div>
                </section>

                <!-- ── DISCLAIMER bienveillant (public en orientation) ── -->
                <p class="ac-disclaimer">
                    À titre indicatif — ces pistes sont un point de départ pour ta
                    réflexion, pas un verdict. Pour aller plus loin, échange avec un
                    conseiller d'orientation ou ton accompagnant France&nbsp;Travail.
                </p>

                <!-- ── CTA PDF (disponible dès le reveal) ─────────────── -->
                <div v-if="ctaVisible" class="ac-cta-pdf fade-in">
                    <p class="ac-cta-label">Ton Grimoire complet t'attend.</p>
                    <a :href="route('results.pdf', attempt.id)" class="ac-btn-primary">
                        Télécharger mon Grimoire de Synthèse (PDF)
                    </a>
                </div>

            </div>
        </div>
    </CandidateLayout>
</template>

<style scoped>
/* ── TOKENS AC ────────────────────────────────── */
.ac-pending-shell,
.ac-results-shell {
    --bg-base:           #F0E8D4;
    --bg-surface:        #E5DAC2;
    --bg-elevated:       #D8CEB5;
    --color-primary:     #A67520;
    --color-primary-dark:#7D5510;
    --color-secondary:   #7B1515;
    --color-accent:      #1C1408;
    --color-success:     #3A6B48;
    --color-danger:      #B03020;
    --color-signal:      #0A7FA0;
    --text-primary:      #2A1E08;
    --text-secondary:    #6B5A3E;
    --glass-bg:          rgba(240,232,212,0.85);
    --glass-border:      rgba(166,117,32,0.25);
    --shadow-card:       0 2px 12px rgba(42,30,8,0.1);
}

/* ── ANIMATIONS ───────────────────────────────── */
.fade-in {
    animation: fadeIn 0.8s ease forwards;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(8px); }
    to   { opacity: 1; transform: none; }
}

@keyframes blink {
    0%, 100% { opacity: 1; }
    50%       { opacity: 0; }
}

@keyframes pulse-dot {
    0%, 100% { opacity: 0.3; transform: scale(0.8); }
    50%       { opacity: 1;   transform: scale(1.2); }
}

/* ═══ PAGE PENDING ═══════════════════════════════════════════════════════ */
.ac-pending-shell {
    min-height: 100vh;
    background-color: var(--bg-base);
    background-image:
        radial-gradient(ellipse at 50% 30%, rgba(166,117,32,0.08) 0%, transparent 70%);
    display: flex;
    align-items: center;
    justify-content: center;
    font-family: 'Inter', sans-serif;
}

.ac-pending-inner {
    text-align: center;
    padding: 3rem 2rem;
    max-width: 420px;
}

.ac-deco-line {
    width: 80px;
    height: 1px;
    background: linear-gradient(to right, transparent, var(--color-primary), transparent);
    margin: 0 auto;
}

.ac-pulse-dots {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    margin: 2rem 0 1.5rem;
}

.ac-pulse-dots span {
    display: block;
    width: 10px;
    height: 10px;
    border-radius: 50%;
    background: var(--color-primary);
    animation: pulse-dot 1.4s ease-in-out infinite;
}

.ac-pulse-dots span:nth-child(2) { animation-delay: 0.2s; }
.ac-pulse-dots span:nth-child(3) { animation-delay: 0.4s; }

.ac-pending-title {
    font-family: 'Space Grotesk', sans-serif;
    font-size: 1.5rem;
    font-weight: 600;
    color: var(--text-primary);
    margin: 0 0 0.75rem;
}

.ac-pending-sub {
    font-family: 'Space Mono', monospace;
    font-size: 12px;
    color: var(--text-secondary);
    letter-spacing: 0.03em;
}

/* ═══ PAGE RÉSULTATS ═════════════════════════════════════════════════════ */
.ac-results-shell {
    max-width: 780px;
    margin: 0 auto;
    padding: 2.5rem 1.25rem 5rem;
    font-family: 'Inter', sans-serif;
    color: var(--text-primary);
}

/* Phase cinématique */
.ac-reveal-phase {
    min-height: 60vh;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 4rem 1rem;
    text-align: center;
}

.ac-reveal-title {
    font-family: 'Space Grotesk', sans-serif;
    font-size: 1.75rem;
    font-weight: 600;
    color: var(--text-primary);
    margin: 1.5rem 0 0;
}

/* En-tête résultats */
.ac-results-header {
    text-align: center;
    margin-bottom: 3rem;
}

.ac-revelation-badge {
    display: inline-block;
    font-family: 'Space Mono', monospace;
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    color: var(--color-primary);
    background: rgba(166,117,32,0.1);
    border: 1px solid var(--glass-border);
    padding: 4px 12px;
    border-radius: 20px;
}

.ac-results-h1 {
    font-family: 'Space Grotesk', sans-serif;
    font-size: 2.5rem;
    font-weight: 700;
    color: var(--text-primary);
    line-height: 1.15;
    margin: 1rem 0 0.5rem;
    letter-spacing: -0.02em;
}

.ac-results-subtitle {
    font-family: 'Inter', sans-serif;
    font-size: 14px;
    color: var(--text-secondary);
}

/* ── CARTES ──────────────────────────────────── */
.ac-card {
    background: var(--bg-surface);
    border: 1px solid var(--glass-border);
    border-radius: 12px;
    padding: 2rem;
    margin-bottom: 1.75rem;
    box-shadow: var(--shadow-card);
}

.ac-card-title {
    font-family: 'Space Grotesk', sans-serif;
    font-size: 1.125rem;
    font-weight: 600;
    color: var(--text-primary);
    margin: 0 0 1.5rem;
    padding-bottom: 0.75rem;
    border-bottom: 1px solid var(--glass-border);
}

/* ── SYNTHÈSE TYPEWRITER ─────────────────────── */
.ac-synthesis-card {
    position: relative;
}

.ac-typewriter-text {
    font-family: 'Space Mono', monospace;
    font-size: 14px;
    line-height: 1.8;
    color: var(--text-primary);
    white-space: pre-wrap;
    min-height: 2rem;
}

.ac-cursor {
    display: inline-block;
    color: var(--color-primary);
    font-weight: 700;
    animation: blink 1s infinite;
}

.ac-synthesis-full {
    font-family: 'Inter', sans-serif;
    font-size: 15px;
    line-height: 1.7;
    color: var(--text-primary);
}

/* ── Bouton « tout afficher » (passer l'animation) ── */
.ac-skip-btn {
    margin-top: 0.75rem;
    padding: 4px 12px;
    font-family: 'Space Mono', monospace;
    font-size: 11px;
    letter-spacing: 0.03em;
    color: var(--color-primary);
    background: rgba(166,117,32,0.08);
    border: 1px solid var(--glass-border);
    border-radius: 14px;
    cursor: pointer;
    transition: background 0.2s ease;
}

.ac-skip-btn:hover,
.ac-skip-btn:focus-visible {
    background: rgba(166,117,32,0.18);
}

/* ── Disclaimer bienveillant ── */
.ac-disclaimer {
    margin: 2rem 0 0;
    padding: 0.9rem 1.1rem;
    font-family: 'Inter', sans-serif;
    font-size: 12.5px;
    line-height: 1.55;
    color: var(--text-secondary);
    background: var(--glass-bg);
    border-left: 2px solid var(--color-signal);
    border-radius: 0 8px 8px 0;
}

/* ── DIMENSIONS ──────────────────────────────── */
.ac-dimensions {
    display: flex;
    flex-direction: column;
    gap: 1.25rem;
}

.ac-card-hint {
    font-family: 'Space Mono', monospace;
    font-size: 11px;
    color: var(--text-secondary);
    margin: -1rem 0 1.25rem;
    letter-spacing: 0.02em;
}

.ac-dimension-row {}

.ac-dimension-header {
    display: flex;
    justify-content: space-between;
    align-items: baseline;
    width: 100%;
    margin-bottom: 0.4rem;
    padding: 0;
    background: none;
    border: none;
    text-align: left;
    cursor: pointer;
    -webkit-appearance: none;
            appearance: none;
}

.ac-dimension-header:hover .ac-dimension-name,
.ac-dimension-header:focus-visible .ac-dimension-name {
    color: var(--color-primary-dark);
}

.ac-dimension-name {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    font-family: 'Space Grotesk', sans-serif;
    font-size: 13px;
    font-weight: 600;
    color: var(--text-primary);
    transition: color 0.2s ease;
}

.ac-dimension-info {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 15px;
    height: 15px;
    border-radius: 50%;
    border: 1px solid var(--glass-border);
    font-family: 'Space Mono', monospace;
    font-size: 9px;
    font-style: italic;
    font-weight: 700;
    line-height: 1;
    color: var(--color-primary);
    transition: background 0.2s ease, border-color 0.2s ease, transform 0.2s ease;
}

.ac-dimension-row.is-open .ac-dimension-info,
.ac-dimension-header:hover .ac-dimension-info {
    background: var(--color-primary);
    border-color: var(--color-primary);
    color: var(--bg-base);
}

.ac-dimension-def {
    font-family: 'Inter', sans-serif;
    font-size: 12.5px;
    line-height: 1.55;
    color: var(--text-secondary);
    margin: 0.6rem 0 0;
    padding: 0.7rem 0.85rem;
    background: var(--glass-bg);
    border-left: 2px solid var(--color-primary);
    border-radius: 0 6px 6px 0;
}

.ac-def-enter-active,
.ac-def-leave-active {
    transition: opacity 0.25s ease, transform 0.25s ease;
}

.ac-def-enter-from,
.ac-def-leave-to {
    opacity: 0;
    transform: translateY(-4px);
}

.ac-dimension-score {
    font-family: 'Space Mono', monospace;
    font-size: 12px;
    color: var(--text-secondary);
}

.ac-progress-track {
    width: 100%;
    height: 8px;
    background: var(--bg-elevated);
    border-radius: 4px;
    overflow: hidden;
}

.ac-progress-fill {
    height: 100%;
    background: var(--color-primary);
    border-radius: 4px;
    transition: width 1s ease;
}

/* ── VOIES POSSIBLES ─────────────────────────── */
.ac-jobs-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1rem;
}

@media (max-width: 600px) {
    .ac-jobs-grid {
        grid-template-columns: 1fr;
    }
}

.ac-job-card {
    position: relative;
    background: var(--bg-base);
    border: 1px solid var(--glass-border);
    border-radius: 10px;
    padding: 1.25rem;
    transition: border-color 0.2s ease, box-shadow 0.2s ease;
}

.ac-job-card:hover {
    border-color: var(--color-primary);
    box-shadow: 0 4px 16px rgba(166,117,32,0.12);
}

.ac-job-num {
    position: absolute;
    top: 0.875rem;
    right: 0.875rem;
    font-family: 'Space Mono', monospace;
    font-size: 11px;
    color: var(--text-secondary);
    opacity: 0.6;
}

.ac-job-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 0.5rem;
    margin-bottom: 0.375rem;
    padding-right: 1.5rem;
}

.ac-job-title {
    font-family: 'Space Grotesk', sans-serif;
    font-size: 15px;
    font-weight: 600;
    color: var(--text-primary);
    line-height: 1.3;
}

.ac-job-fit {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 42px;
    height: 42px;
    border-radius: 50%;
    background: var(--color-success);
    color: #fff;
    font-family: 'Space Mono', monospace;
    font-size: 11px;
    font-weight: 700;
    flex-shrink: 0;
}

.ac-job-sector {
    font-family: 'Space Mono', monospace;
    font-size: 10px;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    color: var(--text-secondary);
    margin: 0.5rem 0 0.625rem;
}

.ac-job-desc {
    font-family: 'Inter', sans-serif;
    font-size: 13px;
    color: var(--text-primary);
    line-height: 1.5;
}

.ac-job-step {
    font-family: 'Inter', sans-serif;
    font-size: 12px;
    color: var(--color-primary);
    font-weight: 500;
    margin-top: 0.75rem;
}

/* ── CTA PDF ─────────────────────────────────── */
.ac-cta-pdf {
    text-align: center;
    margin-top: 3rem;
    padding: 2rem;
    background: var(--bg-elevated);
    border-radius: 12px;
    border: 1px solid var(--glass-border);
}

.ac-cta-label {
    font-family: 'Inter', sans-serif;
    font-size: 13px;
    color: var(--text-secondary);
    margin-bottom: 1rem;
}

.ac-btn-primary {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0.875rem 2rem;
    background: var(--color-accent);
    color: var(--bg-base);
    font-family: 'Space Grotesk', sans-serif;
    font-size: 15px;
    font-weight: 600;
    border-radius: 8px;
    text-decoration: none;
    transition: background 0.2s ease, transform 0.1s ease;
    letter-spacing: 0.01em;
}

.ac-btn-primary:hover {
    background: var(--color-primary-dark);
    transform: translateY(-1px);
}

.ac-btn-primary:active {
    transform: translateY(0);
}

/* ── Accessibilité : réduire les animations si l'utilisateur le demande ── */
@media (prefers-reduced-motion: reduce) {
    .fade-in,
    .ac-cursor,
    .ac-pulse-dots span,
    .ac-progress-fill,
    .ac-btn-primary {
        animation: none !important;
        transition: none !important;
    }
}
</style>
