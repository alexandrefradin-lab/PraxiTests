<script setup>
/**
 * En-tête de colonne triable (tri serveur).
 * Émet 'sort' avec le champ ; le parent gère sort/dir dans ses filtres.
 */
const props = defineProps({
    field: { type: String, required: true },
    sort:  { type: String, default: '' },
    dir:   { type: String, default: 'desc' },
    align: { type: String, default: 'left' },
})

const emit = defineEmits(['sort'])

const arrow = () => props.sort === props.field ? (props.dir === 'asc' ? '↑' : '↓') : ''
</script>

<template>
    <th class="ac-th px-5 py-3 select-none cursor-pointer" :class="`text-${align}`"
        role="columnheader"
        :aria-sort="sort === field ? (dir === 'asc' ? 'ascending' : 'descending') : 'none'"
        @click="emit('sort', field)">
        <span class="inline-flex items-center gap-1">
            <slot />
            <span aria-hidden="true" style="color:var(--color-primary)">{{ arrow() }}</span>
        </span>
    </th>
</template>
