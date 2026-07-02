<script setup>
import { useForm, Link } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const props = defineProps({ lead: Object, attempts: { type: Array, default: () => [] } })

const attemptStatusLabel = {
    completed:   'Terminée',
    in_progress: 'En cours',
    started:     'Commencée',
    abandoned:   'Abandonnée',
}
const attemptStatusClass = {
    completed:   'ac-badge-success',
    in_progress: 'ac-badge-warning',
    started:     'ac-badge-warning',
    abandoned:   'ac-badge-neutral',
}

const form = useForm({
    status: props.lead.status,
    score: props.lead.score,
})

const save = () => form.put(route('admin.leads.update', props.lead.id))
</script>

<template>
    <AdminLayout>
        <Head :title="lead.email" />

        <Link :href="route('admin.leads.index')" class="text-sm hover:underline" style="color:var(--text-muted)">← Retour aux leads</Link>

        <div class="flex items-start justify-between mt-3 mb-6">
            <div>
                <h1 class="text-2xl font-semibold" style="color:var(--text-primary);font-family:var(--font-display)">{{ lead.email }}</h1>
                <p class="text-sm mt-1" style="color:var(--text-muted)">{{ [lead.first_name, lead.last_name].filter(Boolean).join(' ') ?? 'Sans nom' }}</p>
            </div>
        </div>

        <div class="grid lg:grid-cols-3 gap-6">
            <section class="pt-card p-6 lg:col-span-2 space-y-4">
                <h2 class="font-semibold mb-2" style="color:var(--text-primary);font-family:var(--font-display)">Informations</h2>
                <dl class="grid grid-cols-2 gap-3 text-sm">
                    <div><dt class="text-xs" style="color:var(--text-muted)">Téléphone</dt><dd>{{ lead.phone ?? '—' }}</dd></div>
                    <div><dt class="text-xs" style="color:var(--text-muted)">Source</dt><dd>{{ lead.source ?? '—' }}</dd></div>
                    <div><dt class="text-xs" style="color:var(--text-muted)">Créé le</dt><dd>{{ lead.created_at }}</dd></div>
                    <div><dt class="text-xs" style="color:var(--text-muted)">Dernière activité</dt><dd>{{ lead.last_activity_at ?? '—' }}</dd></div>
                </dl>

                <h3 class="font-semibold mt-6 mb-2" style="color:var(--text-primary);font-family:var(--font-display)">
                    Épreuves
                    <span class="text-xs font-normal" style="color:var(--text-muted)">({{ attempts.length }})</span>
                </h3>
                <p v-if="!lead.user_id" class="text-sm" style="color:var(--text-muted)">
                    Ce lead n'est rattaché à aucun compte — aucune épreuve à afficher.
                </p>
                <p v-else-if="!attempts.length" class="text-sm" style="color:var(--text-muted)">
                    Aucune épreuve commencée pour l'instant.
                </p>
                <div v-else class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr>
                                <th class="ac-th text-left py-2 pr-3">Épreuve</th>
                                <th class="ac-th text-left py-2 pr-3">Statut</th>
                                <th class="ac-th text-left py-2 pr-3">Commencée</th>
                                <th class="ac-th text-left py-2 pr-3">Terminée</th>
                                <th class="ac-th text-left py-2 pr-3">Synthèse IA</th>
                                <th class="ac-th text-left py-2">Résultats</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y" style="border-color:var(--border-light)">
                            <tr v-for="a in attempts" :key="a.id">
                                <td class="py-2 pr-3 font-medium" style="color:var(--text-primary)">{{ a.test_name }}</td>
                                <td class="py-2 pr-3">
                                    <span :class="attemptStatusClass[a.status] ?? 'ac-badge-neutral'" class="whitespace-nowrap">
                                        {{ attemptStatusLabel[a.status] ?? a.status }}
                                    </span>
                                </td>
                                <td class="py-2 pr-3 text-xs whitespace-nowrap" style="color:var(--text-muted)">{{ a.started_at ?? '—' }}</td>
                                <td class="py-2 pr-3 text-xs whitespace-nowrap" style="color:var(--text-muted)">{{ a.completed_at ?? '—' }}</td>
                                <td class="py-2 pr-3 text-xs" style="color:var(--text-muted)">{{ a.has_synthesis ? 'Oui' : '—' }}</td>
                                <td class="py-2 text-xs whitespace-nowrap">
                                    <a v-if="a.results_url" :href="a.results_url" target="_blank" rel="noopener"
                                       class="hover:underline" style="color:var(--color-primary-dark,#7D5510);font-weight:600">
                                        Voir ↗
                                    </a>
                                    <a v-if="a.pdf_url" :href="a.pdf_url" target="_blank" rel="noopener"
                                       class="hover:underline ml-2" style="color:var(--color-primary-dark,#7D5510)">
                                        PDF
                                    </a>
                                    <span v-if="!a.results_url && !a.pdf_url" style="color:var(--text-ghost,#B0A08A)">—</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <h3 class="font-semibold mt-6 mb-2" style="color:var(--text-primary);font-family:var(--font-display)">UTM</h3>
                <pre class="text-xs rounded-lg p-3 overflow-auto" style="background:var(--bg-elevated)">{{ JSON.stringify(lead.utm ?? {}, null, 2) }}</pre>

                <h3 class="font-semibold mt-6 mb-2" style="color:var(--text-primary);font-family:var(--font-display)">Métadonnées</h3>
                <pre class="text-xs rounded-lg p-3 overflow-auto" style="background:var(--bg-elevated)">{{ JSON.stringify(lead.metadata ?? {}, null, 2) }}</pre>
            </section>

            <aside class="pt-card p-6 h-fit space-y-4">
                <h2 class="font-semibold" style="color:var(--text-primary);font-family:var(--font-display)">Qualification</h2>
                <div>
                    <label class="pt-label">Statut</label>
                    <select v-model="form.status" class="pt-input mt-1">
                        <option value="new">Nouveau</option>
                        <option value="contacted">Contacté</option>
                        <option value="qualified">Qualifié</option>
                        <option value="converted">Converti</option>
                        <option value="lost">Perdu</option>
                    </select>
                </div>
                <div>
                    <label class="pt-label">Score (0-100)</label>
                    <input type="number" min="0" max="100" v-model.number="form.score" class="pt-input mt-1">
                </div>
                <button @click="save" :disabled="form.processing" class="ac-btn-primary w-full">Enregistrer</button>
            </aside>
        </div>
    </AdminLayout>
</template>
