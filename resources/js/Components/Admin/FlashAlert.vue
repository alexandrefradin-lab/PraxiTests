<script setup>
import { usePage } from '@inertiajs/vue3'
import { computed } from 'vue'

/**
 * Bandeau flash unifié (succès / erreur / info / avertissement).
 * Remplace les blocs stylés en hex bruts dispersés dans les pages admin —
 * source unique alignée sur les tokens du design system Parchemin/Or.
 */
const page = usePage()

const flash = computed(() => {
    const f = page.props.flash ?? {}
    if (f.success) return { type: 'success', message: f.success }
    if (f.error)   return { type: 'error',   message: f.error }
    if (f.warning) return { type: 'warning', message: f.warning }
    if (f.info)    return { type: 'info',    message: f.info }
    return null
})

const styles = {
    success: 'background:color-mix(in srgb, var(--color-success) 12%, transparent);border:1px solid color-mix(in srgb, var(--color-success) 30%, transparent);color:var(--color-success)',
    error:   'background:color-mix(in srgb, var(--color-danger) 12%, transparent);border:1px solid color-mix(in srgb, var(--color-danger) 30%, transparent);color:var(--color-danger)',
    warning: 'background:color-mix(in srgb, var(--color-primary) 12%, transparent);border:1px solid color-mix(in srgb, var(--color-primary) 30%, transparent);color:var(--color-primary-dark, #7D5510)',
    info:    'background:color-mix(in srgb, var(--color-signal) 12%, transparent);border:1px solid color-mix(in srgb, var(--color-signal) 30%, transparent);color:var(--color-signal)',
}
</script>

<template>
    <div v-if="flash" class="mb-6 p-4 rounded-lg text-sm" :style="styles[flash.type]" role="status">
        {{ flash.message }}
    </div>
</template>
