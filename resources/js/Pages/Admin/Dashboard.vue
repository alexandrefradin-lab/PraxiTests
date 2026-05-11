<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'

defineProps({
    stats: Object,
    recent_attempts: Array,
    recent_leads: Array,
})
</script>

<template>
    <AdminLayout>
        <Head title="Tableau de bord" />
        <h1 class="text-2xl font-semibold mb-8">Tableau de bord</h1>

        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-10">
            <div class="pt-card p-5">
                <p class="text-xs text-slate-500">Utilisateurs</p>
                <p class="text-3xl font-semibold mt-1">{{ stats.total_users }}</p>
            </div>
            <div class="pt-card p-5">
                <p class="text-xs text-slate-500">Tests complétés</p>
                <p class="text-3xl font-semibold mt-1">{{ stats.attempts_completed }}</p>
            </div>
            <div class="pt-card p-5">
                <p class="text-xs text-slate-500">En cours</p>
                <p class="text-3xl font-semibold mt-1">{{ stats.attempts_inprogress }}</p>
            </div>
            <div class="pt-card p-5">
                <p class="text-xs text-slate-500">Taux complétion</p>
                <p class="text-3xl font-semibold mt-1">{{ stats.completion_rate }}%</p>
            </div>
            <div class="pt-card p-5">
                <p class="text-xs text-slate-500">Nouveaux leads</p>
                <p class="text-3xl font-semibold mt-1">{{ stats.leads_new }}</p>
            </div>
            <div class="pt-card p-5">
                <p class="text-xs text-slate-500">Qualifiés</p>
                <p class="text-3xl font-semibold mt-1">{{ stats.leads_qualified }}</p>
            </div>
        </div>

        <div class="grid lg:grid-cols-2 gap-6">
            <section class="pt-card p-6">
                <h2 class="font-semibold mb-4">Tests récents</h2>
                <div class="space-y-3">
                    <div v-for="a in recent_attempts" :key="a.id" class="flex items-center justify-between text-sm border-b border-slate-100 pb-3 last:border-0">
                        <div>
                            <p class="font-medium">{{ a.user?.name ?? 'Anonyme' }}</p>
                            <p class="text-slate-500 text-xs">{{ a.test?.name }}</p>
                        </div>
                        <span class="text-xs text-slate-400">{{ a.completed_at }}</span>
                    </div>
                </div>
            </section>

            <section class="pt-card p-6">
                <h2 class="font-semibold mb-4">Derniers leads</h2>
                <div class="space-y-3">
                    <div v-for="l in recent_leads" :key="l.id" class="flex items-center justify-between text-sm border-b border-slate-100 pb-3 last:border-0">
                        <div>
                            <p class="font-medium">{{ l.first_name }} {{ l.last_name }}</p>
                            <p class="text-slate-500 text-xs">{{ l.email }}</p>
                        </div>
                        <span class="px-2 py-0.5 rounded-full text-xs bg-indigo-50 text-indigo-700">{{ l.status }}</span>
                    </div>
                </div>
            </section>
        </div>
    </AdminLayout>
</template>
