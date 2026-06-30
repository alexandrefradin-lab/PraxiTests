<script setup>
/**
 * RestitutionHeader — en-tête unifié des pages de résultats (« restitution »).
 * Kicker doré + titre Space Grotesk + sous-titre + filet décoratif or.
 *
 * Usage :
 *   <RestitutionHeader kicker="Ta restitution" title="La Constellation des Talents"
 *                      subtitle="Bilan révélé le 30 juin 2026 — synthèse augmentée par IA" />
 */
defineProps({
    kicker:   { type: String, default: 'Ta restitution' },
    title:    { type: String, required: true },
    subtitle: { type: String, default: '' },
})
</script>

<template>
    <header class="rh">
        <span class="rh-kicker">✦ {{ kicker }}</span>
        <h1 class="rh-title">{{ title }}</h1>
        <p v-if="subtitle" class="rh-sub">{{ subtitle }}</p>
        <div class="rh-divider" aria-hidden="true">
            <i></i>
            <svg width="12" height="12" viewBox="0 0 16 16" fill="none">
                <path d="M8 0L9.6 6.4L16 8L9.6 9.6L8 16L6.4 9.6L0 8L6.4 6.4L8 0Z" fill="var(--color-primary)" opacity="0.55"/>
            </svg>
            <i></i>
        </div>
    </header>
</template>

<style scoped>
.rh {
    text-align: center;
    margin-bottom: 2rem;
}
.rh-kicker {
    font-family: var(--font-data);
    font-size: 11px;
    letter-spacing: 0.2em;
    text-transform: uppercase;
    color: var(--color-primary);
}
.rh-title {
    font-family: var(--font-display);
    font-size: clamp(1.9rem, 4vw, 2.4rem);
    font-weight: 700;
    letter-spacing: -0.02em;
    color: var(--text-primary);
    margin: 0.5rem 0 0.4rem;
    line-height: 1.1;
}
.rh-sub {
    font-size: 13px;
    color: var(--text-secondary);
    margin: 0;
}
.rh-divider {
    display: flex;
    align-items: center;
    gap: 12px;
    max-width: 440px;
    margin: 1.4rem auto 0;
}
.rh-divider i {
    flex: 1;
    height: 1px;
    background: linear-gradient(to right, transparent, var(--color-primary), transparent);
}
</style>
