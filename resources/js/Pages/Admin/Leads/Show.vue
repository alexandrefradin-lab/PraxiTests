<script setup>
import { useForm, Link } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const props = defineProps({ lead: Object })

const form = useForm({
    status: props.lead.status,
    score: props.lead.score,
})

const save = () => form.put(route('admin.leads.update', props.lead.id))
</script>

<template>
    <AdminLayout>
        <Head :title="lead.email" />

        <Link :href="route('admin.leads.index')" class="text-sm text-slate-500 hover:text-slate-900">← Retour aux leads</Link>

        <div class="flex items-start justify-between mt-3 mb-6">
            <div>
                <h1 class="text-2xl font-semibold">{{ lead.email }}</h1>
                <p class="text-sm text-slate-500 mt-1">{{ [lead.first_name, lead.last_name].filter(Boolean).join(' ') ?? 'Sans nom' }}</p>
            </div>
        </div>

        <div class="grid lg:grid-cols-3 gap-6">
            <section class="pt-card p-6 lg:col-span-2 space-y-4">
                <h2 class="font-semibold mb-2">Informations</h2>
                <dl class="grid grid-cols-2 gap-3 text-sm">
                    <div><dt class="text-slate-500 text-xs">Téléphone</dt><dd>{{ lead.phone ?? '—' }}</dd></div>
                    <div><dt class="text-slate-500 text-xs">Source</dt><dd>{{ lead.source ?? '—' }}</dd></div>
                    <div><dt class="text-slate-500 text-xs">Créé le</dt><dd>{{ lead.created_at }}</dd></div>
                    <div><dt class="text-slate-500 text-xs">Dernière activité</dt><dd>{{ lead.last_activity_at ?? '—' }}</dd></div>
                </dl>

                <h3 class="font-semibold mt-6 mb-2">UTM</h3>
                <pre class="text-xs bg-slate-50 rounded-lg p-3 overflow-auto">{{ JSON.stringify(lead.utm ?? {}, null, 2) }}</pre>

                <h3 class="font-semibold mt-6 mb-2">Métadonnées</h3>
                <pre class="text-xs bg-slate-50 rounded-lg p-3 overflow-auto">{{ JSON.stringify(lead.metadata ?? {}, null, 2) }}</pre>
            </section>

            <aside class="pt-card p-6 h-fit space-y-4">
                <h2 class="font-semibold">Qualification</h2>
                <div>
                    <label class="block text-xs font-medium text-slate-600">Statut</label>
                    <select v-model="form.status" class="pt-input mt-1">
                        <option value="new">Nouveau</option>
                        <option value="contacted">Contacté</option>
                        <option value="qualified">Qualifié</option>
                        <option value="converted">Converti</option>
                        <option value="lost">Perdu</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600">Score (0-100)</label>
                    <input type="number" min="0" max="100" v-model.number="form.score" class="pt-input mt-1">
                </div>
                <button @click="save" :disabled="form.processing" class="pt-btn-primary w-full">Enregistrer</button>
            </aside>
        </div>
    </AdminLayout>
</template>
