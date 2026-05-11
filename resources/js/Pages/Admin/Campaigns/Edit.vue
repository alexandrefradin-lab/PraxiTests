<script setup>
import { useForm } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const props = defineProps({ campaign: Object })

const form = useForm({
    name: props.campaign?.name ?? '',
    subject: props.campaign?.subject ?? '',
    preheader: props.campaign?.preheader ?? '',
    body_html: props.campaign?.body_html ?? '<p>Ton message ici.</p>\n<p>{{ NEURO_PROGRESS }}</p>\n<p>{{ NEURO_SOCIAL_PROOF }}</p>',
    body_text: props.campaign?.body_text ?? '',
    audience_filter: props.campaign?.audience_filter ?? {},
    variants: props.campaign?.variants ?? {},
    scheduled_at: props.campaign?.scheduled_at ?? '',
})

const submit = () => {
    if (props.campaign?.id) {
        form.put(route('admin.campaigns.update', props.campaign.id))
    } else {
        form.post(route('admin.campaigns.store'))
    }
}
</script>

<template>
    <AdminLayout>
        <Head :title="campaign?.id ? 'Éditer campagne' : 'Nouvelle campagne'" />

        <h1 class="text-2xl font-semibold mb-6">{{ campaign?.id ? 'Éditer campagne' : 'Nouvelle campagne' }}</h1>

        <form @submit.prevent="submit" class="grid lg:grid-cols-3 gap-6">
            <section class="pt-card p-6 lg:col-span-2 space-y-5">
                <div>
                    <label class="block text-sm font-medium">Nom interne</label>
                    <input v-model="form.name" required class="pt-input mt-2">
                </div>
                <div>
                    <label class="block text-sm font-medium">Sujet</label>
                    <input v-model="form.subject" required class="pt-input mt-2">
                </div>
                <div>
                    <label class="block text-sm font-medium">Pré-en-tête</label>
                    <input v-model="form.preheader" class="pt-input mt-2" placeholder="Optionnel — affiché dans la liste mail">
                </div>
                <div>
                    <label class="block text-sm font-medium">Corps HTML</label>
                    <textarea v-model="form.body_html" rows="12" required class="pt-input font-mono text-xs mt-2"></textarea>
                    <p class="text-xs text-slate-400 mt-1">Variables neuromarketing dispo : <code v-pre>{{ NEURO_PROGRESS }}</code>, <code v-pre>{{ NEURO_SOCIAL_PROOF }}</code></p>
                </div>
                <div>
                    <label class="block text-sm font-medium">Version texte (fallback)</label>
                    <textarea v-model="form.body_text" rows="4" class="pt-input mt-2"></textarea>
                </div>
            </section>

            <aside class="pt-card p-6 space-y-4 h-fit">
                <h3 class="font-semibold">Programmation</h3>
                <div>
                    <label class="block text-xs font-medium">Envoi programmé</label>
                    <input type="datetime-local" v-model="form.scheduled_at" class="pt-input mt-1">
                </div>

                <h3 class="font-semibold mt-6">Variantes A/B (optionnel)</h3>
                <textarea
                    @input="(e) => { try { form.variants = JSON.parse(e.target.value || '{}') } catch {} }"
                    :value="JSON.stringify(form.variants, null, 2)"
                    rows="6" class="pt-input font-mono text-xs"
                    placeholder='{ "A": "Subject 1", "B": "Subject 2" }'></textarea>

                <h3 class="font-semibold mt-6">Filtre audience</h3>
                <textarea
                    @input="(e) => { try { form.audience_filter = JSON.parse(e.target.value || '{}') } catch {} }"
                    :value="JSON.stringify(form.audience_filter, null, 2)"
                    rows="5" class="pt-input font-mono text-xs"
                    placeholder='{ "status": "jobseeker", "has_completed_test": true }'></textarea>

                <button type="submit" :disabled="form.processing" class="pt-btn-primary w-full mt-4">
                    {{ form.processing ? 'Enregistrement…' : 'Enregistrer' }}
                </button>
            </aside>
        </form>
    </AdminLayout>
</template>
