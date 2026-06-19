<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Link, router } from '@inertiajs/vue3'
import { ref, computed } from 'vue'

const props = defineProps({
    users:   Object,
    plans:   Array,
    filters: Object,
    kpis:    Object,
})

// ---- Filtres ----
const filterPlan   = ref(props.filters.plan   ?? '')
const filterStatus = ref(props.filters.status ?? '')

function applyFilters() {
    router.get('/admin/subscriptions', {
        plan:   filterPlan.value   || undefined,
        status: filterStatus.value || undefined,
    }, { preserveState: true, replace: true })
}

function resetFilters() {
    filterPlan.value   = ''
    filterStatus.value = ''
    router.get('/admin/subscriptions')
}

// ---- Formatage ----
const formatMoney = (cents) => {
    if (!cents) return '—'
    return new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'EUR' }).format(cents / 100)
}

const statusLabel = {
    active:    { text: 'Actif',         cls: 'bg-emerald-50 text-emerald-700' },
    trialing:  { text: 'Essai',         cls: 'bg-sky-50    text-sky-700'     },
    grace:     { text: 'Résiliation…',  cls: 'bg-amber-50  text-amber-700'   },
    cancelled: { text: 'Résilié',       cls: 'bg-red-50    text-red-700'     },
    inactive:  { text: 'Inactif',       cls: 'bg-slate-50  text-slate-500'   },
    none:      { text: 'Aucun',         cls: 'bg-slate-100 text-slate-400'   },
}

const mrrFormatted = computed(() => formatMoney(props.kpis.mrr))

const kpiCards = computed(() => [
    { label: 'MRR',             value: mrrFormatted.value,                color: 'text-[var(--pt-gold)]'  },
    { label: 'Abonnés actifs',  value: props.kpis.active,                 color: 'text-emerald-600' },
    { label: 'En période essai', value: props.kpis.trialing,              color: 'text-sky-600'     },
    { label: 'Résiliés',        value: props.kpis.cancelled,              color: 'text-red-500'     },
    { label: 'Total abonnés',   value: props.kpis.total_subscribers,      color: 'text-[var(--pt-navy)]'  },
])
</script>

<template>
    <AdminLayout>
        <Head title="Abonnements" />
        <h1 class="text-2xl font-semibold mb-8">Abonnements &amp; Facturation</h1>

        <!-- KPIs -->
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4 mb-8">
            <div
                v-for="card in kpiCards"
                :key="card.label"
                class="pt-card p-5"
            >
                <p class="text-xs text-slate-500 mb-1">{{ card.label }}</p>
                <p class="text-3xl font-semibold" :class="card.color">{{ card.value }}</p>
            </div>
        </div>

        <!-- Filtres -->
        <div class="pt-card p-4 mb-6 flex flex-wrap gap-3 items-end">
            <div>
                <label class="block text-xs text-slate-500 mb-1">Plan</label>
                <select v-model="filterPlan" class="pt-input text-sm py-1.5">
                    <option value="">Tous les plans</option>
                    <option v-for="p in plans" :key="p.key" :value="p.key">{{ p.name }}</option>
                </select>
            </div>
            <div>
                <label class="block text-xs text-slate-500 mb-1">Statut</label>
                <select v-model="filterStatus" class="pt-input text-sm py-1.5">
                    <option value="">Tous</option>
                    <option value="active">Actif</option>
                    <option value="trialing">Essai</option>
                    <option value="cancelled">Résilié</option>
                    <option value="none">Sans abonnement</option>
                </select>
            </div>
            <button @click="applyFilters" class="pt-btn text-sm py-1.5 px-4">Filtrer</button>
            <button
                v-if="filterPlan || filterStatus"
                @click="resetFilters"
                class="text-sm text-slate-500 hover:text-slate-700 underline"
            >Effacer</button>
        </div>

        <!-- Tableau -->
        <div class="pt-card overflow-hidden">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-100 text-left">
                        <th class="px-4 py-3 text-xs text-slate-500 font-medium">Utilisateur</th>
                        <th class="px-4 py-3 text-xs text-slate-500 font-medium">Plan</th>
                        <th class="px-4 py-3 text-xs text-slate-500 font-medium">Période</th>
                        <th class="px-4 py-3 text-xs text-slate-500 font-medium">Statut</th>
                        <th class="px-4 py-3 text-xs text-slate-500 font-medium">MRR</th>
                        <th class="px-4 py-3 text-xs text-slate-500 font-medium">Fin / Essai</th>
                        <th class="px-4 py-3 text-xs text-slate-500 font-medium">Inscrit le</th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="u in users.data"
                        :key="u.id"
                        class="border-b border-slate-50 last:border-0 hover:bg-slate-50 transition-colors"
                    >
                        <td class="px-4 py-3">
                            <p class="font-medium text-slate-800">{{ u.name }}</p>
                            <p class="text-xs text-slate-400">{{ u.email }}</p>
                        </td>
                        <td class="px-4 py-3">
                            <span v-if="u.plan_name" class="font-medium">{{ u.plan_name }}</span>
                            <span v-else class="text-slate-400">—</span>
                        </td>
                        <td class="px-4 py-3 text-slate-500 capitalize">
                            {{ u.period === 'monthly' ? 'Mensuel' : u.period === 'yearly' ? 'Annuel' : '—' }}
                        </td>
                        <td class="px-4 py-3">
                            <span
                                class="px-2 py-0.5 rounded-full text-xs font-medium"
                                :class="statusLabel[u.status]?.cls ?? 'bg-slate-100 text-slate-500'"
                            >
                                {{ statusLabel[u.status]?.text ?? u.status }}
                            </span>
                        </td>
                        <td class="px-4 py-3 font-mono text-slate-700">
                            {{ formatMoney(u.mrr) }}
                        </td>
                        <td class="px-4 py-3 text-slate-500 text-xs">
                            <span v-if="u.trial_ends && u.status === 'trialing'">
                                Essai → {{ u.trial_ends }}
                            </span>
                            <span v-else-if="u.ends_at">
                                {{ u.status === 'grace' ? 'Accès jusqu\'au' : 'Résilié le' }} {{ u.ends_at }}
                            </span>
                            <span v-else>—</span>
                        </td>
                        <td class="px-4 py-3 text-slate-400 text-xs">{{ u.created_at }}</td>
                    </tr>

                    <!-- État vide -->
                    <tr v-if="!users.data.length">
                        <td colspan="7" class="px-4 py-12 text-center text-slate-400 text-sm">
                            Aucun utilisateur trouvé.
                        </td>
                    </tr>
                </tbody>
            </table>

            <!-- Pagination -->
            <div v-if="users.last_page > 1" class="px-4 py-3 border-t border-slate-100 flex items-center justify-between text-sm text-slate-500">
                <span>{{ users.total }} résultat{{ users.total > 1 ? 's' : '' }}</span>
                <div class="flex gap-1">
                    <Link
                        v-for="link in users.links"
                        :key="link.label"
                        :href="link.url ?? '#'"
                        v-html="link.label"
                        class="px-3 py-1 rounded text-xs border border-slate-200 transition-colors"
                        :class="[
                            link.active ? 'bg-[var(--pt-navy)] text-white border-[var(--pt-navy)]' : 'hover:bg-slate-50',
                            !link.url   ? 'opacity-40 pointer-events-none' : '',
                        ]"
                    />
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
