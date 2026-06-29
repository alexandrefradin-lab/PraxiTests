<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue'
import { router } from '@inertiajs/vue3'

const props = defineProps({
    attempt:     Object,
    eclatsGagnes:{ type: Number, default: 0 },
    levelUp:     { type: Boolean, default: false },
    newLevel:    { type: Number,  default: null },
    redirectUrl: String,
})

const countdown = ref(4)
let timer = null
let countTimer = null

// Precomputed particles — static so no reactivity noise
const PARTICLES = Array.from({ length: 28 }, (_, i) => ({
    left:     (Math.random() * 96 + 2).toFixed(1) + '%',
    delay:    (Math.random() * 2.5).toFixed(2) + 's',
    duration: (2.8 + Math.random() * 2.2).toFixed(2) + 's',
    size:     Math.round(10 + Math.random() * 16) + 'px',
    opacity:  (0.35 + Math.random() * 0.65).toFixed(2),
    symbol:   ['✦', '✧', '◆', '⋆', '✦'][Math.floor(Math.random() * 5)],
}))

onMounted(() => {
    countTimer = setInterval(() => {
        if (countdown.value > 0) countdown.value--
    }, 1000)

    timer = setTimeout(() => {
        if (props.redirectUrl) {
            router.visit(props.redirectUrl)
        } else if (props.attempt?.id) {
            router.visit(route('candidate.results.show', props.attempt.id))
        }
    }, 4000)
})

onBeforeUnmount(() => {
    clearTimeout(timer)
    clearInterval(countTimer)
})
</script>

<template>
    <div class="ac-overlay" role="status" aria-live="polite">

        <!-- Falling Éclats particles -->
        <div class="ac-particles" aria-hidden="true">
            <span
                v-for="(p, i) in PARTICLES"
                :key="i"
                class="ac-particle"
                :style="{
                    left: p.left,
                    animationDelay: p.delay,
                    animationDuration: p.duration,
                    fontSize: p.size,
                    opacity: p.opacity,
                }"
            >{{ p.symbol }}</span>
        </div>

        <!-- Card -->
        <div class="ac-card">

            <!-- Map fragment reveal -->
            <div class="ac-fragment" aria-hidden="true">
                <svg viewBox="0 0 120 104" class="ac-fragment-svg" xmlns="http://www.w3.org/2000/svg">
                    <polygon points="60,2 118,32 118,72 60,102 2,72 2,32"
                             fill="rgba(212,168,67,0.06)" stroke="#D4A843" stroke-width="1.2"/>
                    <path d="M18,62 L42,36 L62,48 L84,28 L104,54"
                          stroke="#D4A843" stroke-width="0.9" fill="none" opacity="0.5" stroke-dasharray="3 4"/>
                    <path d="M28,78 L60,65 L92,72"
                          stroke="#D4A843" stroke-width="0.7" fill="none" opacity="0.35"/>
                    <circle cx="60" cy="52" r="5" fill="#D4A843" opacity="0.85"/>
                    <circle cx="60" cy="52" r="10" fill="none" stroke="#D4A843" stroke-width="0.6" opacity="0.3"/>
                    <polygon points="60,2 118,32 118,72 60,102 2,72 2,32"
                             fill="none" stroke="#D4A843" stroke-width="0.6" stroke-dasharray="4 7" opacity="0.3"/>
                </svg>
                <p class="ac-fragment-label">Nouveau territoire révélé</p>
            </div>

            <!-- Éclats burst -->
            <div class="ac-eclats">
                <span class="ac-eclat-icon" aria-hidden="true">✦</span>
                <span class="ac-eclat-count">+{{ eclatsGagnes }}</span>
                <span class="ac-eclat-label">Éclats gagnés</span>
            </div>

            <!-- Level up banner -->
            <div v-if="levelUp" class="ac-level-banner">
                ✧ Niveau {{ newLevel }} atteint ! ✧
            </div>

            <h1 class="ac-title">Épreuve accomplie !</h1>
            <p class="ac-subtitle">Ton Grimoire se révèle…</p>

            <!-- Countdown dots -->
            <div class="ac-countdown" aria-hidden="true">
                <span :class="{ 'ac-dot': true, 'ac-dot-active': countdown >= 3 }"/>
                <span :class="{ 'ac-dot': true, 'ac-dot-active': countdown >= 2 }"/>
                <span :class="{ 'ac-dot': true, 'ac-dot-active': countdown >= 1 }"/>
                <span :class="{ 'ac-dot': true, 'ac-dot-active': countdown >= 0 }"/>
            </div>

        </div>
    </div>
</template>

<style scoped>
.ac-overlay {
    position: fixed;
    inset: 0;
    background: rgba(8, 5, 1, 0.95);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 9999;
    overflow: hidden;
}

/* ── Particles ── */
.ac-particles {
    position: absolute;
    inset: 0;
    pointer-events: none;
}
.ac-particle {
    position: absolute;
    top: -30px;
    color: #D4A843;
    animation: ac-fall linear infinite;
    will-change: transform, opacity;
}
@keyframes ac-fall {
    0%   { transform: translateY(0) rotate(0deg);    opacity: var(--op, 0.7); }
    80%  { opacity: var(--op, 0.7); }
    100% { transform: translateY(110vh) rotate(360deg); opacity: 0; }
}

/* ── Card ── */
.ac-card {
    text-align: center;
    color: #F0E8D4;
    padding: 2.5rem 3.5rem;
    position: relative;
    z-index: 1;
    animation: ac-rise 0.5s ease-out;
}
@keyframes ac-rise {
    from { transform: translateY(20px); opacity: 0; }
    to   { transform: translateY(0);    opacity: 1; }
}

/* ── Map fragment ── */
.ac-fragment {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.4rem;
    margin-bottom: 1.8rem;
    animation: ac-reveal 0.8s ease-out 0.3s both;
}
@keyframes ac-reveal {
    from { transform: scale(0.7) rotate(-8deg); opacity: 0; }
    to   { transform: scale(1) rotate(0deg);    opacity: 1; }
}
.ac-fragment-svg {
    width: 88px;
    height: auto;
    filter: drop-shadow(0 0 12px rgba(212, 168, 67, 0.35));
}
.ac-fragment-label {
    font-size: 0.65rem;
    letter-spacing: 0.14em;
    text-transform: uppercase;
    color: #D4A843;
    opacity: 0.55;
    margin: 0;
    font-family: 'Space Mono', monospace;
}

/* ── Éclats ── */
.ac-eclats {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.2rem;
    margin-bottom: 1.5rem;
}
.ac-eclat-icon {
    font-size: 2.8rem;
    color: #D4A843;
    animation: ac-pulse 1.4s ease-in-out infinite;
    display: block;
}
@keyframes ac-pulse {
    0%, 100% { transform: scale(1);    filter: drop-shadow(0 0 4px #D4A843); }
    50%       { transform: scale(1.18); filter: drop-shadow(0 0 14px #D4A843); }
}
.ac-eclat-count {
    font-size: 2.6rem;
    font-weight: 800;
    color: #D4A843;
    font-family: 'Space Mono', monospace;
    letter-spacing: 0.04em;
    line-height: 1;
}
.ac-eclat-label {
    font-size: 0.68rem;
    letter-spacing: 0.15em;
    text-transform: uppercase;
    opacity: 0.5;
    font-family: 'Space Mono', monospace;
}

/* ── Level banner ── */
.ac-level-banner {
    background: linear-gradient(135deg, #A67520, #D4A843);
    color: #1a0e00;
    font-weight: 700;
    font-size: 0.95rem;
    padding: 0.5rem 1.8rem;
    border-radius: 2rem;
    display: inline-block;
    margin-bottom: 1.25rem;
}

/* ── Title / subtitle ── */
.ac-title {
    font-size: 2rem;
    font-weight: 700;
    margin: 0.25rem 0;
    font-family: 'Space Grotesk', sans-serif;
}
.ac-subtitle {
    font-size: 0.9rem;
    opacity: 0.5;
    margin-bottom: 1.8rem;
    font-family: 'Inter', sans-serif;
}

/* ── Countdown dots ── */
.ac-countdown {
    display: flex;
    gap: 0.45rem;
    justify-content: center;
}
.ac-dot {
    width: 7px;
    height: 7px;
    border-radius: 50%;
    background: rgba(212, 168, 67, 0.2);
    border: 1px solid rgba(212, 168, 67, 0.3);
    transition: background 0.4s ease, transform 0.4s ease;
}
.ac-dot-active {
    background: #D4A843;
    border-color: #D4A843;
    transform: scale(1.15);
}
</style>
