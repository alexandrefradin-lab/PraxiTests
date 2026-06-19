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
            backgroundColor: ['#1e293b', '#94a3b8', '#f59e0b', '#16a34a'],
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
            backgroundColor: '#1e293b',
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
    pending: 'bg-slate-100 text-slate-600',
    sent: 'bg-sky-100 text-sky-700',
    opened: 'bg-indigo-100 text-indigo-700',
    started: 'bg-amber-100 text-amber-700',
    completed: 'bg-green-100 text-green-700',
    expired: 'bg-rose-100 text-rose-700',
}
const campaignStatus = {
    draft: 'bg-slate-100 text-slate-600',
    scheduled: 'bg-sky-100 text-sky-700',
    sending: 'bg-amber-100 text-amber-700',
    sent: 'bg-green-100 text-green-700',
    paused: 'bg-rose-100 text-rose-700',
}
const openRate = (c) => (c.delivered > 0 ? Math.round((c.opened / c.delivered) * 100) : 0)
</script>

<template>
    <AdminLayout>
        <Head title="Tableau de bord conseiller" />

        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-2xl font-semibold">Tableau de bord conseiller</h1>
                <p class="text-sm text-slate-500 mt-1">Suivez vos candidats, leurs synthèses IA et vos campagnes.</p>
            </div>
            <Link href="/admin/campaigns/create" class="text-sm font-medium px-4 py-2 rounded-lg text-white" style="background: var(--pt-navy)">
                + Inviter des candidats
            </Link>
        </div>

        <!-- KPIs -->
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-8">
            <div v-for="k in kpis" :key="k.label" class="pt-card p-5">
                <p class="text-xs text-slate-500">{{ k.label }}</p>
                <p class="text-3xl font-semibold mt-1">{{ k.value }}</p>
            </div>
        </div>

        <!-- Graphiques -->
        <div class="grid lg:grid-cols-3 gap-6 mb-8">
            <section class="pt-card p-6 lg:col-span-1">
                <h2 class="font-semibold mb-4">Entonnoir des candidats</h2>
                <div class="h-56">
                    <Doughnut :data="funnelChart" :options="doughnutOpts" />
                </div>
            </section>
            <section class="pt-card p-6 lg:col-span-2">
                <h2 class="font-semibold mb-4">Activité — 14 derniers jours</h2>
                <div class="h-56">
                    <Bar :data="activityChart" :options="barOpts" />
                </div>
            </section>
        </div>

        <div class="grid lg:grid-cols-3 gap-6">
            <!-- Candidats -->
            <section class="pt-card p-6 lg:col-span-2">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="font-semibold">Candidats</h2>
                    <span class="text-xs text-slate-400">{{ candidates.length }} affichés</span>
                </div>

                <div v-if="candidates.length" class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-left text-xs text-slate-400 border-b border-slate-100">
                                <th class="pb-2 font-medium">Candidat</th>
                                <th class="pb-2 font-medium">Test</th>
                                <th class="pb-2 font-medium">Statut</th>
                                <th class="pb-2 font-medium">Avancement</th>
                                <th class="pb-2 font-medium text-right">IA / Métiers</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="c in candidates" :key="c.id" class="border-b border-slate-50 last:border-0">
                                <td class="py-3">
                                    <p class="font-medium">{{ c.name }}</p>
                                    <p class="text-xs text-slate-400">{{ c.email }}</p>
                                </td>
                                <td class="py-3 text-slate-600">{{ c.test ?? '—' }}</td>
                                <td class="py-3">
                                    <span class="text-xs px-2 py-0.5 rounded-full" :class="statusClasses[c.status] ?? 'bg-slate-100 text-slate-600'">
                                        {{ statusLabels[c.status] ?? c.status }}
                                    </span>
                                </td>
                                <td class="py-3 w-32">
                                    <div class="flex items-center gap-2">
                                        <div class="flex-1 h-1.5 rounded-full bg-slate-100 overflow-hidden">
                                            <div class="h-full rounded-full" :style="{ width: c.progress + '%', background: 'var(--pt-navy)' }"></div>
                                        </div>
                                        <span class="text-xs text-slate-400">{{ c.progress }}%</span>
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
                                        <span v-else class="text-slate-400">En attente IA</span>
                                    </Link>
                                    <span v-else class="text-xs text-slate-300">—</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <p v-else class="text-sm text-slate-400 py-8 text-center">Aucun candidat invité pour le moment.</p>
            </section>

            <!-- Synthèses IA & métiers -->
            <section class="pt-card p-6">
                <h2 class="font-semibold mb-4">Synthèses IA &amp; idées de métiers</h2>
                <div v-if="aiInsights.length" class="space-y-4">
                    <div v-for="a in aiInsights" :key="a.attempt_id" class="border-b border-slate-50 pb-4 last:border-0 last:pb-0">
                        <div class="flex items-center justify-between">
                            <p class="font-medium text-sm">{{ a.candidate }}</p>
                            <span class="text-xs text-slate-400">{{ a.jobs_count }} métiers</span>
                        </div>
                        <p class="text-xs text-slate-500 mt-1 leading-relaxed">{{ a.excerpt }}</p>
                        <div v-if="a.top_jobs.length" class="flex flex-wrap gap-1.5 mt-2">
                            <span v-for="(j, i) in a.top_jobs" :key="i" class="text-xs px-2 py-0.5 rounded-full bg-violet-50 text-violet-700">
                                {{ j }}
                            </span>
                        </div>
                        <Link :href="`/results/${a.attempt_id}`" class="text-xs font-medium mt-2 inline-block" style="color: var(--pt-navy)">
                            Ouvrir la synthèse →
                        </Link>
                    </div>
                </div>
                <p v-else class="text-sm text-slate-400 py-8 text-center">Aucune synthèse générée pour l'instant.</p>
            </section>
        </div>

        <!-- Campagnes -->
        <section class="pt-card p-6 mt-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="font-semibold">Campagnes d'invitation</h2>
                <Link href="/admin/campaigns" class="text-xs font-medium" style="color: var(--pt-navy)">Toutes les campagnes →</Link>
            </div>
            <div v-if="campaigns.length" class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-xs text-slate-400 border-b border-slate-100">
                            <th class="pb-2 font-medium">Campagne</th>
                            <th class="pb-2 font-medium">Statut</th>
                            <th class="pb-2 font-medium">Envoyée le</th>
                            <th class="pb-2 font-medium text-right">Délivrés</th>
                            <th class="pb-2 font-medium text-right">Ouverts</th>
                            <th class="pb-2 font-medium text-right">Taux d'ouverture</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="c in campaigns" :key="c.id" class="border-b border-slate-50 last:border-0">
                            <td class="py-3 font-medium">{{ c.name }}</td>
                            <td class="py-3">
                                <span class="text-xs px-2 py-0.5 rounded-full" :class="campaignStatus[c.status] ?? 'bg-slate-100 text-slate-600'">
                                    {{ c.status }}
                                </span>
                            </td>
                            <td class="py-3 text-slate-500">{{ c.sent_at ?? '—' }}</td>
                            <td class="py-3 text-right">{{ c.delivered }}</td>
                            <td class="py-3 text-right">{{ c.opened }}</td>
                            <td class="py-3 text-right font-medium">{{ openRate(c) }}%</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <p v-else class="text-sm text-slate-400 py-8 text-center">Aucune campagne créée.</p>
        </section>
    </AdminLayout>
</template>
