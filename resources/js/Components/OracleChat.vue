<script setup>
import { ref, computed, nextTick, watch, onBeforeUnmount } from 'vue'
import { useParcours } from '@/composables/useParcours'

const { isCorporate } = useParcours()

// Libellés du widget selon le parcours (médiéval : Oracle · corporate : conseiller)
const T = computed(() => isCorporate.value
    ? {
        name: 'Votre conseiller',
        welcomeTitle: 'Posez votre question',
        welcomeText: "Il connaît vos évaluations et votre dossier de synthèse. Demandez-lui d'éclairer votre profil, d'explorer une piste, ou de vous suggérer des métiers qui vous ressemblent.",
        suggestionsLabel: 'Commencez par ici :',
        suggestions: ['Quels métiers me correspondent le mieux ?', 'Comment interpréter mes résultats ?', 'Quelle évaluation passer en priorité ?'],
        placeholder: 'Écrivez à votre conseiller…',
        errorPrefix: "Le conseiller n'a pas pu répondre",
        errorRetry: 'Réessayez dans un instant.',
        disclaimer: "Ce conseiller est une IA d'orientation : ses réponses sont indicatives et ne remplacent pas l'avis d'un psychologue, d'un médecin ou d'un coach.",
        fabLabel: 'Ouvrir le conseiller',
    }
    : {
        name: "L'Oracle",
        welcomeTitle: "Pose ta question à l'Oracle",
        welcomeText: "Il connaît tes épreuves et ton Grimoire. Demande-lui d'éclairer ton profil, d'explorer une voie, ou de te suggérer des métiers qui te ressemblent.",
        suggestionsLabel: 'Commence par ici :',
        suggestions: ['Quels métiers me correspondent le mieux ?', 'Comment interpréter mon score ?', 'Quelle épreuve passer en priorité ?'],
        placeholder: "Écris à l'Oracle…",
        errorPrefix: "L'Oracle n'a pas pu répondre",
        errorRetry: 'Réessaie dans un instant.',
        disclaimer: "L'Oracle est une IA d'orientation : ses réponses sont indicatives et ne remplacent pas l'avis d'un psychologue, d'un médecin ou d'un coach.",
        fabLabel: "Ouvrir l'Oracle",
    })

// Renderer Markdown léger pour les bulles oracle.
// Utilise exclusivement des inline styles — aucune dépendance aux classes CSS
// ou aux règles scoped Vue (qui ne s'appliquent pas sur les éléments v-html).
function renderOracle(text) {
    if (!text) return ''

    const esc = s => s.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
    const inline = s => esc(s)
        .replace(/\*\*([^*]+)\*\*/g, '<strong style="font-weight:700;color:inherit">$1</strong>')
        .replace(/\*([^*\n]+)\*/g, '<em style="font-style:italic;color:inherit">$1</em>')
        .replace(/`([^`]+)`/g, '<code style="font-family:monospace;font-size:0.9em;background:rgba(166,117,32,0.15);padding:1px 5px;border-radius:3px">$1</code>')

    // Styles inline constants
    const PS  = 'style="margin:0 0 5px;padding:0;font-size:13.5px;line-height:1.5;color:inherit"'
    const ULS = 'style="margin:4px 0 5px;padding-left:1.2em;list-style:disc"'
    const OLS = 'style="margin:4px 0 5px;padding-left:1.5em;list-style:decimal"'
    const LIS = 'style="margin:1px 0;font-size:13.5px;line-height:1.5;color:inherit"'

    const lines = text.replace(/\r\n/g, '\n').trim().split('\n')
    const out = []
    let listType = null
    let para = []

    const flushPara = () => {
        if (para.length) { out.push(`<p ${PS}>${inline(para.join(' '))}</p>`); para = [] }
    }
    const closeList = () => { if (listType) { out.push(`</${listType}>`); listType = null } }

    for (const raw of lines) {
        const line = raw.trim()
        if (!line) { flushPara(); closeList(); continue }

        const ul = line.match(/^[-*+]\s+(.*)$/)
        if (ul) {
            flushPara()
            if (listType !== 'ul') { closeList(); out.push(`<ul ${ULS}>`); listType = 'ul' }
            out.push(`<li ${LIS}>${inline(ul[1])}</li>`)
            continue
        }

        const ol = line.match(/^\d+[.)]\s+(.*)$/)
        if (ol) {
            flushPara()
            if (listType !== 'ol') { closeList(); out.push(`<ol ${OLS}>`); listType = 'ol' }
            out.push(`<li ${LIS}>${inline(ol[1])}</li>`)
            continue
        }

        if (listType) closeList()
        para.push(line)
    }
    flushPara(); closeList()

    // Supprimer le margin-bottom du dernier élément de bloc
    return out.join('').replace(/margin:0 0 5px([^"]*)"(?=[^<]*<\/(?:p|li)>[^<]*(?:<\/(?:ul|ol)>)?[^<]*$)/, 'margin:0$1"')
}

// ── Modale de confirmation suppression (UX-07) ──
const showDeleteConfirm = ref(false)

function askDelete() {
    showDeleteConfirm.value = true
}

function cancelDelete() {
    showDeleteConfirm.value = false
}

async function executeDelete() {
    showDeleteConfirm.value = false
    try {
        await fetch(route('oracle.clear'), {
            method: 'DELETE',
            headers: { Accept: 'application/json', 'X-XSRF-TOKEN': xsrfToken() },
            credentials: 'same-origin',
        })
        messages.value = []
    } catch (e) { /* ignore */ }
}

// ── Suggestions conversation vide (UX-04) ──
function sendSuggestion(text) {
    draft.value = text
    send()
}

let currentAbortController = null

const open = ref(false)
const oraclePanel = ref(null)
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
        if (currentAbortController) currentAbortController.abort()
        currentAbortController = new AbortController()
        const r = await fetch(route('oracle.message'), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
                'X-XSRF-TOKEN': xsrfToken(),
            },
            credentials: 'same-origin',
            body: JSON.stringify({ message: text }),
            signal: currentAbortController.signal,
        })
        if (!r.ok) {
            const body = await r.text().catch(() => '')
            throw new Error(`HTTP ${r.status} — ${body.slice(0, 120)}`)
        }
        const data = await r.json()
        messages.value.push({ role: 'assistant', content: data.reply?.content ?? '…' })
    } catch (e) {
        error.value = `${T.value.errorPrefix} (${e.message}). ${T.value.errorRetry}`
    } finally {
        sending.value = false
        scrollToBottom()
    }
}

function clearConversation() {
    askDelete()
}

function onKeydown(e) {
    // Entrée envoie, Maj+Entrée = nouvelle ligne.
    if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault()
        send()
    }
}

// FE-C4: Focus trap panneau Oracle
function trapFocusOracle(e) {
    if (!oraclePanel.value) return
    const f = oraclePanel.value.querySelectorAll(
        'a[href], button:not([disabled]), input, select, textarea, [tabindex]:not([tabindex="-1"])'
    )
    if (!f.length) return
    const first = f[0], last = f[f.length - 1]
    if (e.key === 'Tab') {
        if (e.shiftKey && document.activeElement === first) {
            e.preventDefault(); last.focus()
        } else if (!e.shiftKey && document.activeElement === last) {
            e.preventDefault(); first.focus()
        }
    }
    if (e.key === 'Escape') { open.value = false }
}

watch(open, (isOpen) => {
    if (isOpen) {
        nextTick(() => {
            document.addEventListener('keydown', trapFocusOracle)
            // Focus initial sur le textarea de saisie
            oraclePanel.value?.querySelector('textarea, button')?.focus()
        })
    } else {
        document.removeEventListener('keydown', trapFocusOracle)
    }
})

watch(messages, scrollToBottom, { deep: true })

onBeforeUnmount(() => {
    if (currentAbortController) currentAbortController.abort()
    document.removeEventListener('keydown', trapFocusOracle)
})
</script>

<template>
    <div class="oracle-root">
        <!-- Panneau -->
        <transition name="oracle-pop">
            <section ref="oraclePanel" v-if="open" class="oracle-panel" role="dialog" aria-modal="true" aria-labelledby="oracle-panel-title">
                <header class="oracle-head">
                    <div class="oracle-head-id">
                        <span class="oracle-sigil">&#10022;</span>
                        <div>
                            <p class="oracle-name" id="oracle-panel-title">{{ T.name }}</p>
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
                        <p class="oracle-welcome-title">{{ T.welcomeTitle }}</p>
                        <p class="oracle-welcome-text">
                            {{ T.welcomeText }}
                        </p>

                        <!-- Suggestions (UX-04) -->
                        <div class="oracle-suggestions">
                            <p class="oracle-suggestions-label">{{ T.suggestionsLabel }}</p>
                            <div class="oracle-suggestions-list">
                                <button
                                    v-for="s in T.suggestions"
                                    :key="s"
                                    @click="sendSuggestion(s)"
                                    class="oracle-suggestion-chip"
                                >{{ s }}</button>
                            </div>
                        </div>
                    </div>

                    <div
                        v-for="(m, i) in messages"
                        :key="i"
                        class="oracle-msg"
                        :class="m.role === 'user' ? 'oracle-msg--user' : 'oracle-msg--oracle'"
                    >
                        <div class="oracle-bubble">
                            <div v-if="m.role === 'assistant'"
                                 style="font-size:13.5px;line-height:1.5;color:inherit;word-break:break-word"
                                 v-html="renderOracle(m.content)"></div>
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
                        :placeholder="T.placeholder"
                        class="oracle-textarea"
                        @keydown="onKeydown"
                    ></textarea>
                    <button class="oracle-send" :disabled="sending || !draft.trim()" @click="send" aria-label="Envoyer">
                        &#10148;
                    </button>
                </footer>

                <p style="margin:0;padding:0.5rem 0.9rem 0.7rem;font-size:10.5px;line-height:1.5;color:var(--pt-text-muted,#8C7A5E);text-align:center">
                    {{ T.disclaimer }}
                </p>
            </section>
        </transition>

        <!-- Modale confirmation suppression (UX-07) -->
        <Transition name="fade">
            <div
                v-if="showDeleteConfirm"
                class="oracle-confirm-overlay"
                @click.self="cancelDelete"
            >
                <div class="oracle-confirm-dialog" role="dialog" aria-modal="true">
                    <p class="oracle-confirm-title">Effacer la conversation ?</p>
                    <p class="oracle-confirm-body">Cette action est irréversible.</p>
                    <div class="oracle-confirm-actions">
                        <button @click="cancelDelete" class="oracle-confirm-cancel">Annuler</button>
                        <button @click="executeDelete" class="oracle-confirm-delete">Effacer</button>
                    </div>
                </div>
            </div>
        </Transition>

        <!-- Bulle flottante -->
        <button class="oracle-fab" :class="{ 'oracle-fab--open': open }" @click="toggle" :aria-label="T.fabLabel">
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
    word-wrap: break-word;
    overflow-wrap: break-word;
}
/* pre-wrap uniquement pour les messages user (texte brut) */
.oracle-msg--user .oracle-bubble { white-space: pre-wrap; }
.oracle-msg--user .oracle-bubble {
    background: linear-gradient(180deg, var(--or-gold), var(--or-gold-dark));
    color: #FBF6EA;
    border-bottom-right-radius: 4px;
    box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.2);
}
.oracle-msg--oracle .oracle-bubble {
    background: #FFFFFF;
    color: var(--or-ink);
    border: 1px solid rgba(166, 117, 32, 0.45);
    border-bottom-left-radius: 4px;
    box-shadow: 0 1px 4px rgba(42, 30, 8, 0.1);
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

/* ── Suggestions (UX-04) ── */
.oracle-suggestions {
    margin-top: 1.25rem;
    text-align: left;
}
.oracle-suggestions-label {
    font-family: var(--font-body, 'Inter', sans-serif);
    font-size: 11px;
    color: var(--or-gold-dark);
    opacity: 0.7;
    margin-bottom: 0.5rem;
    letter-spacing: 0.04em;
}
.oracle-suggestions-list {
    display: flex;
    flex-direction: column;
    gap: 0.4rem;
}
.oracle-suggestion-chip {
    text-align: left;
    font-family: var(--font-body, 'Inter', sans-serif);
    font-size: 12.5px;
    padding: 7px 12px;
    border-radius: 10px;
    background: rgba(166, 117, 32, 0.09);
    border: 1px solid rgba(166, 117, 32, 0.25);
    color: var(--or-ink);
    cursor: pointer;
    transition: background 0.15s, border-color 0.15s;
    line-height: 1.4;
}
.oracle-suggestion-chip:hover {
    background: rgba(166, 117, 32, 0.18);
    border-color: rgba(166, 117, 32, 0.45);
}

/* ── Modale confirmation suppression (UX-07) ── */
.oracle-confirm-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.5);
    z-index: 9999;
    display: flex;
    align-items: center;
    justify-content: center;
}
.oracle-confirm-dialog {
    background: #FBF3DF;
    border: 1px solid rgba(166, 117, 32, 0.35);
    border-radius: 14px;
    padding: 1.5rem;
    max-width: 320px;
    margin: 0 1rem;
    box-shadow: 0 12px 40px rgba(42, 30, 8, 0.28);
}
.oracle-confirm-title {
    font-family: var(--font-display, sans-serif);
    font-weight: 700;
    font-size: 15px;
    color: var(--or-ink);
    margin: 0 0 0.4rem;
}
.oracle-confirm-body {
    font-family: var(--font-body, 'Inter', sans-serif);
    font-size: 13px;
    color: #6B5A3E;
    margin: 0 0 1.1rem;
    line-height: 1.5;
}
.oracle-confirm-actions {
    display: flex;
    gap: 0.5rem;
    justify-content: flex-end;
}
.oracle-confirm-cancel {
    padding: 0.45rem 1rem;
    font-size: 13px;
    border: 1px solid rgba(166, 117, 32, 0.3);
    border-radius: 8px;
    background: transparent;
    color: #6B5A3E;
    cursor: pointer;
    transition: background 0.15s;
}
.oracle-confirm-cancel:hover { background: rgba(166, 117, 32, 0.1); }
.oracle-confirm-delete {
    padding: 0.45rem 1rem;
    font-size: 13px;
    border: none;
    border-radius: 8px;
    background: #B03020;
    color: #fff;
    cursor: pointer;
    font-weight: 600;
    transition: background 0.15s;
}
.oracle-confirm-delete:hover { background: #8B2316; }

/* ── Contenu oracle ─────────────────────────────────────────────────
   Les styles de typographie sont en inline styles directement dans le
   HTML généré par renderOracle() — pas de dépendance aux règles scoped. */

/* ── Transition fade (modale) ── */
.fade-enter-active, .fade-leave-active { transition: opacity 0.18s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }

/* ── Parcours Corporate : conseiller banque privée (marine / blanc / laiton) ── */
html[data-theme="corporate"] .oracle-fab {
    background: var(--color-accent);
    color: #D4B368;
    border-color: rgba(176,141,63,0.5);
    box-shadow: 0 6px 22px rgba(21,34,56,0.30);
}
html[data-theme="corporate"] .oracle-fab:hover { box-shadow: 0 10px 28px rgba(21,34,56,0.38); }
html[data-theme="corporate"] .oracle-fab--open {
    background: var(--color-accent);
    color: #F5F7FA;
}
html[data-theme="corporate"] .oracle-panel {
    background: #FFFFFF;
    border-color: var(--border-mid);
    box-shadow: 0 18px 48px rgba(21,34,56,0.25);
}
html[data-theme="corporate"] .oracle-head {
    background: #F5F7FA;
    border-bottom-color: var(--border-light);
}
html[data-theme="corporate"] .oracle-sigil {
    background: var(--color-accent);
    color: #D4B368;
    border-color: rgba(176,141,63,0.4);
}
html[data-theme="corporate"] .oracle-icon-btn:hover { background: var(--bg-elevated); }
html[data-theme="corporate"] .oracle-msg--user .oracle-bubble {
    background: var(--color-accent);
    color: #F5F7FA;
    box-shadow: none;
}
html[data-theme="corporate"] .oracle-msg--oracle .oracle-bubble {
    background: #F5F7FA;
    border-color: var(--border-mid);
    box-shadow: 0 1px 4px rgba(21,34,56,0.08);
}
html[data-theme="corporate"] .oracle-input {
    background: #F5F7FA;
    border-top-color: var(--border-light);
}
html[data-theme="corporate"] .oracle-textarea {
    background: #FFFFFF;
    border-color: var(--border-mid);
}
html[data-theme="corporate"] .oracle-textarea:focus {
    border-color: var(--color-accent);
    box-shadow: 0 0 0 2px rgba(22,50,92,0.14);
}
html[data-theme="corporate"] .oracle-send {
    background: var(--color-accent);
    color: #F5F7FA;
}
html[data-theme="corporate"] .oracle-suggestions-label { color: var(--text-muted); }
html[data-theme="corporate"] .oracle-suggestion-chip {
    background: rgba(22,50,92,0.05);
    border-color: var(--border-mid);
}
html[data-theme="corporate"] .oracle-suggestion-chip:hover {
    background: rgba(22,50,92,0.10);
    border-color: var(--border-strong);
}
html[data-theme="corporate"] .oracle-confirm-dialog {
    background: #FFFFFF;
    border-color: var(--border-mid);
    box-shadow: 0 12px 40px rgba(21,34,56,0.25);
}
html[data-theme="corporate"] .oracle-confirm-body,
html[data-theme="corporate"] .oracle-confirm-cancel { color: var(--text-secondary); }
html[data-theme="corporate"] .oracle-confirm-cancel { border-color: var(--border-mid); }
html[data-theme="corporate"] .oracle-confirm-cancel:hover { background: var(--bg-elevated); }
</style>

