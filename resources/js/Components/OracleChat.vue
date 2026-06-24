<script setup>
import { ref, nextTick, watch } from 'vue'
import MarkdownText from '@/Components/MarkdownText.vue'

const open = ref(false)
const loaded = ref(false)        // historique déjà chargé ?
const sending = ref(false)
const messages = ref([])         // { role, content }
const draft = ref('')
const scroller = ref(null)
const error = ref('')

// CSRF : Laravel pose le cookie XSRF-TOKEN ; on le renvoie en en-tête.
function xsrfToken() {
    const m = document.cookie.match(/XSRF-TOKEN=([^;]+)/)
    return m ? decodeURIComponent(m[1]) : ''
}

function scrollToBottom() {
    nextTick(() => {
        const el = scroller.value
        if (el) el.scrollTop = el.scrollHeight
    })
}

async function loadHistory() {
    try {
        const r = await fetch(route('oracle.history'), {
            headers: { Accept: 'application/json' },
            credentials: 'same-origin',
        })
        const data = await r.json()
        messages.value = (data.messages ?? []).map(m => ({ role: m.role, content: m.content }))
    } catch (e) {
        /* silencieux : on garde un fil vide */
    } finally {
        loaded.value = true
        scrollToBottom()
    }
}

function toggle() {
    open.value = !open.value
    if (open.value && !loaded.value) loadHistory()
    if (open.value) scrollToBottom()
}

async function send() {
    const text = draft.value.trim()
    if (!text || sending.value) return

    error.value = ''
    messages.value.push({ role: 'user', content: text })
    draft.value = ''
    sending.value = true
    scrollToBottom()

    try {
        const r = await fetch(route('oracle.message'), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
                'X-XSRF-TOKEN': xsrfToken(),
            },
            credentials: 'same-origin',
            body: JSON.stringify({ message: text }),
        })
        if (!r.ok) throw new Error('http ' + r.status)
        const data = await r.json()
        messages.value.push({ role: 'assistant', content: data.reply?.content ?? '…' })
    } catch (e) {
        error.value = "L'Oracle n'a pas pu répondre. Réessaie dans un instant."
    } finally {
        sending.value = false
        scrollToBottom()
    }
}

async function clearConversation() {
    if (!confirm('Effacer toute la conversation avec l\'Oracle ?')) return
    try {
        await fetch(route('oracle.clear'), {
            method: 'DELETE',
            headers: { Accept: 'application/json', 'X-XSRF-TOKEN': xsrfToken() },
            credentials: 'same-origin',
        })
        messages.value = []
    } catch (e) { /* ignore */ }
}

function onKeydown(e) {
    // Entrée envoie, Maj+Entrée = nouvelle ligne.
    if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault()
        send()
    }
}

watch(messages, scrollToBottom, { deep: true })
</script>

<template>
    <div class="oracle-root">
        <!-- Panneau -->
        <transition name="oracle-pop">
            <section v-if="open" class="oracle-panel" role="dialog" aria-label="L'Oracle">
                <header class="oracle-head">
                    <div class="oracle-head-id">
                        <span class="oracle-sigil">&#10022;</span>
                        <div>
                            <p class="oracle-name">L'Oracle</p>
                            <p class="oracle-role">Conseil d'orientation</p>
                        </div>
                    </div>
                    <div class="oracle-head-actions">
                        <button v-if="messages.length" class="oracle-icon-btn" title="Effacer la conversation" @click="clearConversation">&#8635;</button>
                        <button class="oracle-icon-btn" title="Fermer" @click="open = false">&#10005;</button>
                    </div>
                </header>

                <div ref="scroller" class="oracle-body">
                    <div v-if="!messages.length" class="oracle-welcome">
                        <span class="oracle-sigil-lg">&#10022;</span>
                        <p class="oracle-welcome-title">Pose ta question à l'Oracle</p>
                        <p class="oracle-welcome-text">
                            Il connaît tes épreuves et ton Grimoire. Demande-lui d'éclairer ton profil,
                            d'explorer une voie, ou de te suggérer des métiers qui te ressemblent.
                        </p>
                    </div>

                    <div
                        v-for="(m, i) in messages"
                        :key="i"
                        class="oracle-msg"
                        :class="m.role === 'user' ? 'oracle-msg--user' : 'oracle-msg--oracle'"
                    >
                        <div class="oracle-bubble">
                            <template v-if="m.role === 'assistant'">
                                <MarkdownText :source="m.content" />
                            </template>
                            <template v-else>{{ m.content }}</template>
                        </div>
                    </div>

                    <div v-if="sending" class="oracle-msg oracle-msg--oracle">
                        <p class="oracle-bubble oracle-typing"><span></span><span></span><span></span></p>
                    </div>

                    <p v-if="error" class="oracle-error">{{ error }}</p>
                </div>

                <footer class="oracle-input">
                    <textarea
                        v-model="draft"
                        rows="1"
                        placeholder="Écris à l'Oracle…"
                        class="oracle-textarea"
                        @keydown="onKeydown"
                    ></textarea>
                    <button class="oracle-send" :disabled="sending || !draft.trim()" @click="send" aria-label="Envoyer">
                        &#10148;
                    </button>
                </footer>
            </section>
        </transition>

        <!-- Bulle flottante -->
        <button class="oracle-fab" :class="{ 'oracle-fab--open': open }" @click="toggle" aria-label="Ouvrir l'Oracle">
            <svg v-if="!open" width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <circle cx="12" cy="12" r="9" />
                <polygon points="15.5 8.5 11 11 8.5 15.5 13 13" fill="currentColor" stroke="none" />
                <circle cx="12" cy="12" r="0.7" fill="currentColor" stroke="none" />
            </svg>
            <span v-else class="oracle-fab-close">&#10005;</span>
        </button>
    </div>
</template>

<style scoped>
.oracle-root {
    position: fixed;
    right: 22px;
    bottom: 22px;
    z-index: 60;
    --or-gold: var(--color-primary, #A67520);
    --or-gold-dark: var(--color-primary-dark, #7D5510);
    --or-red: var(--color-secondary, #7B1515);
    --or-ink: var(--text-primary, #2A1E08);
}

/* ── Bulle flottante ────────────────────────────────────────────── */
.oracle-fab {
    width: 58px;
    height: 58px;
    border-radius: 50%;
    border: 1px solid var(--or-gold);
    background: radial-gradient(circle at 34% 28%, #FBF3DF, #E9D4A8);
    color: var(--or-gold-dark);
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    box-shadow: 0 6px 22px rgba(42, 30, 8, 0.28), inset 0 1px 0 rgba(255, 255, 255, 0.5);
    transition: transform 0.18s ease, box-shadow 0.18s ease;
    margin-left: auto;
    position: relative;
}
/* Anneau pulsant */
.oracle-fab::before {
    content: '';
    position: absolute;
    inset: -6px;
    border-radius: 50%;
    border: 1.5px solid var(--or-gold);
    opacity: 0;
    animation: oraclePulse 2.8s ease-out infinite;
    pointer-events: none;
}
.oracle-fab::after {
    content: '';
    position: absolute;
    inset: -12px;
    border-radius: 50%;
    border: 1px solid var(--or-gold);
    opacity: 0;
    animation: oraclePulse 2.8s ease-out infinite 0.7s;
    pointer-events: none;
}
@keyframes oraclePulse {
    0%   { opacity: 0.55; inset: -4px; }
    100% { opacity: 0;    inset: -20px; }
}
.oracle-fab--open::before,
.oracle-fab--open::after { animation: none; opacity: 0; }
.oracle-fab:hover { transform: translateY(-2px); box-shadow: 0 10px 28px rgba(42, 30, 8, 0.34); }
.oracle-fab--open { background: linear-gradient(180deg, var(--or-gold), var(--or-gold-dark)); color: #FBF6EA; }
.oracle-fab-close { font-size: 20px; line-height: 1; }

/* ── Panneau ────────────────────────────────────────────────────── */
.oracle-panel {
    position: absolute;
    right: 0;
    bottom: 70px;
    width: min(380px, calc(100vw - 36px));
    height: min(560px, calc(100vh - 130px));
    display: flex;
    flex-direction: column;
    background: linear-gradient(180deg, #F8F2E2, #EFE5CD);
    border: 1px solid var(--or-gold);
    border-radius: 16px;
    box-shadow: 0 18px 48px rgba(42, 30, 8, 0.32);
    overflow: hidden;
}

.oracle-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 12px 14px;
    background: linear-gradient(180deg, #F4EAD2, #E9DCBC);
    border-bottom: 1px solid rgba(166, 117, 32, 0.3);
}
.oracle-head-id { display: flex; align-items: center; gap: 10px; }
.oracle-sigil {
    width: 34px; height: 34px;
    display: flex; align-items: center; justify-content: center;
    border-radius: 50%;
    border: 1px solid var(--or-gold);
    background: radial-gradient(circle at 35% 30%, #FBF3DF, #E9D9B4);
    color: var(--or-gold-dark);
    font-size: 16px;
}
.oracle-name { font-family: var(--font-display, 'Space Grotesk', sans-serif); font-weight: 700; font-size: 15px; color: var(--or-ink); margin: 0; line-height: 1.1; }
.oracle-role { font-family: var(--font-data, monospace); font-size: 9px; letter-spacing: 0.12em; text-transform: uppercase; color: var(--or-gold-dark); margin: 0; }
.oracle-head-actions { display: flex; gap: 4px; }
.oracle-icon-btn {
    width: 30px; height: 30px;
    border: none; background: none; cursor: pointer;
    color: var(--text-secondary, #6B5A3E);
    border-radius: 8px;
    font-size: 15px;
    transition: background 0.15s, color 0.15s;
}
.oracle-icon-btn:hover { background: rgba(166, 117, 32, 0.14); color: var(--or-ink); }

/* ── Corps / messages ───────────────────────────────────────────── */
.oracle-body { flex: 1; overflow-y: auto; padding: 16px 14px; display: flex; flex-direction: column; gap: 10px; }

.oracle-welcome { text-align: center; margin: auto 0; padding: 1rem; }
.oracle-sigil-lg { color: var(--or-gold); font-size: 26px; opacity: 0.8; }
.oracle-welcome-title { font-family: var(--font-display, sans-serif); font-weight: 700; font-size: 16px; color: var(--or-ink); margin: 10px 0 6px; }
.oracle-welcome-text { font-family: var(--font-body, 'Inter', sans-serif); font-size: 13px; line-height: 1.6; color: var(--text-secondary, #6B5A3E); max-width: 280px; margin: 0 auto; }

.oracle-msg { display: flex; }
.oracle-msg--user { justify-content: flex-end; }
.oracle-msg--oracle { justify-content: flex-start; }
.oracle-bubble {
    max-width: 84%;
    padding: 9px 13px;
    border-radius: 14px;
    font-family: var(--font-body, 'Inter', sans-serif);
    font-size: 13.5px;
    line-height: 1.55;
    margin: 0;
    white-space: pre-wrap;
    word-wrap: break-word;
}
.oracle-msg--user .oracle-bubble {
    background: linear-gradient(180deg, var(--or-gold), var(--or-gold-dark));
    color: #FBF6EA;
    border-bottom-right-radius: 4px;
    box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.2);
}
.oracle-msg--oracle .oracle-bubble {
    background: #FFFDF6;
    color: var(--or-ink);
    border: 1px solid rgba(166, 117, 32, 0.28);
    border-bottom-left-radius: 4px;
}

.oracle-typing { display: flex; gap: 5px; align-items: center; }
.oracle-typing span { width: 6px; height: 6px; border-radius: 50%; background: var(--or-gold); animation: oracleBlink 1.2s infinite ease-in-out; }
.oracle-typing span:nth-child(2) { animation-delay: 0.2s; }
.oracle-typing span:nth-child(3) { animation-delay: 0.4s; }
@keyframes oracleBlink { 0%, 80%, 100% { opacity: 0.25; transform: scale(0.8); } 40% { opacity: 1; transform: scale(1); } }

.oracle-error { font-family: var(--font-body, sans-serif); font-size: 12px; color: var(--or-red); text-align: center; margin: 4px 0 0; }

/* ── Saisie ─────────────────────────────────────────────────────── */
.oracle-input {
    display: flex;
    align-items: flex-end;
    gap: 8px;
    padding: 10px 12px;
    border-top: 1px solid rgba(166, 117, 32, 0.3);
    background: #F4EAD2;
}
.oracle-textarea {
    flex: 1;
    resize: none;
    max-height: 110px;
    border: 1px solid rgba(166, 117, 32, 0.35);
    border-radius: 10px;
    padding: 9px 11px;
    font-family: var(--font-body, 'Inter', sans-serif);
    font-size: 13.5px;
    line-height: 1.45;
    color: var(--or-ink);
    background: #FFFDF6;
    outline: none;
}
.oracle-textarea:focus { border-color: var(--or-gold); box-shadow: 0 0 0 2px rgba(166, 117, 32, 0.16); }
.oracle-send {
    flex-shrink: 0;
    width: 40px; height: 40px;
    border-radius: 10px;
    border: none;
    cursor: pointer;
    background: linear-gradient(180deg, var(--or-gold), var(--or-gold-dark));
    color: #FBF6EA;
    font-size: 17px;
    transition: filter 0.15s, opacity 0.15s;
}
.oracle-send:hover:not(:disabled) { filter: brightness(1.08); }
.oracle-send:disabled { opacity: 0.45; cursor: not-allowed; }

/* ── Transition d'ouverture ─────────────────────────────────────── */
.oracle-pop-enter-active, .oracle-pop-leave-active { transition: opacity 0.18s ease, transform 0.18s ease; }
.oracle-pop-enter-from, .oracle-pop-leave-to { opacity: 0; transform: translateY(12px) scale(0.97); }

@media (max-width: 480px) {
    .oracle-root { right: 14px; bottom: 14px; }
    .oracle-panel { bottom: 66px; height: min(70vh, calc(100vh - 110px)); }
}
</style>
