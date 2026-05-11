<script setup>
import { useForm } from '@inertiajs/vue3'
import CandidateLayout from '@/Layouts/CandidateLayout.vue'

defineProps({
    profile: Object,
    statuses: Object,
    cv_max_size_kb: Number,
    cv_allowed_mimes: Array,
})

const form = useForm({
    status: '',
    status_since: '',
    current_role: '',
    industry: '',
    cv: null,
    consent_data: false,
    consent_marketing: false,
})

const submit = () => form.post(route('onboarding.store'), { forceFormData: true })
</script>

<template>
    <CandidateLayout>
        <Head title="Avant de commencer" />

        <div class="max-w-2xl mx-auto">
            <h1 class="text-3xl font-semibold tracking-tight">Avant de commencer</h1>
            <p class="mt-2 text-slate-600">Quelques infos sur toi pour personnaliser tes résultats. Ça prend 2 minutes.</p>

            <form @submit.prevent="submit" class="pt-card mt-8 p-8 space-y-6">
                <div>
                    <label class="block text-sm font-medium text-slate-700">Ton statut actuel</label>
                    <select v-model="form.status" class="pt-input mt-2" required>
                        <option value="" disabled>— Choisir —</option>
                        <option v-for="(label, key) in statuses" :key="key" :value="key">{{ label }}</option>
                    </select>
                    <p v-if="form.errors.status" class="text-xs text-rose-600 mt-1">{{ form.errors.status }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700">Depuis quand ?</label>
                    <input type="date" v-model="form.status_since" class="pt-input mt-2" required>
                    <p v-if="form.errors.status_since" class="text-xs text-rose-600 mt-1">{{ form.errors.status_since }}</p>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Poste actuel (optionnel)</label>
                        <input type="text" v-model="form.current_role" class="pt-input mt-2" placeholder="Ex : Chef de projet">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Secteur (optionnel)</label>
                        <input type="text" v-model="form.industry" class="pt-input mt-2" placeholder="Ex : Industrie">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700">CV</label>
                    <input type="file" :accept="cv_allowed_mimes.map(m => '.' + m).join(',')" @change="form.cv = $event.target.files[0]" class="block w-full text-sm mt-2 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100" required>
                    <p class="text-xs text-slate-500 mt-1">{{ cv_allowed_mimes.join(', ').toUpperCase() }} — max {{ Math.round(cv_max_size_kb / 1024) }} Mo</p>
                    <p v-if="form.errors.cv" class="text-xs text-rose-600 mt-1">{{ form.errors.cv }}</p>
                </div>

                <div class="space-y-3 border-t border-slate-100 pt-6">
                    <label class="flex items-start gap-3 text-sm">
                        <input type="checkbox" v-model="form.consent_data" class="mt-1 rounded border-slate-300 text-indigo-600">
                        <span>J'accepte que mes données soient analysées par l'IA pour produire ma synthèse et mes recommandations métiers. <span class="text-rose-500">*</span></span>
                    </label>
                    <label class="flex items-start gap-3 text-sm">
                        <input type="checkbox" v-model="form.consent_marketing" class="mt-1 rounded border-slate-300 text-indigo-600">
                        <span>Je veux recevoir des conseils personnalisés par email (optionnel).</span>
                    </label>
                </div>

                <button type="submit" :disabled="form.processing" class="pt-btn-primary w-full">
                    <span v-if="form.processing">Envoi…</span>
                    <span v-else>Continuer</span>
                </button>
            </form>
        </div>
    </CandidateLayout>
</template>
