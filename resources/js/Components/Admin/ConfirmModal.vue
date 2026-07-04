<script setup>
/**
 * Modale de confirmation au thème Parchemin/Or — remplace confirm() natif.
 *
 * Usage :
 *   <ConfirmModal v-model:show="confirming" title="Envoyer ?" danger
 *                 @confirm="doIt()">Texte explicatif.</ConfirmModal>
 */
defineProps({
    show:         { type: Boolean, default: false },
    title:        { type: String, required: true },
    confirmLabel: { type: String, default: 'Confirmer' },
    cancelLabel:  { type: String, default: 'Annuler' },
    danger:       { type: Boolean, default: false },
})

const emit = defineEmits(['update:show', 'confirm'])

const close = () => emit('update:show', false)
const confirm = () => { emit('confirm'); close() }
</script>

<template>
    <Teleport to="body">
        <div v-if="show" class="acm-overlay" @click.self="close" @keydown.esc="close">
            <div class="pt-card acm-panel" role="dialog" aria-modal="true" :aria-label="title">
                <h3 class="text-lg font-semibold mb-2" style="font-family:var(--font-display);color:var(--text-primary)">
                    {{ title }}
                </h3>
                <p class="text-sm mb-6" style="color:var(--text-secondary)"><slot /></p>
                <div class="flex justify-end gap-3">
                    <button type="button" class="ac-btn-ghost" @click="close">{{ cancelLabel }}</button>
                    <button type="button" :class="danger ? 'ac-btn-danger' : 'ac-btn-primary'" @click="confirm">
                        {{ confirmLabel }}
                    </button>
                </div>
            </div>
        </div>
    </Teleport>
</template>

<style scoped>
.acm-overlay {
    position: fixed;
    inset: 0;
    z-index: 60;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1rem;
    background: color-mix(in srgb, var(--color-accent) 55%, transparent);
    backdrop-filter: blur(2px);
}
.acm-panel {
    width: 100%;
    max-width: 26rem;
    padding: 1.5rem;
}
</style>
