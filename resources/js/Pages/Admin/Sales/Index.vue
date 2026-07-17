<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import AdminPagination from '@/Components/Admin/AdminPagination.vue'
import { router } from '@inertiajs/vue3'
import { onUnmounted, ref, computed, watch } from 'vue'
import { Bar, Doughnut } from 'vue-chartjs'
import {
    Chart as ChartJS,
    Tooltip,
    Legend,
    BarElement,
    ArcElement,
    CategoryScale,
    LinearScale,
} from 'chart.js'

ChartJS.register(Tooltip, Legend, BarElement, ArcElement, CategoryScale, LinearScale)

const props = defineProps({
    kpis:           Object,
    plan_breakdown: Array,
    trend:          Array,
    particuliers:   Object,
    cabinets:       Object,
    plans:          Array,
    filters:        Object,
})

// ---- Onglet actif : Particuliers | Cabinets ----
const tab = ref('particuliers')

// ---- Filtres live avec debounce (pattern des autres listes admin) ----
const filterSearch = ref(props.filters.search ?? '')
const filterPlan   = ref(props.filters.plan   ?? '')
const filterStatus = ref(props.filters.status ?? '')
let timer = null
watch([filterSearch, filterPlan, filterStatus], () => {
    clearTimeout(timer)
    timer = setTimeout(() => {
        router.get(route('admin.sales'), {
            search: filterSearch.value || undefined,
            plan:   filterPlan.value   || undefined,
            status: filterStatus.value || undefined,
        }, { preserveState: true, preserveScroll: true, replace: true })
    }, 250)
})
onUnmounted(() => clearTimeout(timer))

function resetFilters() {
    filterSearch.value = ''
    filterPlan.value   = ''
    filterStatus.value = ''
}

// ---- Formatage ----
const formatMoney = (cents) => new Intl.NumberFormat('fr-FR', {
    style: 'currency', currency: 'EUR', maximumFractionDigits: cents % 100 === 0 ? 0 : 2,
}).format((cents ?? 0) / 100)

const statusLabel = {
    active:    { text: 'Actif',        cls: 'ac-badge-success' },
    trialing:  { text: 'Essai',        cls: 'ac-badge-signal'  },
    grace:     { text: 'Résiliation…', cls: 'ac-badge-warning' },
    cancelled: { text: 'Résilié',      cls: 'ac-badge-danger'  },
    inactive:  { text: 'Inactif',      cls: 'ac-badge-neutral' },
    none:      { text: 'Aucun',        cls: 'ac-badge-neutral' },
}

const periodLabel = (p) => p === 'monthly' ? 'Mensuel' : p === 'yearly' ? 'Annuel' : '—'

// ---- Delta nouveaux abonnés vs mois précédent ----
const newDelta = computed(() => props.kpis.new_this_month - props.kpis.new_last_month)

// ---- Charts ----
const cssVar = (name, fallback) => (typeof window !== 'undefined'
    ? getComputedStyle(document.documentElement).getPropertyValue(name).trim() || fallback
    : fallback)

const trendChart = computed(() => ({
    labels: props.trend.map((m) => m.label),
    datasets: [
        {
            label: 'Particuliers',
            data: props.trend.map((m) => m.particuliers),
            backgroundColor: cssVar('--color-primary', '#A67520'),
            stack: 'new',
            borderRadius: 4,
            maxBarThickness: 22,
        },
        {
            label: 'Cabinets',
            data: props.trend.map((m) => m.cabinets),
            backgroundColor: '#1B2A4A',
            stack: 'new',
            borderRadius: 4,
            maxBarThickness: 22,
        },
        {
            label: 'Résiliations',
            data: props.trend.map((m) => -m.cancelled),
            backgroundColor: cssVar('--color-danger', '#7B1515'),
            stack: 'new',
            borderRadius: 4,
            maxBarThickness: 22,
        },
    ],
}))

const trendOpts = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 11 } } },
        tooltip: {
            callbacks: {
                // Ligne additionnelle : MRR ajouté sur le mois survolé
                footer: (items) => {
                    const m = props.trend[items[0]?.dataIndex]
                    return m ? `MRR ajouté : ${formatMoney(m.new_mrr)}` : ''
                },
            },
        },
    },
    scales: {
        y: { stacked: true, ticks: { precision: 0 }, grid: { color: cssVar('--border-light', 'rgba(166,117,32,0.18)') } },
        x: { stacked: true, grid: { display: false } },
    },
}

const planChart = computed(() => ({
    labels: props.plan_breakdown.map((p) => p.name),
    datasets: [{
        data: props.plan_breakdown.map((p) => p.count),
        backgroundColor: props.plan_breakdown.map((p) => p.color),
        borderWidth: 0,
    }],
}))

const planOpts = {
    responsive: true,
    maintainAspectRatio: false,
    cutout: '62%',
    plugins: { legend: { display: false } },
}

const hasActivePlans = computed(() => props.plan_breakdown.some((p) => p.count > 0))

// ---- KPI cards ----
const kpiCards = computed(() => [
    { label: 'MRR', value: formatMoney(props.kpis.mrr), sub: `ARR ${formatMoney(props.kpis.arr)}`, color: 'text-[var(--pt-gold)]' },
    { label: 'MRR Particuliers', value: formatMoney(props.kpis.mrr_particuliers), sub: null, color: 'text-[var(--color-primary)]' },
    { label: 'MRR Cabinets', value: formatMoney(props.kpis.mrr_cabinets), sub: `${props.kpis.cabinets_total} cabinet${props.kpis.cabinets_total > 1 ? 's' : ''} créé${props.kpis.cabinets_total > 1 ? 's' : ''}`, color: 'text-[var(--color-accent)]' },
    { label: 'Nouveaux ce mois', value: props.kpis.new_this_month, sub: newDelta.value === 0 ? '= mois précédent' : `${newDelta.value > 0 ? '+' : ''}${newDelta.value} vs mois précédent`, color: newDelta.value >= 0 ? 'text-[var(--color-success)]' : 'text-[var(--color-danger)]' },
])

const statusCards = computed(() => [
    { label: 'Actifs',                value: props.kpis.active,    color: 'text-[var(--color-success)]' },
    { label: 'En essai',              value: props.kpis.trialing,  color: 'text-[var(--color-signal)]'  },
    { label: 'Résiliation en cours',  value: props.kpis.grace,     color: 'text-[var(--color-warning,#B45309)]' },
    { label: 'Résiliés',              value: props.kpis.cancelled, color: 'text-[var(--color-danger)]'  },
])
</script>

<template>
    <AdminLayout>
        <Head title="Console des ventes" />
        <h1 class="text-2xl font-semibold mb-8" style="color:var(--text-primary);font-family:var(--font-display)">Console des ventes</h1>

        <!-- KPIs revenu -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
            <div v-for="card in kpiCards" :key="card.label" class="pt-card p-5">
                <p class="text-xs mb-1" style="color:var(--text-muted)">{{ card.label }}</p>
                <p class="text-3xl font-semibold" :class="card.color">{{ card.value }}</p>
                <p v-if="card.sub" class="text-xs mt-1" style="color:var(--text-muted)">{{ card.sub }}</p>
            </div>
        </div>

        <!-- KPIs statuts -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
            <div v-for="card in statusCards" :key="card.label" class="pt-card p-4">
                <p class="text-xs mb-1" style="color:var(--text-muted)">{{ card.label }}</p>
                <p class="text-2xl font-semibold" :class="card.color">{{ card.value }}</p>
            </div>
        </div>

        <!-- Graphiques -->
        <div class="grid lg:grid-cols-3 gap-6 mb-8">
            <section class="pt-card p-6 lg:col-span-2">
                <h2 class="font-semibold mb-4" style="font-family:var(--font-display);color:var(--text-primary)">
                    Nouveaux abonnements — 12 derniers mois
                </h2>
                <div class="h-64">
                    <Bar :data="trendChart" :options="trendOpts" />
                </div>
            </section>

            <section class="pt-card p-6">
                <h2 class="font-semibold mb-4" style="font-family:var(--font-display);color:var(--text-primary)">
                    Répartition par plan
                </h2>
                <div v-if="hasActivePlans" class="h-40 mb-4">
                    <Doughnut :data="planChart" :options="planOpts" />
                </div>
                <p v-else class="text-sm py-8 text-center" style="color:var(--text-muted)">
                    Aucun abonnement actif pour le moment.
                </p>
                <ul class="space-y-2">
                    <li v-for="p in plan_breakdown" :key="p.key" class="flex items-center gap-2 text-sm">
                        <span class="inline-block w-3 h-3 rounded-full shrink-0" :style="{ background: p.color }" />
                        <span style="color:var(--text-primary)">{{ p.name }}</span>
                        <span v-if="!p.available" class="ac-badge-neutral text-[10px]">Bientôt</span>
                        <span class="ml-auto font-mono text-xs" style="color:var(--text-secondary)">
                            {{ p.count }} · {{ formatMoney(p.mrr) }}
                        </span>
                    </li>
                </ul>
            </section>
        </div>

        <!-- Onglets Particuliers / Cabinets -->
        <div class="flex flex-wrap items-end gap-3 mb-4">
            <div class="flex rounded-lg overflow-hidden border" style="border-color:var(--border-light)">
                <button
                    @click="tab = 'particuliers'"
                    class="px-4 py-2 text-sm font-medium transition"
                    :style="tab === 'particuliers'
                        ? 'background:var(--color-primary);color:#fff'
                        : 'background:transparent;color:var(--text-muted)'"
                >Particuliers ({{ particuliers.total }})</button>
                <button
                    @click="tab = 'cabinets'"
                    class="px-4 py-2 text-sm font-medium transition"
                    :style="tab === 'cabinets'
                        ? 'background:var(--color-primary);color:#fff'
                        : 'background:transparent;color:var(--text-muted)'"
                >Cabinets ({{ cabinets.total }})</button>
            </div>

            <div class="ml-auto flex flex-wrap items-end gap-3">
                <div>
                    <label for="sales-search" class="pt-label mb-1">Recherche</label>
                    <input id="sales-search" v-model="filterSearch"
                        placeholder="Nom, email ou cabinet…" class="pt-input text-sm py-1.5">
                </div>
                <div>
                    <label for="sales-plan" class="pt-label mb-1">Plan</label>
                    <select id="sales-plan" v-model="filterPlan" class="pt-input text-sm py-1.5">
                        <option value="">Tous les plans</option>
                        <option v-for="p in plans" :key="p.key" :value="p.key">{{ p.name }}</option>
                    </select>
                </div>
                <div>
                    <label for="sales-status" class="pt-label mb-1">Statut</label>
                    <select id="sales-status" v-model="filterStatus" class="pt-input text-sm py-1.5">
                        <option value="">Tous</option>
                        <option value="active">Actif</option>
                        <option value="trialing">Essai</option>
                        <option value="cancelled">Résilié</option>
                    </select>
                </div>
                <button
                    v-if="filterSearch || filterPlan || filterStatus"
                    @click="resetFilters"
                    class="text-sm underline hover:no-underline pb-2"
                    style="color:var(--text-muted)"
                >Effacer</button>
                <a :href="route('admin.sales.export', { segment: tab })" class="ac-btn-ghost text-sm py-1.5 px-4">
                    Exporter CSV
                </a>
            </div>
        </div>

        <!-- Tableau Particuliers -->
        <div v-show="tab === 'particuliers'">
            <div class="pt-card overflow-hidden">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left">
                            <th class="ac-th px-4 py-3">Client</th>
                            <th class="ac-th px-4 py-3">Plan</th>
                            <th class="ac-th px-4 py-3">Période</th>
                            <th class="ac-th px-4 py-3">Statut</th>
                            <th class="ac-th px-4 py-3">MRR</th>
                            <th class="ac-th px-4 py-3">Fin / Essai</th>
                            <th class="ac-th px-4 py-3">Inscrit le</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="u in particuliers.data" :key="u.id"
                            class="ac-row-hover border-b last:border-0" style="border-color:var(--border-light)">
                            <td class="px-4 py-3">
                                <p class="font-medium" style="color:var(--text-primary)">{{ u.name }}</p>
                                <p class="text-xs" style="color:var(--text-muted)">{{ u.email }}</p>
                            </td>
                            <td class="px-4 py-3">
                                <span v-if="u.plan_name" class="font-medium">{{ u.plan_name }}</span>
                                <span v-else style="color:var(--text-muted)">—</span>
                            </td>
                            <td class="px-4 py-3" style="color:var(--text-muted)">{{ periodLabel(u.period) }}</td>
                            <td class="px-4 py-3">
                                <span :class="statusLabel[u.status]?.cls ?? 'ac-badge-neutral'">
                                    {{ statusLabel[u.status]?.text ?? u.status }}
                                </span>
                            </td>
                            <td class="px-4 py-3 font-mono" style="color:var(--text-secondary)">{{ formatMoney(u.mrr) }}</td>
                            <td class="px-4 py-3 text-xs" style="color:var(--text-muted)">
                                <span v-if="u.trial_ends && u.status === 'trialing'">Essai → {{ u.trial_ends }}</span>
                                <span v-else-if="u.ends_at">{{ u.status === 'grace' ? 'Accès jusqu\'au' : 'Résilié le' }} {{ u.ends_at }}</span>
                                <span v-else>—</span>
                            </td>
                            <td class="px-4 py-3 text-xs" style="color:var(--text-muted)">{{ u.created_at }}</td>
                        </tr>
                        <tr v-if="!particuliers.data.length">
                            <td colspan="7" class="px-4 py-12 text-center text-sm" style="color:var(--text-muted)">
                                Aucun abonné particulier trouvé.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <AdminPagination :links="particuliers.links" />
        </div>

        <!-- Tableau Cabinets -->
        <div v-show="tab === 'cabinets'">
            <div class="pt-card overflow-hidden">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left">
                            <th class="ac-th px-4 py-3">Cabinet</th>
                            <th class="ac-th px-4 py-3">Propriétaire</th>
                            <th class="ac-th px-4 py-3">Membres</th>
                            <th class="ac-th px-4 py-3">Plan facturé</th>
                            <th class="ac-th px-4 py-3">Statut</th>
                            <th class="ac-th px-4 py-3">MRR</th>
                            <th class="ac-th px-4 py-3">Créé le</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="c in cabinets.data" :key="c.id"
                            class="ac-row-hover border-b last:border-0" style="border-color:var(--border-light)">
                            <td class="px-4 py-3">
                                <p class="font-medium" style="color:var(--text-primary)">{{ c.company_name }}</p>
                                <p class="text-xs capitalize" style="color:var(--text-muted)">Compte : {{ c.account_plan }}</p>
                            </td>
                            <td class="px-4 py-3">
                                <p style="color:var(--text-primary)">{{ c.owner_name ?? '—' }}</p>
                                <p class="text-xs" style="color:var(--text-muted)">{{ c.owner_email }}</p>
                            </td>
                            <td class="px-4 py-3 font-mono" style="color:var(--text-secondary)">
                                {{ c.members_count }} / {{ c.seats_limit }}
                            </td>
                            <td class="px-4 py-3">
                                <span v-if="c.plan_name" class="font-medium">{{ c.plan_name }}</span>
                                <span v-else style="color:var(--text-muted)">—</span>
                                <span v-if="c.period" class="text-xs ml-1" style="color:var(--text-muted)">({{ periodLabel(c.period) }})</span>
                            </td>
                            <td class="px-4 py-3">
                                <span :class="statusLabel[c.status]?.cls ?? 'ac-badge-neutral'">
                                    {{ statusLabel[c.status]?.text ?? c.status }}
                                </span>
                            </td>
                            <td class="px-4 py-3 font-mono" style="color:var(--text-secondary)">{{ formatMoney(c.mrr) }}</td>
                            <td class="px-4 py-3 text-xs" style="color:var(--text-muted)">{{ c.created_at }}</td>
                        </tr>
                        <tr v-if="!cabinets.data.length">
                            <td colspan="7" class="px-4 py-12 text-center text-sm" style="color:var(--text-muted)">
                                Aucun cabinet créé pour le moment. Les plans Cabinet et Centre sont « Bientôt disponibles » —
                                cette vue se remplira dès l'ouverture du multi-comptes.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <AdminPagination :links="cabinets.links" />
        </div>
    </AdminLayout>
</template>
