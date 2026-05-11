<script setup>
import { Link, router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

defineProps({ campaigns: Array })

const send = (id) => {
    if (confirm('Envoyer cette campagne maintenant ?')) {
        router.post(route('admin.campaigns.send', id))
    }
}

const statusColor = {
    draft: 'bg-slate-100 text-slate-600',
    scheduled: 'bg-amber-50 text-amber-700',
    sending: 'bg-indigo-50 text-indigo-700',
    sent: 'bg-emerald-50 text-emerald-700',
    paused: 'bg-rose-50 text-rose-700',
}
</script>

<template>
    <AdminLayout>
        <Head title="Campagnes" />

        <div class="flex items-end justify-between mb-6">
            <h1 class="text-2xl font-semibold">Campagnes email</h1>
            <Link :href="route('admin.campaigns.create')" class="pt-btn-primary">+ Nouvelle campagne</Link>
        </div>

        <div class="pt-card overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-xs uppercase text-slate-500">
                    <tr>
                        <th class="text-left px-5 py-3">Nom</th>
                        <th class="text-left px-5 py-3">Sujet</th>
                        <th class="text-left px-5 py-3">Statut</th>
                        <th class="text-left px-5 py-3">Envoi prévu</th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <tr v-for="c in campaigns" :key="c.id" class="hover:bg-slate-50">
                        <td class="px-5 py-3 font-medium">{{ c.name }}</td>
                        <td class="px-5 py-3 text-slate-600">{{ c.subject }}</td>
                        <td class="px-5 py-3">
                            <span :class="statusColor[c.status] ?? 'bg-slate-100'" class="text-xs px-2 py-1 rounded-full">{{ c.status }}</span>
                        </td>
                        <td class="px-5 py-3 text-slate-500 text-xs">{{ c.scheduled_at ?? '—' }}</td>
                        <td class="px-5 py-3 text-right space-x-3">
                            <Link :href="route('admin.campaigns.edit', c.id)" class="text-indigo-600 hover:underline text-xs">Éditer</Link>
                            <button v-if="c.status === 'draft'" @click="send(c.id)" class="text-emerald-600 hover:underline text-xs">Envoyer</button>
                        </td>
                    </tr>
                    <tr v-if="!campaigns.length">
                        <td colspan="5" class="text-center text-slate-500 py-12">Aucune campagne. Crée la première.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </AdminLayout>
</template>
