<script setup>
/**
 * Disclaimer — encart d'avertissement bienveillant réutilisable.
 *
 * Carte parchemin à liseré or. Le texte est fourni via le slot par défaut
 * (HTML autorisé : <strong>, etc.). Mutualise les blocs « ceci n'est pas un
 * diagnostic » répétés dans praxifocus, praxisens, praxizen…
 *
 * Usage : <Disclaimer><strong>Ceci n'est pas un diagnostic.</strong> …</Disclaimer>
 */
defineProps({
    accent: { type: String, default: 'var(--pt-gold)' },
})
</script>

<template>
    <div class="pt-disclaimer pt-card" :style="{ borderLeftColor: accent }">
        <p class="pt-disclaimer__text"><slot /></p>
    </div>
</template>

<style scoped>
.pt-disclaimer {
    padding: 1rem 1.25rem;
    margin-bottom: 1.25rem;
    border-left: 4px solid var(--pt-gold);
    background: var(--pt-gold-pale);
}
.pt-disclaimer__text {
    font-size: 13px;
    line-height: 1.6;
    color: var(--pt-text);
    margin: 0;
}
.pt-disclaimer__text :deep(strong) { font-weight: 600; color: var(--pt-navy); }
</style>
