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
                <h1 class="text-2xl font-semibold">Tests</h1>
                <p class="text-sm text-slate-500 mt-1">Tous les tests de ta plateforme — natifs et issus de plugins.</p>
            </div>
            <Link :href="route('admin.tests.create')" class="pt-btn-primary">+ Nouveau test</Link>
        </div>

        <div class="pt-card overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-xs uppercase text-slate-500">
                    <tr>
                        <th class="text-left px-5 py-3">Nom</th>
                        <th class="text-left px-5 py-3">Type</th>
                        <th class="text-left px-5 py-3">Plugin</th>
                        <th class="text-left px-5 py-3">Durée</th>
                        <th class="text-left px-5 py-3">Statut</th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <tr v-for="t in tests" :key="t.id" class="hover:bg-slate-50">
                        <td class="px-5 py-3 font-medium">{{ t.name }}</td>
                        <td class="px-5 py-3 text-slate-600">{{ t.type }}</td>
                        <td class="px-5 py-3 text-slate-600">{{ t.plugin?.name ?? '— natif —' }}</td>
                        <td class="px-5 py-3 text-slate-600">{{ t.estimated_minutes }} min</td>
                        <td class="px-5 py-3">
                            <span :class="t.published ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-600'" class="text-xs px-2 py-1 rounded-full">
                                {{ t.published ? 'Publié' : 'Brouillon' }}
                            </span>
                        </td>
                        <td class="px-5 py-3 text-right">
                            <Link :href="route('admin.tests.edit', t.id)" class="text-indigo-600 hover:underline text-xs">Éditer</Link>
                        </td>
                    </tr>
                    <tr v-if="!tests.length">
                        <td colspan="6" class="text-center text-slate-500 py-12">Aucun test pour le moment.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </AdminLayout>
</template>
