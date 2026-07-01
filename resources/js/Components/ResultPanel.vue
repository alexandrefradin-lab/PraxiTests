<script setup>
/**
 * ResultPanel — panneau « constellation » unifié pour les visualisations de
 * résultats (radar, quadrant, jauge…). Cadre à ornements d'angle dorés.
 * Variante sombre (encre) par défaut pour l'effet constellation du mockup,
 * ou claire (parchemin) via :dark="false".
 *
 * Usage :
 *   <ResultPanel label="Tes 6 dimensions">
 *     <RadarChart :axes="axes" dark />
 *   </ResultPanel>
 */
defineProps({
    label: { type: String, default: '' },
    dark:  { type: Boolean, default: true },
})
</script>

<template>
    <section class="rp" :class="dark ? 'rp--dark' : 'rp--light'">
        <span class="rp-orn tl" aria-hidden="true"></span>
        <span class="rp-orn tr" aria-hidden="true"></span>
        <span class="rp-orn bl" aria-hidden="true"></span>
        <span class="rp-orn br" aria-hidden="true"></span>
        <div v-if="label" class="rp-label">{{ label }}</div>
        <slot />
    </section>
</template>

<style scoped>
.rp {
    position: relative;
    border-radius: var(--r-lg);
    padding: 1.4rem 1.5rem 1.6rem;
    overflow: hidden;
}
.rp--dark {
    background: radial-gradient(ellipse at 50% 18%, #241a0e 0%, var(--color-accent) 60%, #120c04 100%);
    border: 1px solid var(--color-primary-dark);
    box-shadow: 0 10px 26px rgba(42,30,8,0.28), inset 0 1px 0 rgba(166,117,32,0.25);
    /* Panneau sombre : bascule les tokens texte en clair (comme .ac-card-dark)
       pour que tout contenu en var(--pt-navy)/var(--pt-text) reste lisible. */
    color: #F4ECD8;
    --pt-navy:        #F4ECD8;
    --pt-navy-mid:    rgba(240,232,212,0.85);
    --pt-navy-light:  rgba(240,232,212,0.72);
    --pt-text:        #F4ECD8;
    --pt-text-muted:  rgba(240,232,212,0.72);
    --pt-text-light:  rgba(240,232,212,0.55);
    --text-primary:   #F4ECD8;
    --text-secondary: rgba(240,232,212,0.72);
    --text-muted:     rgba(240,232,212,0.55);
}
.rp--light {
    background: linear-gradient(180deg, var(--bg-base), var(--bg-surface));
    border: 1px solid var(--border-light);
    box-shadow: 0 2px 4px rgba(42,30,8,0.06), 0 10px 22px rgba(42,30,8,0.09), inset 0 1px 0 rgba(255,255,255,0.45);
}
.rp-label {
    font-family: var(--font-data);
    font-size: 10px;
    letter-spacing: 0.16em;
    text-transform: uppercase;
    text-align: center;
    margin-bottom: 0.5rem;
}
.rp--dark .rp-label { color: var(--color-primary); }
.rp--light .rp-label { color: var(--text-muted); }

/* Ornements d'angle dorés */
.rp-orn {
    position: absolute;
    width: 16px;
    height: 16px;
    border: 1.5px solid var(--color-primary);
    opacity: 0.6;
    pointer-events: none;
}
.rp-orn.tl { top: 9px; left: 9px; border-right: 0; border-bottom: 0; border-radius: 3px 0 0 0; }
.rp-orn.tr { top: 9px; right: 9px; border-left: 0; border-bottom: 0; border-radius: 0 3px 0 0; }
.rp-orn.bl { bottom: 9px; left: 9px; border-right: 0; border-top: 0; border-radius: 0 0 0 3px; }
.rp-orn.br { bottom: 9px; right: 9px; border-left: 0; border-top: 0; border-radius: 0 0 3px 0; }
</style>
