<script setup>
import { Link } from '@inertiajs/vue3'

/**
 * Pagination des listes admin (props : links du paginator Laravel).
 * SEC-08 : libellés rendus en texte (jamais de v-html) ; les entités
 * HTML des flèches sont converties explicitement.
 */
defineProps({ links: { type: Array, default: () => [] } })

const label = (raw) => String(raw ?? '')
    .replace(/<[^>]*>/g, '')
    .replace(/&laquo;/g, '«')
    .replace(/&raquo;/g, '»')
    .replace(/&hellip;/g, '…')
    .trim()
</script>

<template>
    <nav v-if="links.length > 3" class="flex items-center justify-center gap-1 mt-6" aria-label="Pagination">
        <component :is="link.url ? Link : 'span'" v-for="(link, i) in links" :key="i" :href="link.url ?? ''"
            class="px-3 py-1 text-xs rounded"
            :class="[link.active ? 'acp-active' : 'acp-idle', !link.url && 'opacity-40']"
            :aria-current="link.active ? 'page' : undefined">{{ label(link.label) }}</component>
    </nav>
</template>

<style scoped>
.acp-active {
    background: var(--color-accent);
    color: var(--bg-base);
    font-weight: 600;
}
.acp-idle {
    color: var(--text-secondary);
}
.acp-idle:hover {
    background: var(--bg-elevated);
}
</style>
