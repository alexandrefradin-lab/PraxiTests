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
        <h1 class="text-2xl font-semibold mb-8" style="font-family:var(--font-display);color:var(--text-primary)">Tableau de bord</h1>

        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-10">
            <div class="pt-card p-5">
                <p class="text-xs" style="color:var(--text-muted)">Utilisateurs</p>
                <p class="text-3xl font-semibold mt-1" style="color:var(--text-primary)">{{ stats.total_users }}</p>
            </div>
            <div class="pt-card p-5">
                <p class="text-xs" style="color:var(--text-muted)">Tests complétés</p>
                <p class="text-3xl font-semibold mt-1" style="color:var(--text-primary)">{{ stats.attempts_completed }}</p>
            </div>
            <div class="pt-card p-5">
                <p class="text-xs" style="color:var(--text-muted)">En cours</p>
                <p class="text-3xl font-semibold mt-1" style="color:var(--text-primary)">{{ stats.attempts_inprogress }}</p>
            </div>
            <div class="pt-card p-5">
                <p class="text-xs" style="color:var(--text-muted)">Taux complétion</p>
                <p class="text-3xl font-semibold mt-1" style="color:var(--text-primary)">{{ stats.completion_rate }}%</p>
            </div>
            <div class="pt-card p-5">
                <p class="text-xs" style="color:var(--text-muted)">Nouveaux leads</p>
                <p class="text-3xl font-semibold mt-1" style="color:var(--text-primary)">{{ stats.leads_new }}</p>
            </div>
            <div class="pt-card p-5">
                <p class="text-xs" style="color:var(--text-muted)">Qualifiés</p>
                <p class="text-3xl font-semibold mt-1" style="color:var(--text-primary)">{{ stats.leads_qualified }}</p>
            </div>
        </div>

        <div class="grid lg:grid-cols-2 gap-6">
            <section class="pt-card p-6">
                <h2 class="font-semibold mb-4" style="font-family:var(--font-display);color:var(--text-primary)">Tests récents</h2>
                <div class="space-y-3">
                    <div v-for="a in recent_attempts" :key="a.id" class="flex items-center justify-between text-sm border-b pb-3 last:border-0" style="border-color:var(--border-light)">
                        <div>
                            <p class="font-medium" style="color:var(--text-primary)">{{ a.user?.name ?? 'Anonyme' }}</p>
                            <p class="text-xs" style="color:var(--text-muted)">{{ a.test?.name }}</p>
                        </div>
                        <span class="text-xs" style="color:var(--text-muted)">{{ a.completed_at }}</span>
                    </div>
                </div>
            </section>

            <section class="pt-card p-6">
                <h2 class="font-semibold mb-4" style="font-family:var(--font-display);color:var(--text-primary)">Derniers leads</h2>
                <div class="space-y-3">
                    <div v-for="l in recent_leads" :key="l.id" class="flex items-center justify-between text-sm border-b pb-3 last:border-0" style="border-color:var(--border-light)">
                        <div>
                            <p class="font-medium" style="color:var(--text-primary)">{{ l.first_name }} {{ l.last_name }}</p>
                            <p class="text-xs" style="color:var(--text-muted)">{{ l.email }}</p>
                        </div>
                        <span class="ac-badge-signal">{{ l.status }}</span>
                    </div>
                </div>
            </section>
        </div>
    </AdminLayout>
</template>
