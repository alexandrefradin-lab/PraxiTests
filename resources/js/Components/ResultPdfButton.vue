<script setup>
/**
 * ResultPdfButton — CTA de téléchargement du Grimoire (PDF), unifié sur toutes
 * les pages de résultats. Carte parchemin + bouton encre.
 *
 * Usage : <ResultPdfButton :attempt-id="attempt.id" />
 */
defineProps({
    attemptId: { type: [Number, String], required: true },
    caption:   { type: String, default: "Ton Grimoire complet t'attend." },
    label:     { type: String, default: 'Télécharger mon Grimoire de Synthèse (PDF)' },
})
</script>

<template>
    <div class="rpdf">
        <p class="rpdf-caption">{{ caption }}</p>
        <a :href="route('results.pdf', attemptId)" class="rpdf-btn">{{ label }}</a>
    </div>
</template>

<style scoped>
.rpdf {
    text-align: center;
    margin-top: 3rem;
    padding: 2rem;
    background: var(--bg-elevated);
    border-radius: 12px;
    border: 1px solid var(--glass-border);
}
.rpdf-caption {
    font-family: var(--font-body);
    font-size: 13px;
    color: var(--text-secondary);
    margin-bottom: 1rem;
}
.rpdf-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0.875rem 2rem;
    background: var(--color-accent);
    color: var(--bg-base);
    font-family: var(--font-display);
    font-size: 15px;
    font-weight: 600;
    border: 1px solid var(--color-primary-dark);
    border-radius: 8px;
    text-decoration: none;
    letter-spacing: 0.01em;
    box-shadow: 0 4px 14px rgba(42,30,8,0.25), inset 0 1px 0 rgba(166,117,32,0.25);
    transition: background 0.2s ease, transform 0.1s ease, box-shadow 0.2s ease;
}
.rpdf-btn:hover {
    background: #2A1E08;
    transform: translateY(-1px);
    box-shadow: 0 6px 20px rgba(42,30,8,0.3), inset 0 1px 0 rgba(166,117,32,0.3);
}
.rpdf-btn:active { transform: translateY(0); }
</style>
