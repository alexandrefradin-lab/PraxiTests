<script setup>
import { Link } from '@inertiajs/vue3'
import { computed } from 'vue'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import FlashAlert from '@/Components/Admin/FlashAlert.vue'
import { Bar } from 'vue-chartjs'
import {
    Chart as ChartJS,
    Tooltip,
    BarElement,
    CategoryScale,
    LinearScale,
} from 'chart.js'

ChartJS.register(Tooltip, BarElement, CategoryScale, LinearScale)

const props = defineProps({
    stats: Object,
    recent_attempts: Array,
    recent_leads: Array,
    activity: Array,
    alerts: Array,
})

const cssVar = (name, fallback) => (typeof window !== 'undefined'
    ? getComputedStyle(document.documentElement).getPropertyValue(name).trim() || fallback
    : fallback)

const activityChart = computed(() => ({
    labels: props.activity.map((a) => a.label),
    datasets: [{
        label: 'Tests complétés',
        data: props.activity.map((a) => a.value),
        backgroundColor: cssVar('--color-primary', '#A67520'),
        borderRadius: 4,
        maxBarThickness: 18,
    }],
}))

const barOpts = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: { legend: { display: false } },
    scales: {
        y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: cssVar('--border-light', 'rgba(166,117,32,0.18)') } },
        x: { grid: { display: false } },
    },
}

const statusColor = {
    new: 'ac-badge-signal',
    contacted: 'ac-badge-warning',
    qualified: 'ac-badge-success',
    converted: 'ac-badge-signal',
    lost: 'ac-badge-neutral',
}
</script>

<template>
    <AdminLayout>
        <Head title="Tableau de bord" />
        <h1 class="text-2xl font-semibold mb-6" style="font-family:var(--font-display);color:var(--text-primary)">Tableau de bord</h1>

        <FlashAlert />

        <!-- Alertes exploitation : à traiter en priorité -->
        <div v-if="alerts?.length" class="mb-8 space-y-2">
            <Link v-for="(alert, i) in alerts" :key="i" :href="alert.href"
                class="flex items-center gap-3 p-3 rounded-lg text-sm font-medium transition hover:opacity-80"
                style="background:color-mix(in srgb, var(--color-danger) 8%, transparent);border:1px solid color-mix(in srgb, var(--color-danger) 25%, transparent);color:var(--color-danger)">
                <span aria-hidden="true">⚠</span>
                {{ alert.label }}
                <span class="ml-auto text-xs">Traiter →</span>
            </Link>
        </div>

        <!-- KPI cliquables : chaque carte mène à sa liste filtrée quand elle existe -->
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-8">
            <component :is="stats.total_users != null ? Link : 'div'"
                :href="stats.total_users != null ? route('admin.users.index') : undefined"
                class="pt-card p-5" :class="stats.total_users != null && 'pt-kpi-link'">
                <p class="text-xs" style="color:var(--text-muted)">Utilisateurs</p>
                <p class="text-3xl font-semibold mt-1" style="color:var(--text-primary)">{{ stats.total_users ?? '—' }}</p>
            </component>
            <div class="pt-card p-5">
                <p class="text-xs" style="color:var(--text-muted)">Tests complétés</p>
                <p class="text-3xl font-semibold mt-1" style="color:var(--text-primary)">{{ stats.attempts_completed ?? '—' }}</p>
            </div>
            <div class="pt-card p-5">
                <p class="text-xs" style="color:var(--text-muted)">En cours</p>
                <p class="text-3xl font-semibold mt-1" style="color:var(--text-primary)">{{ stats.attempts_inprogress ?? '—' }}</p>
            </div>
            <div class="pt-card p-5">
                <p class="text-xs" style="color:var(--text-muted)">Taux complétion</p>
                <p class="text-3xl font-semibold mt-1" style="color:var(--text-primary)">{{ stats.completion_rate != null ? stats.completion_rate + '%' : '—' }}</p>
            </div>
            <Link :href="route('admin.leads.index', { status: 'new' })" class="pt-card p-5 pt-kpi-link">
                <p class="text-xs" style="color:var(--text-muted)">Nouveaux leads</p>
                <p class="text-3xl font-semibold mt-1" style="color:var(--text-primary)">{{ stats.leads_new }}</p>
            </Link>
            <Link :href="route('admin.leads.index', { status: 'qualified' })" class="pt-card p-5 pt-kpi-link">
                <p class="text-xs" style="color:var(--text-muted)">Qualifiés</p>
                <p class="text-3xl font-semibold mt-1" style="color:var(--text-primary)">{{ stats.leads_qualified }}</p>
            </Link>
        </div>

        <!-- Tendance d'activité (admin) -->
        <section v-if="activity?.length" class="pt-card p-6 mb-8">
            <h2 class="font-semibold mb-4" style="font-family:var(--font-display);color:var(--text-primary)">Activité — 14 derniers jours</h2>
            <div class="h-48">
                <Bar :data="activityChart" :options="barOpts" />
            </div>
        </section>

        <div class="grid lg:grid-cols-2 gap-6">
            <section class="pt-card p-6">
                <h2 class="font-semibold mb-4" style="font-family:var(--font-display);color:var(--text-primary)">Tests récents</h2>
                <div v-if="recent_attempts.length" class="space-y-3">
                    <component :is="a.results_url ? Link : 'div'" v-for="a in recent_attempts" :key="a.id"
                        :href="a.results_url ?? undefined"
                        class="flex items-center justify-between text-sm border-b pb-3 last:border-0"
                        :class="a.results_url && 'transition hover:opacity-75'"
                        style="border-color:var(--border-light)">
                        <div>
                            <p class="font-medium" style="color:var(--text-primary)">{{ a.user?.name ?? 'Anonyme' }}</p>
                            <p class="text-xs" style="color:var(--text-muted)">{{ a.test?.name }}</p>
                        </div>
                        <span class="text-xs whitespace-nowrap" style="color:var(--text-muted)">{{ a.completed_at ?? 'en cours' }}</span>
                    </component>
                </div>
                <p v-else class="text-sm py-8 text-center" style="color:var(--text-muted)">
                    Aucun test passé pour le moment.
                </p>
            </section>

            <section class="pt-card p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="font-semibold" style="font-family:var(--font-display);color:var(--text-primary)">Derniers leads</h2>
                    <Link :href="route('admin.leads.index')" class="ac-link-primary text-xs">Tous les leads →</Link>
                </div>
                <div v-if="recent_leads.length" class="space-y-3">
                    <Link v-for="l in recent_leads" :key="l.id" :href="route('admin.leads.show', l.id)"
                        class="flex items-center justify-between text-sm border-b pb-3 last:border-0 transition hover:opacity-75"
                        style="border-color:var(--border-light)">
                        <div>
                            <p class="font-medium" style="color:var(--text-primary)">{{ [l.first_name, l.last_name].filter(Boolean).join(' ') || l.email }}</p>
                            <p class="text-xs" style="color:var(--text-muted)">{{ l.email }} · {{ l.created_at }}</p>
                        </div>
                        <span :class="statusColor[l.status] ?? 'ac-badge-neutral'">{{ l.status }}</span>
                    </Link>
                </div>
                <p v-else class="text-sm py-8 text-center" style="color:var(--text-muted)">
                    Aucun lead pour le moment. Ils apparaîtront dès la première inscription ou invitation.
                </p>
            </section>
        </div>
    </AdminLayout>
</template>

<style scoped>
.pt-kpi-link {
    display: block;
    transition: transform .12s ease, box-shadow .12s ease;
}
.pt-kpi-link:hover {
    transform: translateY(-1px);
    box-shadow: 0 3px 10px rgba(0, 0, 0, 0.08);
}
</style>
