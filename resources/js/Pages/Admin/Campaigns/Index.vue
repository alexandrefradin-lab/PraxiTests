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
    draft: 'ac-badge-neutral',
    scheduled: 'ac-badge-warning',
    sending: 'ac-badge-signal',
    sent: 'ac-badge-success',
    paused: 'ac-badge-danger',
}
</script>

<template>
    <AdminLayout>
        <Head title="Campagnes" />

        <div class="flex items-end justify-between mb-6">
            <h1 class="text-2xl font-semibold" style="color:var(--text-primary);font-family:var(--font-display)">Campagnes email</h1>
            <Link :href="route('admin.campaigns.create')" class="ac-btn-primary">+ Nouvelle campagne</Link>
        </div>

        <div class="pt-card overflow-hidden">
            <table class="w-full text-sm">
                <thead>
                    <tr>
                        <th class="ac-th text-left px-5 py-3">Nom</th>
                        <th class="ac-th text-left px-5 py-3">Sujet</th>
                        <th class="ac-th text-left px-5 py-3">Statut</th>
                        <th class="ac-th text-left px-5 py-3">Envoi prévu</th>
                        <th class="ac-th px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y" style="border-color:var(--border-light)">
                    <tr v-for="c in campaigns" :key="c.id" class="ac-row-hover">
                        <td class="px-5 py-3 font-medium" style="color:var(--text-primary)">{{ c.name }}</td>
                        <td class="px-5 py-3" style="color:var(--text-secondary)">{{ c.subject }}</td>
                        <td class="px-5 py-3">
                            <span :class="statusColor[c.status] ?? 'ac-badge-neutral'">{{ c.status }}</span>
                        </td>
                        <td class="px-5 py-3 text-xs" style="color:var(--text-muted)">{{ c.scheduled_at ?? '—' }}</td>
                        <td class="px-5 py-3 text-right space-x-3">
                            <Link :href="route('admin.campaigns.edit', c.id)" class="ac-link-primary text-xs">Éditer</Link>
                            <button v-if="c.status === 'draft'" @click="send(c.id)" class="ac-link-success text-xs">Envoyer</button>
                        </td>
                    </tr>
                    <tr v-if="!campaigns.length">
                        <td colspan="5" class="text-center py-12" style="color:var(--text-muted)">Aucune campagne. Crée la première.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </AdminLayout>
</template>
