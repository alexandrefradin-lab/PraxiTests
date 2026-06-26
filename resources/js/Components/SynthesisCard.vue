<script setup>
/**
 * SynthesisCard — carte « synthèse IA » partagée par toutes les pages résultats.
 *
 * Rend la synthèse générée par l'IA en Markdown (via MarkdownText) dans une
 * carte parchemin homogène. Remplace les anciens blocs `white-space:pre-line`
 * dupliqués dans chaque plugin. Premier composant de la future bibliothèque
 * de composants Vue mutualisés.
 *
 * Usage :
 *   <SynthesisCard :source="result.ai_synthesis" title="Ta synthèse" />
 */
import MarkdownText from '@/Components/MarkdownText.vue'

defineProps({
    source: { type: String, default: '' },
    title:  { type: String, default: 'Ta synthèse' },
})
</script>

<template>
    <section v-if="source" class="pt-card synthesis-card">
        <h2 class="synthesis-card__title">{{ title }}</h2>
        <MarkdownText :source="source" />
    </section>
</template>

<style scoped>
.synthesis-card {
    padding: 1.5rem;
    margin-bottom: 1rem;
}
.synthesis-card__title {
    font-family: var(--font-display);
    font-size: 18px;
    font-weight: 600;
    color: var(--pt-navy);
    margin-bottom: 1rem;
}
</style>
