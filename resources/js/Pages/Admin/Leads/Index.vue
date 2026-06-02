<script setup>
import { Link, router } from '@inertiajs/vue3'
import { onUnmounted, ref, watch } from 'vue'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const props = defineProps({ leads: Object, filters: Object })

const search = ref(props.filters?.search ?? '')
const status = ref(props.filters?.status ?? '')

let timer = null
watch([search, status], () => {
    clearTimeout(timer)
    timer = setTimeout(() => {
        router.get(route('admin.leads.index'), { search: search.value, status: status.value }, {
            preserveState: true, preserveScroll: true, replace: true,
        })
    }, 250)
})

// FE-08 — nettoie le timer au démontage du composant
onUnmounted(() => clearTimeout(timer))

const statusColor = {
    new: 'bg-indigo-50 text-indigo-700',
    contacted: 'bg-amber-50 text-amber-700',
    qualified: 'bg-emerald-50 text-emerald-700',
    converted: 'bg-violet-50 text-violet-700',
    lost: 'bg-slate-100 text-slate-600',
}
</script>

<template>
    <AdminLayout>
        <Head title="Leads" />

        <div class="flex items-end justify-between mb-6">
            <h1 class="text-2xl font-semibold">Leads</h1>
            <div class="flex gap-3">
                <input v-model="search" placeholder="Rechercher email ou nom…" class="pt-input">
                <select v-model="status" class="pt-input">
                    <option value="">Tous les statuts</option>
                    <option value="new">Nouveaux</option>
                    <option value="contacted">Contactés</option>
                    <option value="qualified">Qualifiés</option>
                    <option value="converted">Convertis</option>
                    <option value="lost">Perdus</option>
                </select>
            </div>
        </div>

        <div class="pt-card overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-xs uppercase text-slate-500">
                    <tr>
                        <th class="text-left px-5 py-3">Email</th>
                        <th class="text-left px-5 py-3">Nom</th>
                        <th class="text-left px-5 py-3">Source</th>
                        <th class="text-left px-5 py-3">Score</th>
                        <th class="text-left px-5 py-3">Statut</th>
                        <th class="text-left px-5 py-3">Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <tr v-for="l in leads.data" :key="l.id" class="hover:bg-slate-50 cursor-pointer" @click="router.visit(route('admin.leads.show', l.id))">
                        <td class="px-5 py-3 font-medium">{{ l.email }}</td>
                        <td class="px-5 py-3 text-slate-600">{{ [l.first_name, l.last_name].filter(Boolean).join(' ') }}</td>
                        <td class="px-5 py-3 text-slate-500 text-xs">{{ l.source ?? '—' }}</td>
                        <td class="px-5 py-3">{{ l.score }}/100</td>
                        <td class="px-5 py-3">
                            <span :class="statusColor[l.status] ?? 'bg-slate-100'" class="text-xs px-2 py-1 rounded-full">{{ l.status }}</span>
                        </td>
                        <td class="px-5 py-3 text-xs text-slate-500">{{ l.created_at }}</td>
                    </tr>
                    <tr v-if="!leads.data.length"><td colspan="6" class="text-center text-slate-500 py-12">Aucun lead.</td></tr>
                </tbody>
            </table>
        </div>

        <div v-if="leads.links" class="flex items-center justify-center gap-1 mt-6">
            <!-- SEC-08 : libellés rendus en texte (plus de v-html) -->
            <component :is="link.url ? Link : 'span'" v-for="link in leads.links" :key="link.label" :href="link.url ?? ''"
                class="px-3 py-1 text-xs rounded"
                :class="[link.active ? 'bg-indigo-600 text-white' : 'text-slate-600 hover:bg-slate-100', !link.url && 'opacity-40']">{{ link.label }}</component>
        </div>
    </AdminLayout>
</template>
