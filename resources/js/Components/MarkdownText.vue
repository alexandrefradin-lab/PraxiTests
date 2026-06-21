<script setup>
/**
 * MarkdownText — rendu Markdown léger & sûr pour PraxiQuest.
 *
 * Convertit la synthèse IA (Markdown) en HTML stylé avec les tokens --pt-*.
 * Sans dépendance externe : on échappe d'abord le HTML (anti-XSS), puis on
 * applique un sous-ensemble Markdown (titres, gras, italique, listes, hr,
 * paragraphes, citations). Pensé pour les textes générés par l'IA.
 *
 * Usage :
 *   <MarkdownText :source="result.ai_synthesis" />
 */
import { computed } from 'vue'

const props = defineProps({
    source: { type: String, default: '' },
})

const escapeHtml = (s) => s
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')

// Inline : gras, italique, code, liens markdown
const inline = (s) => escapeHtml(s)
    .replace(/\*\*([^*]+)\*\*/g, '<strong>$1</strong>')
    .replace(/__([^_]+)__/g, '<strong>$1</strong>')
    .replace(/(^|[^*])\*([^*\n]+)\*/g, '$1<em>$2</em>')
    .replace(/`([^`]+)`/g, '<code>$1</code>')
    .replace(/\[([^\]]+)\]\((https?:\/\/[^\s)]+)\)/g, '<a href="$2" target="_blank" rel="noopener">$1</a>')

const html = computed(() => {
    const src = (props.source || '').replace(/\r\n/g, '\n').trim()
    if (!src) return ''

    const lines = src.split('\n')
    const out = []
    let listType = null     // 'ul' | 'ol' | null
    let para = []

    const flushPara = () => {
        if (para.length) {
            out.push(`<p>${inline(para.join(' '))}</p>`)
            para = []
        }
    }
    const closeList = () => {
        if (listType) { out.push(`</${listType}>`); listType = null }
    }

    for (const raw of lines) {
        const line = raw.trim()

        if (!line) { flushPara(); closeList(); continue }

        // Règle horizontale
        if (/^(-{3,}|\*{3,}|_{3,})$/.test(line)) {
            flushPara(); closeList(); out.push('<hr>'); continue
        }

        // Titres
        const h = line.match(/^(#{1,6})\s+(.*)$/)
        if (h) {
            flushPara(); closeList()
            const level = Math.min(h[1].length + 1, 6) // # -> h2 (h1 réservé au titre de page)
            out.push(`<h${level}>${inline(h[2])}</h${level}>`)
            continue
        }

        // Liste à puces
        const ul = line.match(/^[-*+]\s+(.*)$/)
        if (ul) {
            flushPara()
            if (listType !== 'ul') { closeList(); out.push('<ul>'); listType = 'ul' }
            out.push(`<li>${inline(ul[1])}</li>`)
            continue
        }

        // Liste numérotée
        const ol = line.match(/^\d+[.)]\s+(.*)$/)
        if (ol) {
            flushPara()
            if (listType !== 'ol') { closeList(); out.push('<ol>'); listType = 'ol' }
            out.push(`<li>${inline(ol[1])}</li>`)
            continue
        }

        // Citation
        const bq = line.match(/^>\s?(.*)$/)
        if (bq) {
            flushPara(); closeList()
            out.push(`<blockquote>${inline(bq[1])}</blockquote>`)
            continue
        }

        // Paragraphe (accumulation)
        if (listType) closeList()
        para.push(line)
    }
    flushPara(); closeList()
    return out.join('\n')
})
</script>

<template>
    <div class="pt-md" v-html="html"></div>
</template>

<style scoped>
.pt-md {
    font-size: 15px;
    line-height: 1.75;
    color: var(--pt-text);
}
.pt-md :deep(h2) {
    font-family: 'Playfair Display', serif;
    font-size: 20px;
    font-weight: 600;
    color: var(--pt-navy);
    margin: 1.6rem 0 .6rem;
    line-height: 1.3;
}
.pt-md :deep(h2:first-child) { margin-top: 0; }
.pt-md :deep(h3) {
    font-size: 15px;
    font-weight: 600;
    color: var(--pt-gold-hover);
    text-transform: uppercase;
    letter-spacing: .04em;
    margin: 1.4rem 0 .4rem;
}
.pt-md :deep(h4) {
    font-size: 14px;
    font-weight: 600;
    color: var(--pt-text);
    margin: 1.1rem 0 .3rem;
}
.pt-md :deep(p) { margin: 0 0 .85rem; }
.pt-md :deep(strong) { font-weight: 600; color: var(--pt-navy); }
.pt-md :deep(em) { font-style: italic; }
.pt-md :deep(code) {
    font-family: 'Space Mono', monospace;
    font-size: .88em;
    background: var(--pt-gold-pale);
    padding: 1px 5px;
    border-radius: 4px;
}
.pt-md :deep(a) { color: var(--pt-gold-hover); text-decoration: underline; }
.pt-md :deep(ul), .pt-md :deep(ol) {
    margin: .4rem 0 1rem;
    padding-left: 1.3rem;
}
.pt-md :deep(li) { margin: .3rem 0; padding-left: .2rem; }
.pt-md :deep(ul li)::marker { color: var(--pt-gold); }
.pt-md :deep(ol li)::marker { color: var(--pt-gold); font-weight: 600; }
.pt-md :deep(hr) {
    border: none;
    border-top: 1px solid var(--pt-border);
    margin: 1.5rem 0;
}
.pt-md :deep(blockquote) {
    border-left: 2px solid var(--pt-gold-border);
    padding: .2rem 0 .2rem 1rem;
    margin: .8rem 0;
    color: var(--pt-text-muted);
    font-style: italic;
}
</style>
