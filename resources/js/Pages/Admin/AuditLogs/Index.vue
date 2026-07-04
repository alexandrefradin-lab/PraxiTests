<script setup>
import { router } from '@inertiajs/vue3'
import { onUnmounted, ref, watch } from 'vue'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import AdminPagination from '@/Components/Admin/AdminPagination.vue'

const props = defineProps({ logs: Object, actionPrefixes: Array, filters: Object })

const search = ref(props.filters?.search ?? '')
const action = ref(props.filters?.action ?? '')

let timer = null
watch([search, action], () => {
    clearTimeout(timer)
    timer = setTimeout(() => {
        router.get(route('admin.audit-logs.index'), { search: search.value, action: action.value },
            { preserveState: true, preserveScroll: true, replace: true })
    }, 250)
})
onUnmounted(() => clearTimeout(timer))

// Détail metadata replié par ligne
const expanded = ref(new Set())
const toggle = (id) => {
    expanded.value.has(id) ? expanded.value.delete(id) : expanded.value.add(id)
    expanded.value = new Set(expanded.value)
}

// Famille d'action → couleur du badge (destructif en rouge)
const actionBadge = (a) => {
    if (/destroy|suspend|delete/.test(a)) return 'ac-badge-danger'
    if (/restore|created/.test(a)) return 'ac-badge-success'
    if (/export/.test(a)) return 'ac-badge-warning'
    return 'ac-badge-neutral'
}
</script>

<template>
    <AdminLayout>
        <Head title="Journal d'audit" />

        <div class="flex items-end justify-between mb-6">
            <div>
                <h1 class="text-2xl font-semibold" style="color:var(--text-primary);font-family:var(--font-display)">Journal d'audit</h1>
                <p class="text-sm mt-1" style="color:var(--text-muted)">Qui a fait quoi, et quand — lecture seule.</p>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-3 mb-4">
            <label for="log-search" class="sr-only">Rechercher</label>
            <input id="log-search" v-model="search" placeholder="Rechercher utilisateur ou ressource…" class="pt-input">
            <label for="log-action" class="sr-only">Filtrer par type d'action</label>
            <select id="log-action" v-model="action" class="pt-input">
                <option value="">Toutes les actions</option>
                <option v-for="p in actionPrefixes" :key="p" :value="p">{{ p }}</option>
            </select>
        </div>

        <div class="pt-card overflow-hidden">
            <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr>
                        <th class="ac-th text-left px-5 py-3">Date</th>
                        <th class="ac-th text-left px-5 py-3">Action</th>
                        <th class="ac-th text-left px-5 py-3">Par</th>
                        <th class="ac-th text-left px-5 py-3">Ressource</th>
                        <th class="ac-th text-left px-5 py-3">IP</th>
                        <th class="ac-th px-5 py-3"><span class="sr-only">Détail</span></th>
                    </tr>
                </thead>
                <tbody class="divide-y" style="border-color:var(--border-light)">
                    <template v-for="log in logs.data" :key="log.id">
                        <tr class="ac-row-hover">
                            <td class="px-5 py-3 text-xs whitespace-nowrap" style="color:var(--text-muted)">{{ log.created_at }}</td>
                            <td class="px-5 py-3"><span :class="actionBadge(log.action)">{{ log.action }}</span></td>
                            <td class="px-5 py-3" style="color:var(--text-secondary)">{{ log.user }}</td>
                            <td class="px-5 py-3 text-xs" style="color:var(--text-muted)">
                                {{ log.resource_type ?? '—' }}<span v-if="log.resource_id"> #{{ log.resource_id }}</span>
                            </td>
                            <td class="px-5 py-3 text-xs" style="color:var(--text-muted)">{{ log.ip_address ?? '—' }}</td>
                            <td class="px-5 py-3 text-right">
                                <button v-if="log.metadata" @click="toggle(log.id)" class="ac-link-primary text-xs"
                                    :aria-expanded="expanded.has(log.id)">
                                    {{ expanded.has(log.id) ? 'Masquer' : 'Détail' }}
                                </button>
                            </td>
                        </tr>
                        <tr v-if="expanded.has(log.id)">
                            <td colspan="6" class="px-5 pb-3">
                                <pre class="text-xs p-3 rounded overflow-x-auto"
                                    style="background:var(--bg-elevated);color:var(--text-secondary);font-family:var(--font-data)">{{ JSON.stringify(log.metadata, null, 2) }}</pre>
                            </td>
                        </tr>
                    </template>
                    <tr v-if="!logs.data.length">
                        <td colspan="6" class="text-center py-12" style="color:var(--text-muted)">Aucune action enregistrée pour ces filtres.</td>
                    </tr>
                </tbody>
            </table>
            </div>
        </div>

        <AdminPagination :links="logs.links" />
    </AdminLayout>
</template>
