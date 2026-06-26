<script setup>
import { Link } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

defineProps({ tests: Array })
</script>

<template>
    <AdminLayout>
        <Head title="Tests" />

        <div class="flex items-end justify-between mb-6">
            <div>
                <h1 class="text-2xl font-semibold" style="font-family:var(--font-display);color:var(--text-primary)">Tests</h1>
                <p class="text-sm mt-1" style="color:var(--text-muted)">Tous les tests de ta plateforme — natifs et issus de plugins.</p>
            </div>
            <Link :href="route('admin.tests.create')" class="pt-btn-primary">+ Nouveau test</Link>
        </div>

        <div class="pt-card overflow-hidden">
            <table class="w-full text-sm">
                <thead>
                    <tr>
                        <th class="ac-th text-left px-5 py-3">Nom</th>
                        <th class="ac-th text-left px-5 py-3">Type</th>
                        <th class="ac-th text-left px-5 py-3">Plugin</th>
                        <th class="ac-th text-left px-5 py-3">Durée</th>
                        <th class="ac-th text-left px-5 py-3">Statut</th>
                        <th class="ac-th px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y" style="border-color:var(--border-light)">
                    <tr v-for="t in tests" :key="t.id" class="pt-row-hover">
                        <td class="px-5 py-3 font-medium" style="color:var(--text-primary)">{{ t.name }}</td>
                        <td class="px-5 py-3" style="color:var(--text-secondary)">{{ t.type }}</td>
                        <td class="px-5 py-3" style="color:var(--text-secondary)">{{ t.plugin?.name ?? '— natif —' }}</td>
                        <td class="px-5 py-3" style="color:var(--text-secondary)">{{ t.estimated_minutes }} min</td>
                        <td class="px-5 py-3">
                            <span :class="t.published ? 'ac-badge-success' : 'ac-badge-neutral'">
                                {{ t.published ? 'Publié' : 'Brouillon' }}
                            </span>
                        </td>
                        <td class="px-5 py-3 text-right">
                            <Link :href="route('admin.tests.edit', t.id)" class="ac-link-primary text-xs">Éditer</Link>
                        </td>
                    </tr>
                    <tr v-if="!tests.length">
                        <td colspan="6" class="text-center py-12" style="color:var(--text-muted)">Aucun test pour le moment.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </AdminLayout>
</template>

<style scoped>
.pt-row-hover { transition: background-color .15s ease; }
.pt-row-hover:hover { background-color: var(--bg-elevated); }
</style>
