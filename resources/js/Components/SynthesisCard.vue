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
        <p class="synthesis-card__disclaimer">
            <strong>Outil d'auto-évaluation et de développement personnel.</strong>
            Cette synthèse est générée par intelligence artificielle, à titre informatif.
            Elle ne constitue pas un avis professionnel et ne remplace pas un psychologue,
            un médecin ou un coach.
        </p>
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
.synthesis-card__disclaimer {
    margin: 1.25rem 0 0;
    padding-top: 0.85rem;
    border-top: 1px solid var(--pt-gold-pale, rgba(166,117,32,0.18));
    font-size: 11.5px;
    line-height: 1.55;
    color: var(--pt-text-muted, #8C7A5E);
}
.synthesis-card__disclaimer strong { color: var(--pt-navy); font-weight: 600; }
</style>
