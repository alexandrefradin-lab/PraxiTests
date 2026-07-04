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
const filterSearch = ref(props.filters.search ?? '')

function applyFilters() {
    router.get('/admin/subscriptions', {
        plan:   filterPlan.value   || undefined,
        status: filterStatus.value || undefined,
        search: filterSearch.value || undefined,
    }, { preserveState: true, replace: true })
}

function resetFilters() {
    filterPlan.value   = ''
    filterStatus.value = ''
    filterSearch.value = ''
    router.get('/admin/subscriptions')
}

// cf. audit M-14 — on n'injecte plus le label de pagination via v-html.
// On retire les balises HTML et on décode les entités usuelles (« »),
// puis on rend le résultat en texte simple (pas de surface XSS).
function paginationLabel(label) {
    const text = String(label ?? '').replace(/<[^>]*>/g, '')
    return text
        .replace(/&laquo;/g, '«')
        .replace(/&raquo;/g, '»')
        .replace(/&amp;/g, '&')
        .replace(/&nbsp;/g, ' ')
        .trim()
}

// ---- Formatage ----
const formatMoney = (cents) => {
    if (!cents) return '—'
    return new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'EUR' }).format(cents / 100)
}

const statusLabel = {
    active:    { text: 'Actif',         cls: 'ac-badge-success' },
    trialing:  { text: 'Essai',         cls: 'ac-badge-signal'  },
    grace:     { text: 'Résiliation…',  cls: 'ac-badge-warning' },
    cancelled: { text: 'Résilié',       cls: 'ac-badge-danger'  },
    inactive:  { text: 'Inactif',       cls: 'ac-badge-neutral' },
    none:      { text: 'Aucun',         cls: 'ac-badge-neutral' },
}

const mrrFormatted = computed(() => formatMoney(props.kpis.mrr))

const kpiCards = computed(() => [
    { label: 'MRR',             value: mrrFormatted.value,                color: 'text-[var(--pt-gold)]'  },
    { label: 'Abonnés actifs',  value: props.kpis.active,                 color: 'text-[var(--color-success)]' },
    { label: 'En période essai', value: props.kpis.trialing,              color: 'text-[var(--color-signal)]'  },
    { label: 'Résiliés',        value: props.kpis.cancelled,              color: 'text-[var(--color-danger)]'  },
    { label: 'Total abonnés',   value: props.kpis.total_subscribers,      color: 'text-[var(--color-accent)]'  },
])
</script>

<template>
    <AdminLayout>
        <Head title="Abonnements" />
        <h1 class="text-2xl font-semibold mb-8" style="color:var(--text-primary);font-family:var(--font-display)">Abonnements &amp; Facturation</h1>

        <!-- KPIs -->
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4 mb-8">
            <div
                v-for="card in kpiCards"
                :key="card.label"
                class="pt-card p-5"
            >
                <p class="text-xs mb-1" style="color:var(--text-muted)">{{ card.label }}</p>
                <p class="text-3xl font-semibold" :class="card.color">{{ card.value }}</p>
            </div>
        </div>

        <!-- Filtres -->
        <div class="pt-card p-4 mb-6 flex flex-wrap gap-3 items-end">
            <div>
                <label for="sub-search" class="pt-label mb-1">Recherche</label>
                <input id="sub-search" v-model="filterSearch" @keyup.enter="applyFilters"
                    placeholder="Email ou nom…" class="pt-input text-sm py-1.5">
            </div>
            <div>
                <label for="sub-plan" class="pt-label mb-1">Plan</label>
                <select id="sub-plan" v-model="filterPlan" class="pt-input text-sm py-1.5">
                    <option value="">Tous les plans</option>
                    <option v-for="p in plans" :key="p.key" :value="p.key">{{ p.name }}</option>
                </select>
            </div>
            <div>
                <label for="sub-status" class="pt-label mb-1">Statut</label>
                <select id="sub-status" v-model="filterStatus" class="pt-input text-sm py-1.5">
                    <option value="">Tous</option>
                    <option value="active">Actif</option>
                    <option value="trialing">Essai</option>
                    <option value="cancelled">Résilié</option>
                    <option value="none">Sans abonnement</option>
                </select>
            </div>
            <button @click="applyFilters" class="ac-btn-ghost text-sm py-1.5 px-4">Filtrer</button>
            <button
                v-if="filterPlan || filterStatus || filterSearch"
                @click="resetFilters"
                class="text-sm underline hover:no-underline"
                style="color:var(--text-muted)"
            >Effacer</button>
            <a :href="route('admin.subscriptions.export')" class="ac-btn-ghost text-sm py-1.5 px-4 ml-auto">Exporter CSV</a>
        </div>

        <!-- Tableau -->
        <div class="pt-card overflow-hidden">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left">
                        <th class="ac-th px-4 py-3">Utilisateur</th>
                        <th class="ac-th px-4 py-3">Plan</th>
                        <th class="ac-th px-4 py-3">Période</th>
                        <th class="ac-th px-4 py-3">Statut</th>
                        <th class="ac-th px-4 py-3">MRR</th>
                        <th class="ac-th px-4 py-3">Fin / Essai</th>
                        <th class="ac-th px-4 py-3">Inscrit le</th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="u in users.data"
                        :key="u.id"
                        class="ac-row-hover border-b last:border-0"
                        style="border-color:var(--border-light)"
                    >
                        <td class="px-4 py-3">
                            <p class="font-medium" style="color:var(--text-primary)">{{ u.name }}</p>
                            <p class="text-xs" style="color:var(--text-muted)">{{ u.email }}</p>
                        </td>
                        <td class="px-4 py-3">
                            <span v-if="u.plan_name" class="font-medium">{{ u.plan_name }}</span>
                            <span v-else style="color:var(--text-muted)">—</span>
                        </td>
                        <td class="px-4 py-3 capitalize" style="color:var(--text-muted)">
                            {{ u.period === 'monthly' ? 'Mensuel' : u.period === 'yearly' ? 'Annuel' : '—' }}
                        </td>
                        <td class="px-4 py-3">
                            <span :class="statusLabel[u.status]?.cls ?? 'ac-badge-neutral'">
                                {{ statusLabel[u.status]?.text ?? u.status }}
                            </span>
                        </td>
                        <td class="px-4 py-3 font-mono" style="color:var(--text-secondary)">
                            {{ formatMoney(u.mrr) }}
                        </td>
                        <td class="px-4 py-3 text-xs" style="color:var(--text-muted)">
                            <span v-if="u.trial_ends && u.status === 'trialing'">
                                Essai → {{ u.trial_ends }}
                            </span>
                            <span v-else-if="u.ends_at">
                                {{ u.status === 'grace' ? 'Accès jusqu\'au' : 'Résilié le' }} {{ u.ends_at }}
                            </span>
                            <span v-else>—</span>
                        </td>
                        <td class="px-4 py-3 text-xs" style="color:var(--text-muted)">{{ u.created_at }}</td>
                    </tr>

                    <!-- État vide -->
                    <tr v-if="!users.data.length">
                        <td colspan="7" class="px-4 py-12 text-center text-sm" style="color:var(--text-muted)">
                            Aucun utilisateur trouvé.
                        </td>
                    </tr>
                </tbody>
            </table>

            <!-- Pagination -->
            <div v-if="users.last_page > 1" class="px-4 py-3 border-t flex items-center justify-between text-sm" style="border-color:var(--border-light);color:var(--text-muted)">
                <span>{{ users.total }} résultat{{ users.total > 1 ? 's' : '' }}</span>
                <div class="flex gap-1">
                    <Link
                        v-for="link in users.links"
                        :key="link.label"
                        :href="link.url ?? '#'"
                        v-text="paginationLabel(link.label)"
                        class="px-3 py-1 rounded text-xs border transition-colors"
                        :class="[
                            link.active ? 'bg-[var(--color-accent)] text-[#F0E8D4] border-[var(--color-accent)]' : 'hover:bg-[var(--bg-elevated)]',
                            !link.url   ? 'opacity-40 pointer-events-none' : '',
                        ]"
                        :style="!link.active ? 'border-color:var(--border-light)' : ''"
                    />
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
