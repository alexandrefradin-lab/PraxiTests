<script setup>
/**
 * PathTier — section d'un palier de restitution des pistes PTP.
 *
 * Trois paliers : accessible (0 formation) / ptp (≤ 1 an, finançable, cœur de l'offre)
 * / horizon (> 1 an). Le palier « ptp » est mis en avant. Replie « horizon » par défaut.
 * Voir PLAN-PISTES-DYNAMIQUES-PTP.md.
 */
import { ref } from 'vue'
import PathCard from '@/Components/PathCard.vue'

const props = defineProps({
    tier:  { type: String, required: true },     // 'accessible' | 'ptp' | 'horizon'
    paths: { type: Array,  default: () => [] },
})

const emit = defineEmits(['unlock'])

const META = {
    accessible: {
        title: 'Accessible maintenant',
        subtitle: 'Sans formation supplémentaire.',
        badge: '0 formation',
        collapsedByDefault: false,
        featured: false,
    },
    ptp: {
        title: 'À portée via un PTP',
        subtitle: 'Finançable en ≤ 1 an dans le cadre d\'un Projet de Transition Professionnelle.',
        badge: '≤ 1 an · finançable',
        collapsedByDefault: false,
        featured: true,
    },
    horizon: {
        title: 'Horizon long',
        subtitle: 'Ambition au-delà du PTP (plus d\'un an de formation).',
        badge: '> 1 an',
        collapsedByDefault: true,
        featured: false,
    },
}

const meta = META[props.tier] ?? META.horizon
const open = ref(!meta.collapsedByDefault)
</script>

<template>
    <section v-if="paths.length" class="path-tier" :class="{ 'path-tier--featured': meta.featured }">
        <button type="button" class="path-tier__head" @click="open = !open">
            <div>
                <h2 class="path-tier__title">
                    {{ meta.title }}
                    <span class="path-tier__badge">{{ meta.badge }}</span>
                    <span class="path-tier__count">{{ paths.length }}</span>
                </h2>
                <p class="path-tier__subtitle">{{ meta.subtitle }}</p>
            </div>
            <span class="path-tier__chevron" :class="{ 'is-open': open }">⌄</span>
        </button>

        <div v-show="open" class="path-tier__grid">
            <PathCard v-for="p in paths" :key="p.id ?? p.slug ?? p.title" :path="p" @unlock="emit('unlock', $event)" />
        </div>
    </section>
</template>

<style scoped>
.path-tier { margin-bottom: 1.75rem; }
.path-tier--featured {
    background: var(--pt-gold-pale);
    border: .5px solid var(--pt-gold-border);
    border-radius: 16px;
    padding: 1.25rem;
}
.path-tier__head {
    width: 100%;
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 1rem;
    background: none;
    border: none;
    padding: 0 0 1rem;
    cursor: pointer;
    text-align: left;
}
.path-tier__title {
    font-family: 'Playfair Display', serif;
    font-size: 19px;
    font-weight: 600;
    color: var(--pt-navy);
    margin: 0;
    display: flex;
    align-items: center;
    gap: .6rem;
    flex-wrap: wrap;
}
.path-tier__badge {
    font-family: 'Inter', sans-serif;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: .04em;
    color: var(--pt-gold-hover);
    background: var(--pt-gold-pale);
    border: .5px solid var(--pt-gold-border);
    border-radius: 99px;
    padding: 2px 9px;
}
.path-tier--featured .path-tier__badge { background: #fff; }
.path-tier__count {
    font-family: 'Space Mono', monospace;
    font-size: 12px;
    color: var(--pt-text-light);
}
.path-tier__subtitle { font-size: 13px; color: var(--pt-text-muted); margin: 4px 0 0; }
.path-tier__chevron { font-size: 20px; color: var(--pt-text-light); transition: transform .2s; }
.path-tier__chevron.is-open { transform: rotate(180deg); }
.path-tier__grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: .85rem;
}
</style>
