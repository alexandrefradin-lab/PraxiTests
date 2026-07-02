<script setup>
import { ref, watch } from 'vue'
import { router } from '@inertiajs/vue3'

const props = defineProps({
    show: { type: Boolean, default: false },
})
const emit = defineEmits(['close'])

const claimed = ref(false)
const loading = ref(false)
const eclats = ref(0)
const alreadyClaimed = ref(false)

async function claim() {
    if (loading.value || claimed.value) return
    loading.value = true
    try {
        const res = await fetch(route('easter-egg.claim'), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                'Accept': 'application/json',
            },
            credentials: 'same-origin',
        })
        const data = await res.json()
        if (data.already_claimed) {
            alreadyClaimed.value = true
        } else {
            eclats.value = data.eclats ?? 42
            claimed.value = true
        }
    } catch (e) {
        // réseau : on ferme silencieusement
        emit('close')
    } finally {
        loading.value = false
    }
}

function close() {
    emit('close')
    // Recharger les Éclats dans le layout
    if (claimed.value) {
        router.reload({ only: ['auth'] })
    }
}

watch(() => props.show, (val) => {
    if (val) claim()
})
</script>

<template>
    <Teleport to="body">
        <Transition name="ee-fade">
            <div v-if="show" class="ee-backdrop" @click.self="close" role="dialog" aria-modal="true" aria-label="Secret découvert">
                <div class="ee-modal">
                    <!-- Sceau animé -->
                    <div class="ee-seal" aria-hidden="true">
                        <svg viewBox="0 0 120 120" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <circle cx="60" cy="60" r="55" stroke="#c9a84c" stroke-width="2" stroke-dasharray="4 3" class="ee-seal-ring"/>
                            <circle cx="60" cy="60" r="44" stroke="#c9a84c" stroke-width="1.5" opacity="0.6"/>
                            <text x="60" y="68" text-anchor="middle" font-size="42" class="ee-seal-eye">👁</text>
                        </svg>
                    </div>

                    <!-- Contenu -->
                    <div v-if="loading" class="ee-body">
                        <p class="ee-sub">Invocation en cours…</p>
                    </div>

                    <div v-else-if="alreadyClaimed" class="ee-body">
                        <h2 class="ee-title">Déjà Éveillé</h2>
                        <p class="ee-text">
                            Tu portes déjà la marque des initiés. Le secret ne peut être révélé qu'une seule fois.
                        </p>
                        <button class="ee-btn" @click="close">Fermer</button>
                    </div>

                    <div v-else class="ee-body">
                        <p class="ee-kicker">— Séquence ancienne reconnue —</p>
                        <h2 class="ee-title">L'Oracle s'éveille</h2>
                        <p class="ee-text">
                            Depuis les premiers jours de PraxiQuest, une clé ancienne sommeille dans l'ombre.
                            Ceux qui la connaissent accèdent à une vérité que peu voient :
                            <em>la curiosité est déjà une forme d'intelligence.</em>
                        </p>
                        <p class="ee-text">
                            Tu viens de rejoindre l'ordre des Éveillés.
                        </p>
                        <div class="ee-reward" aria-live="polite">
                            <span class="ee-eclats">+{{ eclats }}</span>
                            <span class="ee-eclats-label">Éclats</span>
                        </div>
                        <p class="ee-badge-note">🏅 Badge « Éveillé » débloqué dans ton profil</p>
                        <button class="ee-btn" @click="close">Continuer mon voyage</button>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

<style scoped>
.ee-backdrop {
    position: fixed;
    inset: 0;
    z-index: 9999;
    background: rgba(10, 8, 5, 0.85);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1.5rem;
    backdrop-filter: blur(4px);
}

.ee-modal {
    background: linear-gradient(160deg, #1a1408 0%, #0f0b04 100%);
    border: 1px solid #c9a84c;
    border-radius: 4px;
    max-width: 480px;
    width: 100%;
    padding: 2.5rem 2rem;
    text-align: center;
    position: relative;
    box-shadow: 0 0 60px rgba(201, 168, 76, 0.15), 0 0 0 1px rgba(201, 168, 76, 0.08);
    animation: ee-appear 0.5s cubic-bezier(0.16, 1, 0.3, 1);
}

@keyframes ee-appear {
    from { opacity: 0; transform: scale(0.92) translateY(12px); }
    to   { opacity: 1; transform: scale(1) translateY(0); }
}

.ee-seal {
    width: 96px;
    height: 96px;
    margin: 0 auto 1.5rem;
}

.ee-seal svg { width: 100%; height: 100%; }

.ee-seal-ring {
    animation: ee-spin 20s linear infinite;
    transform-origin: 60px 60px;
}

@keyframes ee-spin {
    from { transform: rotate(0deg); }
    to   { transform: rotate(360deg); }
}

.ee-seal-eye {
    animation: ee-pulse 3s ease-in-out infinite;
}

@keyframes ee-pulse {
    0%, 100% { opacity: 1; }
    50%       { opacity: 0.5; }
}

.ee-kicker {
    font-size: 0.7rem;
    letter-spacing: 0.18em;
    text-transform: uppercase;
    color: #c9a84c;
    opacity: 0.7;
    margin-bottom: 0.75rem;
}

.ee-title {
    font-family: 'Cinzel', 'Georgia', serif;
    font-size: 1.6rem;
    color: #e8d5a3;
    margin-bottom: 1.25rem;
    letter-spacing: 0.04em;
}

.ee-text {
    font-size: 0.92rem;
    color: #b8a882;
    line-height: 1.7;
    margin-bottom: 1rem;
}

.ee-text em {
    color: #e8d5a3;
    font-style: italic;
}

.ee-sub {
    color: #7a6a44;
    font-size: 0.88rem;
    animation: ee-pulse 1.5s ease-in-out infinite;
}

.ee-reward {
    display: flex;
    align-items: baseline;
    justify-content: center;
    gap: 0.4rem;
    margin: 1.5rem 0 0.5rem;
}

.ee-eclats {
    font-family: 'Space Mono', monospace;
    font-size: 2.2rem;
    color: #c9a84c;
    animation: ee-glow 2s ease-in-out infinite;
}

@keyframes ee-glow {
    0%, 100% { text-shadow: 0 0 8px rgba(201, 168, 76, 0.4); }
    50%       { text-shadow: 0 0 24px rgba(201, 168, 76, 0.8); }
}

.ee-eclats-label {
    font-size: 1rem;
    color: #c9a84c;
    opacity: 0.8;
}

.ee-badge-note {
    font-size: 0.8rem;
    color: #7a6a44;
    margin-bottom: 1.75rem;
}

.ee-btn {
    background: transparent;
    border: 1px solid #c9a84c;
    color: #c9a84c;
    padding: 0.6rem 1.8rem;
    border-radius: 2px;
    font-size: 0.85rem;
    letter-spacing: 0.08em;
    cursor: pointer;
    transition: background 0.2s, color 0.2s;
}

.ee-btn:hover {
    background: #c9a84c;
    color: #0f0b04;
}

/* Transition Fade */
.ee-fade-enter-active,
.ee-fade-leave-active { transition: opacity 0.3s ease; }
.ee-fade-enter-from,
.ee-fade-leave-to    { opacity: 0; }
</style>
