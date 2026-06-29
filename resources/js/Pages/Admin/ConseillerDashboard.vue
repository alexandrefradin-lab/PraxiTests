<script setup>
import { computed } from 'vue'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Bar, Doughnut } from 'vue-chartjs'
import {
    Chart as ChartJS,
    Title,
    Tooltip,
    Legend,
    BarElement,
    ArcElement,
    CategoryScale,
    LinearScale,
} from 'chart.js'

ChartJS.register(Title, Tooltip, Legend, BarElement, ArcElement, CategoryScale, LinearScale)

const props = defineProps({
    stats: Object,
    candidates: Array,
    aiInsights: Array,
    campaigns: Array,
    funnel: Array,
    activity: Array,
})

const kpis = computed(() => [
    { label: 'Candidats invités', value: props.stats.invited, tone: 'navy' },
    { label: 'Tests terminés', value: props.stats.completed, tone: 'green' },
    { label: 'En cours', value: props.stats.in_progress, tone: 'amber' },
    { label: 'En attente', value: props.stats.waiting, tone: 'slate' },
    { label: 'Taux de complétion', value: props.stats.completion_rate + '%', tone: 'navy' },
    { label: 'Synthèses IA', value: props.stats.ai_syntheses, tone: 'violet' },
])

const funnelChart = computed(() => ({
    labels: props.funnel.map((f) => f.label),
    datasets: [
        {
            data: props.funnel.map((f) => f.value),
            backgroundColor: ['#A67520', '#94a3b8', '#f59e0b', '#16a34a'],
            borderWidth: 0,
        },
    ],
}))

const activityChart = computed(() => ({
    labels: props.activity.map((a) => a.label),
    datasets: [
        {
            label: 'Tests complétés',
            data: props.activity.map((a) => a.value),
            backgroundColor: '#A67520',
            borderRadius: 4,
            maxBarThickness: 18,
        },
    ],
}))

const doughnutOpts = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: { legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 11 } } } },
}
const barOpts = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: { legend: { display: false } },
    scales: {
        y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: '#f1f5f9' } },
        x: { grid: { display: false } },
    },
}

const statusLabels = {
    pending: 'En attente',
    sent: 'Envoyé',
    opened: 'Ouvert',
    started: 'En cours',
    completed: 'Terminé',
    expired: 'Expiré',
}
const statusClasses = {
    pending: 'ac-badge-neutral',
    sent: 'ac-badge-signal',
    opened: 'ac-badge-signal',
    started: 'ac-badge-warning',
    completed: 'ac-badge-success',
    expired: 'ac-badge-danger',
}
const campaignStatus = {
    draft: 'ac-badge-neutral',
    scheduled: 'ac-badge-signal',
    sending: 'ac-badge-warning',
    sent: 'ac-badge-success',
    paused: 'ac-badge-danger',
}
const openRate = (c) => (c.delivered > 0 ? Math.round((c.opened / c.delivered) * 100) : 0)
</script>

<template>
    <AdminLayout>
        <Head title="Tableau de bord conseiller" />

        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-2xl font-semibold" style="font-family:var(--font-display);color:var(--text-primary)">Tableau de bord conseiller</h1>
                <p class="text-sm mt-1" style="color:var(--text-muted)">Suivez vos candidats, leurs synthèses IA et vos campagnes.</p>
            </div>
            <Link :href="route('admin.invitations.create')" class="ac-btn-primary text-sm">
                + Inviter un candidat
            </Link>
        </div>

        <!-- Flash success -->
        <div v-if="$page.props.flash?.success" class="mb-6 p-4 rounded-lg text-sm" style="background:#ecfdf5;color:#065f46;border:1px solid #6ee7b7">
            {{ $page.props.flash.success }}
        </div>

        <!-- KPIs -->
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-8">
            <div v-for="k in kpis" :key="k.label" class="pt-card p-5">
                <p class="text-xs" style="color:var(--text-muted)">{{ k.label }}</p>
                <p class="text-3xl font-semibold mt-1" style="color:var(--text-primary)">{{ k.value }}</p>
            </div>
        </div>

        <!-- Graphiques -->
        <div class="grid lg:grid-cols-3 gap-6 mb-8">
            <section class="pt-card p-6 lg:col-span-1">
                <h2 class="font-semibold mb-4" style="font-family:var(--font-display);color:var(--text-primary)">Entonnoir des candidats</h2>
                <div class="h-56">
                    <Doughnut :data="funnelChart" :options="doughnutOpts" />
                </div>
            </section>
            <section class="pt-card p-6 lg:col-span-2">
                <h2 class="font-semibold mb-4" style="font-family:var(--font-display);color:var(--text-primary)">Activité — 14 derniers jours</h2>
                <div class="h-56">
                    <Bar :data="activityChart" :options="barOpts" />
                </div>
            </section>
        </div>

        <div class="grid lg:grid-cols-3 gap-6">
            <!-- Candidats -->
            <section class="pt-card p-6 lg:col-span-2">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="font-semibold" style="font-family:var(--font-display);color:var(--text-primary)">Candidats</h2>
                    <span class="text-xs" style="color:var(--text-muted)">{{ candidates.length }} affichés</span>
                </div>

                <div v-if="candidates.length" class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-left border-b" style="border-color:var(--border-light)">
                                <th class="ac-th">Candidat</th>
                                <th class="ac-th">Test</th>
                                <th class="ac-th">Statut</th>
                                <th class="ac-th">Avancement</th>
                                <th class="ac-th text-right">IA / Métiers</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="c in candidates" :key="c.id" class="border-b last:border-0" style="border-color:var(--border-light)">
                                <td class="py-3">
                                    <p class="font-medium" style="color:var(--text-primary)">{{ c.name }}</p>
                                    <p class="text-xs" style="color:var(--text-muted)">{{ c.email }}</p>
                                </td>
                                <td class="py-3" style="color:var(--text-secondary)">{{ c.test ?? '—' }}</td>
                                <td class="py-3">
                                    <span :class="statusClasses[c.status] ?? 'ac-badge-neutral'">
                                        {{ statusLabels[c.status] ?? c.status }}
                                    </span>
                                </td>
                                <td class="py-3 w-32">
                                    <div class="flex items-center gap-2">
                                        <div class="flex-1 h-1.5 rounded-full overflow-hidden" style="background:var(--bg-elevated)">
                                            <div class="h-full rounded-full" :style="{ width: c.progress + '%', background: 'var(--pt-navy)' }"></div>
                                        </div>
                                        <span class="text-xs" style="color:var(--text-muted)">{{ c.progress }}%</span>
                                    </div>
                                </td>
                                <td class="py-3 text-right">
                                    <Link
                                        v-if="c.attempt_id"
                                        :href="`/results/${c.attempt_id}`"
                                        class="text-xs font-medium"
                                        style="color: var(--pt-navy)"
                                    >
                                        <span v-if="c.has_ai">Voir ({{ c.jobs_count }} métiers)</span>
                                        <span v-else style="color:var(--text-muted)">En attente IA</span>
                                    </Link>
                                    <span v-else class="text-xs" style="color:var(--text-muted)">—</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div v-else class="py-10 text-center">
                    <p class="text-sm mb-3" style="color:var(--text-muted)">Aucun candidat invité pour le moment.</p>
                    <Link :href="route('admin.invitations.create')" class="ac-btn-primary text-sm">+ Inviter le premier candidat</Link>
                </div>
            </section>

            <!-- Synthèses IA & métiers -->
            <section class="pt-card p-6">
                <h2 class="font-semibold mb-4" style="font-family:var(--font-display);color:var(--text-primary)">Synthèses IA &amp; idées de métiers</h2>
                <div v-if="aiInsights.length" class="space-y-4">
                    <div v-for="a in aiInsights" :key="a.attempt_id" class="border-b pb-4 last:border-0 last:pb-0" style="border-color:var(--border-light)">
                        <div class="flex items-center justify-between">
                            <p class="font-medium text-sm" style="color:var(--text-primary)">{{ a.candidate }}</p>
                            <span class="text-xs" style="color:var(--text-muted)">{{ a.jobs_count }} métiers</span>
                        </div>
                        <p class="text-xs mt-1 leading-relaxed" style="color:var(--text-secondary)">{{ a.excerpt }}</p>
                        <div v-if="a.top_jobs.length" class="flex flex-wrap gap-1.5 mt-2">
                            <span v-for="(j, i) in a.top_jobs" :key="i" class="text-xs px-2 py-0.5 rounded-full" style="background:var(--bg-elevated);color:var(--color-primary)">
                                {{ j }}
                            </span>
                        </div>
                        <Link :href="`/results/${a.attempt_id}`" class="text-xs font-medium mt-2 inline-block" style="color: var(--pt-navy)">
                            Ouvrir la synthèse →
                        </Link>
                    </div>
                </div>
                <p v-else class="text-sm py-8 text-center" style="color:var(--text-muted)">Aucune synthèse générée pour l'instant.</p>
            </section>
        </div>

        <!-- Campagnes -->
        <section class="pt-card p-6 mt-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="font-semibold" style="font-family:var(--font-display);color:var(--text-primary)">Campagnes d'invitation</h2>
                <Link href="/admin/campaigns" class="text-xs font-medium" style="color: var(--pt-navy)">Toutes les campagnes →</Link>
            </div>
            <div v-if="campaigns.length" class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left border-b" style="border-color:var(--border-light)">
                            <th class="ac-th">Campagne</th>
                            <th class="ac-th">Statut</th>
                            <th class="ac-th">Envoyée le</th>
                            <th class="ac-th text-right">Délivrés</th>
                            <th class="ac-th text-right">Ouverts</th>
                            <th class="ac-th text-right">Taux d'ouverture</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="c in campaigns" :key="c.id" class="border-b last:border-0" style="border-color:var(--border-light)">
                            <td class="py-3 font-medium" style="color:var(--text-primary)">{{ c.name }}</td>
                            <td class="py-3">
                                <span :class="campaignStatus[c.status] ?? 'ac-badge-neutral'">
                                    {{ c.status }}
                                </span>
                            </td>
                            <td class="py-3" style="color:var(--text-muted)">{{ c.sent_at ?? '—' }}</td>
                            <td class="py-3 text-right" style="color:var(--text-secondary)">{{ c.delivered }}</td>
                            <td class="py-3 text-right" style="color:var(--text-secondary)">{{ c.opened }}</td>
                            <td class="py-3 text-right font-medium" style="color:var(--text-primary)">{{ openRate(c) }}%</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <p v-else class="text-sm py-8 text-center" style="color:var(--text-muted)">Aucune campagne créée.</p>
        </section>
    </AdminLayout>
</template>
