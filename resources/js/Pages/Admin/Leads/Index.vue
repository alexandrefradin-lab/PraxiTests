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
    new: 'ac-badge-signal',
    contacted: 'ac-badge-warning',
    qualified: 'ac-badge-success',
    converted: 'ac-badge-signal',
    lost: 'ac-badge-neutral',
}
</script>

<template>
    <AdminLayout>
        <Head title="Leads" />

        <div class="flex items-end justify-between mb-6">
            <h1 class="text-2xl font-semibold" style="color:var(--text-primary);font-family:var(--font-display)">Leads</h1>
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
                <thead>
                    <tr>
                        <th class="ac-th text-left px-5 py-3">Email</th>
                        <th class="ac-th text-left px-5 py-3">Nom</th>
                        <th class="ac-th text-left px-5 py-3">Source</th>
                        <th class="ac-th text-left px-5 py-3">Épreuves</th>
                        <th class="ac-th text-left px-5 py-3">Score</th>
                        <th class="ac-th text-left px-5 py-3">Statut</th>
                        <th class="ac-th text-left px-5 py-3">Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y" style="border-color:var(--border-light)">
                    <tr v-for="l in leads.data" :key="l.id" class="ac-row-hover cursor-pointer" @click="router.visit(route('admin.leads.show', l.id))">
                        <td class="px-5 py-3 font-medium" style="color:var(--text-primary)">{{ l.email }}</td>
                        <td class="px-5 py-3" style="color:var(--text-secondary)">{{ [l.first_name, l.last_name].filter(Boolean).join(' ') }}</td>
                        <td class="px-5 py-3 text-xs" style="color:var(--text-muted)">{{ l.source ?? '—' }}</td>
                        <td class="px-5 py-3 text-center" style="color:var(--text-secondary)">{{ l.tests_count ?? 0 }}</td>
                        <td class="px-5 py-3">{{ l.score }}/100</td>
                        <td class="px-5 py-3">
                            <span :class="statusColor[l.status] ?? 'ac-badge-neutral'">{{ l.status }}</span>
                        </td>
                        <td class="px-5 py-3 text-xs" style="color:var(--text-muted)">{{ l.created_at }}</td>
                    </tr>
                    <tr v-if="!leads.data.length"><td colspan="7" class="text-center py-12" style="color:var(--text-muted)">Aucun lead.</td></tr>
                </tbody>
            </table>
        </div>

        <div v-if="leads.links" class="flex items-center justify-center gap-1 mt-6">
            <!-- SEC-08 : libellés rendus en texte (plus de v-html) -->
            <component :is="link.url ? Link : 'span'" v-for="link in leads.links" :key="link.label" :href="link.url ?? ''"
                class="px-3 py-1 text-xs rounded"
                :class="[link.active ? 'bg-[var(--color-accent)] text-[#F0E8D4]' : 'hover:bg-[var(--bg-elevated)]', !link.url && 'opacity-40']"
                :style="!link.active ? 'color:var(--text-secondary)' : ''">{{ link.label }}</component>
        </div>
    </AdminLayout>
</template>
