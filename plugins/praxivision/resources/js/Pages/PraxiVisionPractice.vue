<script setup>
import { computed, ref } from 'vue'
import { Link, Head, useForm } from '@inertiajs/vue3'
import CandidateLayout from '@/Layouts/CandidateLayout.vue'

const props = defineProps({
    practice: { type: Object, required: true },
    state:    { type: Object, default: () => ({}) },
    nav:      { type: Object, default: () => ({ prev: null, next: null }) },
    eclatsPerPractice: { type: Number, default: 20 },
})

const iconFor = (name) => ({
    compass: '🧭', target: '🎯', ear: '👂', message: '💬', handshake: '🤝',
    gift: '🎁', flame: '🔥', shield: '🛡️', scale: '⚖️', users: '👥',
    clock: '⏳', book: '📖', heart: '❤️', rocket: '🚀', eye: '👁️',
    seedling: '🌱', anchor: '⚓', map: '🗺️', lightbulb: '💡', sun: '☀️',
}[name] ?? '✨')

const renderMarkdown = (md) => {
    if (!md) return ''
    const esc   = (s) => s.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
    const inline = (s) => esc(s)
        .replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>')
        .replace(/\*(.+?)\*/g, '<em>$1</em>')

    const lines = md.split('\n')
    let html = '', list = null
    const closeList = () => { if (list) { html += `</${list}>`; list = null } }

    for (const raw of lines) {
        const line = raw.trimEnd()
        if (/^### /.test(line)) { closeList(); html += `<h3>${inline(line.slice(4))}</h3>`; continue }
        if (/^## /.test(line))  { closeList(); html += `<h2>${inline(line.slice(3))}</h2>`; continue }
        if (/^---$/.test(line)) { closeList(); html += '<hr>'; continue }
        const ol = line.match(/^\d+\.\s+(.*)/)
        if (ol) { if (list !== 'ol') { closeList(); list = 'ol'; html += '<ol>' } html += `<li>${inline(ol[1])}</li>`; continue }
        const ul = line.match(/^[-*]\s+(.*)/)
        if (ul) { if (list !== 'ul') { closeList(); list = 'ul'; html += '<ul>' } html += `<li>${inline(ul[1])}</li>`; continue }
        if (line === '') { closeList(); continue }
        closeList(); html += `<p>${inline(line)}</p>`
    }
    closeList()
    return html
}

const bodyHtml = computed(() => renderMarkdown(props.practice.body))

const form = useForm({
    felt_score: props.state?.felt_score ?? null,
    notes: props.state?.notes ?? '',
})

const done = ref(props.state?.completed ?? false)

const submit = () => {
    const wasDone = done.value
    form.post(route('praxivision.complete', props.practice.day), {
        preserveScroll: true,
        onSuccess: () => {
            done.value = true
            if (!wasDone) {
                import('canvas-confetti').then(({ default: confetti }) => {
                    confetti({ particleCount: 100, spread: 70, origin: { y: 0.7 } })
                }).catch(() => {})
            }
        },
    })
}
</script>

<template>
    <CandidateLayout>
        <Head :title="`Jour ${practice.day} — ${practice.title}`" />

        <div class="max-w-2xl mx-auto">

            <Link :href="route('praxivision.index')" class="text-sm" style="display: inline-block; margin-bottom: 1.25rem; color: var(--text-secondary); text-decoration: none;">
                ← L'Éveilleur
            </Link>

            <p style="font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.06em; color: var(--text-secondary);">
                Jour {{ practice.day }}/60 · {{ practice.theme }} · {{ practice.duration_min }} min
            </p>
            <h1 class="mt-1" style="font-family: var(--font-display); font-size: 2rem; font-weight: 700; color: var(--text-primary); letter-spacing: -0.02em; line-height: 1.15;">
                {{ iconFor(practice.icon) }} {{ practice.title }}
            </h1>
            <p class="mt-2" style="font-size: 1rem; color: var(--text-secondary); font-style: italic;">
                {{ practice.summary }}
            </p>

            <div
                class="pv-prose mt-6"
                v-html="bodyHtml"
                style="font-family: var(--font-body); color: var(--text-primary); line-height: 1.7;"
            ></div>

            <!-- Le micro-défi du jour -->
            <div class="mt-8" style="background: rgba(30,58,95,.06); border: 1px dashed #2d5986; border-radius: var(--r-md, 12px); padding: 1.1rem 1.3rem;">
                <p style="font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.06em; font-weight: 700; color: #2d5986;">
                    🎯 Le micro-défi du jour
                </p>
                <p class="mt-1.5" style="font-family: var(--font-body); font-size: 1rem; color: var(--text-primary); line-height: 1.55;">
                    {{ practice.micro_challenge }}
                </p>
            </div>

            <!-- Marquer comme appliqué -->
            <div class="mt-8" style="background: var(--surface, #fff); border: 1px solid var(--border, #e5e7eb); border-radius: var(--r-md, 12px); padding: 1.25rem 1.5rem;">
                <div v-if="done" class="flex items-center gap-2 mb-3" style="color: #10B981; font-weight: 600;">
                    ✓ Pratique intégrée
                </div>
                <h3 style="font-family: var(--font-display); font-weight: 700; color: var(--text-primary);">
                    {{ done ? 'Mettre à jour ton ressenti' : 'Tu as appliqué cette pratique ?' }}
                </h3>
                <p v-if="!done" class="mt-1" style="font-size: 0.85rem; color: var(--text-secondary);">
                    +{{ eclatsPerPractice }} Éclats à la clé.
                </p>

                <label class="block mt-4" style="font-size: 0.85rem; color: var(--text-secondary);">Ton ressenti (optionnel)</label>
                <div class="flex gap-2 mt-2">
                    <button
                        v-for="n in 5" :key="n" type="button"
                        @click="form.felt_score = n"
                        :style="{
                            width: '40px', height: '40px', borderRadius: '8px',
                            border: '1px solid var(--border, #e5e7eb)',
                            background: form.felt_score === n ? '#1e3a5f' : 'transparent',
                            color: form.felt_score === n ? '#fff' : 'var(--text-secondary)',
                            cursor: 'pointer', fontWeight: 600,
                        }"
                    >{{ n }}</button>
                </div>

                <label class="block mt-4" style="font-size: 0.85rem; color: var(--text-secondary);">Notes (optionnel)</label>
                <textarea
                    v-model="form.notes" rows="3"
                    class="mt-2 w-full"
                    style="border: 1px solid var(--border, #e5e7eb); border-radius: 8px; padding: 0.6rem 0.75rem; font-family: var(--font-body); resize: vertical;"
                    placeholder="Ce que tu as testé, ce que ça a révélé…"
                ></textarea>

                <button
                    @click="submit" :disabled="form.processing"
                    class="mt-4"
                    style="background: #1e3a5f; color: #fff; border: none; padding: 0.65rem 1.4rem; border-radius: 999px; font-weight: 600; cursor: pointer;"
                >
                    {{ done ? 'Enregistrer' : 'Marquer comme intégré' }}
                </button>
            </div>

            <!-- Navigation -->
            <div class="mt-8 flex items-center justify-between" style="font-size: 0.9rem;">
                <Link
                    v-if="nav.prev"
                    :href="route('praxivision.show', nav.prev)"
                    style="color: var(--text-secondary); text-decoration: none;"
                >← Jour {{ nav.prev }}</Link>
                <span v-else></span>

                <Link
                    v-if="nav.next"
                    :href="route('praxivision.show', nav.next)"
                    style="color: #2d5986; font-weight: 600; text-decoration: none;"
                >Jour {{ nav.next }} →</Link>
                <span v-else style="font-size: 0.82rem; color: var(--text-secondary);">La prochaine pratique se débloquera demain 🌙</span>
            </div>

        </div>
    </CandidateLayout>
</template>

<style scoped>
.pv-prose :deep(h2) { font-family: var(--font-display); font-weight: 700; font-size: 1.25rem; margin: 1.6rem 0 0.6rem; color: var(--text-primary); }
.pv-prose :deep(h3) { font-family: var(--font-display); font-weight: 700; font-size: 1.05rem; margin: 1.2rem 0 0.4rem; color: var(--text-primary); }
.pv-prose :deep(p)  { margin: 0.6rem 0; }
.pv-prose :deep(ul), .pv-prose :deep(ol) { margin: 0.6rem 0 0.6rem 1.25rem; }
.pv-prose :deep(li) { margin: 0.3rem 0; }
.pv-prose :deep(strong) { color: var(--text-primary); }
.pv-prose :deep(hr) { border: none; border-top: 1px solid var(--glass-border); margin: 1.5rem 0; }
</style>
