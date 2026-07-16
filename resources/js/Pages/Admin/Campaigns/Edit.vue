<script setup>
import { useForm, router } from '@inertiajs/vue3'
import { ref, onBeforeUnmount } from 'vue'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import FlashAlert from '@/Components/Admin/FlashAlert.vue'

const props = defineProps({ campaign: Object })

// L'input datetime-local exige « YYYY-MM-DDTHH:mm » ; Eloquent renvoie une
// date ISO complète — on tronque.
const toLocalInput = (value) => {
    if (!value) return ''
    const d = new Date(value)
    if (isNaN(d)) return ''
    const pad = (n) => String(n).padStart(2, '0')
    return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}T${pad(d.getHours())}:${pad(d.getMinutes())}`
}

const form = useForm({
    name: props.campaign?.name ?? '',
    subject: props.campaign?.subject ?? '',
    preheader: props.campaign?.preheader ?? '',
    body_html: props.campaign?.body_html ?? '<p>Ton message ici.</p>\n<p>{{ NEURO_PROGRESS }}</p>\n<p>{{ NEURO_SOCIAL_PROOF }}</p>',
    body_text: props.campaign?.body_text ?? '',
    audience_filter: props.campaign?.audience_filter ?? {},
    variants: props.campaign?.variants ?? {},
    scheduled_at: toLocalInput(props.campaign?.scheduled_at),
})

// Champs JSON (variantes A/B, filtre audience) : au lieu d'avaler les erreurs
// de parse en silence, on garde le texte saisi et on affiche l'erreur.
const variantsStr = ref(JSON.stringify(form.variants, null, 2))
const audienceStr = ref(JSON.stringify(form.audience_filter, null, 2))
const variantsError = ref(null)
const audienceError = ref(null)

const syncJson = (str, formKey, errorRef) => {
    try {
        form[formKey] = JSON.parse(str.trim() || '{}')
        errorRef.value = null
    } catch (e) {
        errorRef.value = 'JSON invalide — ' + e.message
    }
}

const submit = () => {
    if (variantsError.value || audienceError.value) return
    if (props.campaign?.id) {
        form.put(route('admin.campaigns.update', props.campaign.id))
    } else {
        form.post(route('admin.campaigns.store'))
    }
}

// Garde « modifications non enregistrées » : rechargement + navigation Inertia (GET).
const onBeforeUnload = (e) => {
    if (form.isDirty) { e.preventDefault(); e.returnValue = '' }
}
window.addEventListener('beforeunload', onBeforeUnload)
const removeNavGuard = router.on('before', (event) => {
    if (event.detail.visit.method === 'get' && form.isDirty
        && !window.confirm('Des modifications non enregistrées seront perdues. Quitter quand même ?')) {
        event.preventDefault()
    }
})
onBeforeUnmount(() => {
    window.removeEventListener('beforeunload', onBeforeUnload)
    removeNavGuard()
})
</script>

<template>
    <AdminLayout>
        <Head :title="campaign?.id ? 'Éditer campagne' : 'Nouvelle campagne'" />

        <h1 class="text-2xl font-semibold mb-6" style="color:var(--text-primary);font-family:var(--font-display)">{{ campaign?.id ? 'Éditer campagne' : 'Nouvelle campagne' }}</h1>

        <FlashAlert />

        <form @submit.prevent="submit" class="grid lg:grid-cols-3 gap-6">
            <section class="pt-card p-6 lg:col-span-2 space-y-5">
                <div>
                    <label for="cmp-name" class="pt-label">Nom interne</label>
                    <input id="cmp-name" v-model="form.name" required class="pt-input mt-2">
                    <p v-if="form.errors.name" class="text-xs mt-1" style="color:var(--color-danger)">{{ form.errors.name }}</p>
                </div>
                <div>
                    <label for="cmp-subject" class="pt-label">Sujet</label>
                    <input id="cmp-subject" v-model="form.subject" required class="pt-input mt-2">
                    <p v-if="form.errors.subject" class="text-xs mt-1" style="color:var(--color-danger)">{{ form.errors.subject }}</p>
                </div>
                <div>
                    <label for="cmp-preheader" class="pt-label">Pré-en-tête</label>
                    <input id="cmp-preheader" v-model="form.preheader" class="pt-input mt-2" placeholder="Optionnel — affiché dans la liste mail">
                </div>
                <div>
                    <label for="cmp-body-html" class="pt-label">Corps HTML</label>
                    <textarea id="cmp-body-html" v-model="form.body_html" rows="12" required class="pt-input font-mono text-xs mt-2"></textarea>
                    <p class="text-xs mt-1" style="color:var(--text-muted)">Variables neuromarketing dispo : <code v-pre>{{ NEURO_PROGRESS }}</code>, <code v-pre>{{ NEURO_SOCIAL_PROOF }}</code></p>
                </div>
                <div>
                    <label for="cmp-body-text" class="pt-label">Version texte (fallback)</label>
                    <textarea id="cmp-body-text" v-model="form.body_text" rows="4" class="pt-input mt-2"></textarea>
                </div>
            </section>

            <aside class="pt-card p-6 space-y-4 h-fit">
                <h3 class="font-semibold" style="color:var(--text-primary);font-family:var(--font-display)">Programmation</h3>
                <div>
                    <label for="cmp-scheduled" class="pt-label">Envoi programmé</label>
                    <input id="cmp-scheduled" type="datetime-local" v-model="form.scheduled_at" class="pt-input mt-1">
                    <p v-if="form.errors.scheduled_at" class="text-xs mt-1" style="color:var(--color-danger)">{{ form.errors.scheduled_at }}</p>
                </div>

                <label for="cmp-variants" class="pt-label block mt-6">Variantes A/B (optionnel)</label>
                <textarea
                    id="cmp-variants"
                    v-model="variantsStr"
                    @input="syncJson(variantsStr, 'variants', variantsError)"
                    rows="6" class="pt-input font-mono text-xs"
                    placeholder='{ "A": "Subject 1", "B": "Subject 2" }'></textarea>
                <p v-if="variantsError" class="text-xs mt-1" style="color:var(--color-danger)">{{ variantsError }}</p>

                <label for="cmp-audience" class="pt-label block mt-6">Filtre audience</label>
                <textarea
                    id="cmp-audience"
                    v-model="audienceStr"
                    @input="syncJson(audienceStr, 'audience_filter', audienceError)"
                    rows="5" class="pt-input font-mono text-xs"
                    placeholder='{ "status": "jobseeker", "has_completed_test": true }'></textarea>
                <p v-if="audienceError" class="text-xs mt-1" style="color:var(--color-danger)">{{ audienceError }}</p>

                <button type="submit"
                    :disabled="form.processing || !!variantsError || !!audienceError"
                    :title="(variantsError || audienceError) ? 'Corrige les champs JSON invalides avant d\'enregistrer' : undefined"
                    class="ac-btn-primary w-full mt-4">
                    {{ form.processing ? 'Enregistrement…' : 'Enregistrer' }}
                </button>
            </aside>
        </form>
    </AdminLayout>
</template>
