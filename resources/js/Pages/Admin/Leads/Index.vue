<script setup>
import { router } from '@inertiajs/vue3'
import { onUnmounted, ref, watch } from 'vue'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import AdminPagination from '@/Components/Admin/AdminPagination.vue'
import FlashAlert from '@/Components/Admin/FlashAlert.vue'
import SortableTh from '@/Components/Admin/SortableTh.vue'

const props = defineProps({ leads: Object, filters: Object })

const search  = ref(props.filters?.search ?? '')
const status  = ref(props.filters?.status ?? '')
const trashed = ref(!!props.filters?.trashed)
const sort    = ref(props.filters?.sort ?? 'created_at')
const dir     = ref(props.filters?.dir ?? 'desc')

const query = () => ({
    search: search.value || undefined,
    status: status.value || undefined,
    trashed: trashed.value ? 1 : undefined,
    sort: sort.value !== 'created_at' ? sort.value : undefined,
    dir: dir.value !== 'desc' ? dir.value : undefined,
})

let timer = null
const reload = () => router.get(route('admin.leads.index'), query(), {
    preserveState: true, preserveScroll: true, replace: true,
})
watch([search, status, trashed], () => {
    clearTimeout(timer)
    timer = setTimeout(reload, 250)
})

// FE-08 — nettoie le timer au démontage du composant
onUnmounted(() => clearTimeout(timer))

// Tri serveur : re-clic sur la même colonne inverse le sens
const sortBy = (field) => {
    if (sort.value === field) {
        dir.value = dir.value === 'asc' ? 'desc' : 'asc'
    } else {
        sort.value = field
        dir.value = field === 'created_at' ? 'desc' : 'asc'
    }
    reload()
}

const statusColor = {
    new: 'ac-badge-signal',
    contacted: 'ac-badge-warning',
    qualified: 'ac-badge-success',
    converted: 'ac-badge-signal',
    lost: 'ac-badge-neutral',
}

const restore = (l) => router.post(route('admin.leads.restore', l.id), {}, { preserveScroll: true })
const exportUrl = () => route('admin.leads.export', query())
</script>

<template>
    <AdminLayout>
        <Head title="Leads" />

        <div class="flex flex-wrap items-end justify-between gap-4 mb-6">
            <h1 class="text-2xl font-semibold" style="color:var(--text-primary);font-family:var(--font-display)">Leads</h1>
            <div class="flex flex-wrap items-center gap-3">
                <a :href="exportUrl()" class="ac-btn-ghost text-xs">Exporter CSV</a>
                <label for="lead-search" class="sr-only">Rechercher</label>
                <input id="lead-search" v-model="search" placeholder="Rechercher email ou nom…" class="pt-input">
                <label for="lead-status" class="sr-only">Filtrer par statut</label>
                <select id="lead-status" v-model="status" class="pt-input">
                    <option value="">Tous les statuts</option>
                    <option value="new">Nouveaux</option>
                    <option value="contacted">Contactés</option>
                    <option value="qualified">Qualifiés</option>
                    <option value="converted">Convertis</option>
                    <option value="lost">Perdus</option>
                </select>
                <label class="flex items-center gap-2 text-xs cursor-pointer" style="color:var(--text-muted)">
                    <input type="checkbox" v-model="trashed" class="ac-checkbox">
                    Corbeille
                </label>
            </div>
        </div>

        <FlashAlert />

        <div class="pt-card overflow-hidden">
            <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr>
                        <SortableTh field="email" :sort="sort" :dir="dir" @sort="sortBy">Email</SortableTh>
                        <SortableTh field="first_name" :sort="sort" :dir="dir" @sort="sortBy">Nom</SortableTh>
                        <th class="ac-th text-left px-5 py-3">Source</th>
                        <SortableTh field="tests_count" :sort="sort" :dir="dir" align="center" @sort="sortBy">Épreuves</SortableTh>
                        <SortableTh field="score" :sort="sort" :dir="dir" @sort="sortBy">Score</SortableTh>
                        <SortableTh field="status" :sort="sort" :dir="dir" @sort="sortBy">Statut</SortableTh>
                        <SortableTh field="created_at" :sort="sort" :dir="dir" @sort="sortBy">Date</SortableTh>
                        <th v-if="trashed" class="ac-th px-5 py-3"><span class="sr-only">Actions</span></th>
                    </tr>
                </thead>
                <tbody class="divide-y" style="border-color:var(--border-light)">
                    <tr v-for="l in leads.data" :key="l.id" class="ac-row-hover" :class="{ 'cursor-pointer': !trashed }"
                        @click="!trashed && router.visit(route('admin.leads.show', l.id))">
                        <td class="px-5 py-3 font-medium" style="color:var(--text-primary)">{{ l.email }}</td>
                        <td class="px-5 py-3" style="color:var(--text-secondary)">{{ [l.first_name, l.last_name].filter(Boolean).join(' ') }}</td>
                        <td class="px-5 py-3 text-xs" style="color:var(--text-muted)">{{ l.source ?? '—' }}</td>
                        <td class="px-5 py-3 text-center">
                            <span v-if="l.tests_count > 0" class="ac-badge">{{ l.tests_count }}</span>
                            <span v-else class="text-xs" style="color:var(--text-ghost,#B0A08A)">0</span>
                        </td>
                        <td class="px-5 py-3">{{ l.score }}/100</td>
                        <td class="px-5 py-3">
                            <span :class="statusColor[l.status] ?? 'ac-badge-neutral'">{{ l.status }}</span>
                        </td>
                        <td class="px-5 py-3 text-xs whitespace-nowrap" style="color:var(--text-muted)">{{ l.created_at }}</td>
                        <td v-if="trashed" class="px-5 py-3 text-right">
                            <button @click.stop="restore(l)" class="ac-link-success text-xs">Restaurer</button>
                        </td>
                    </tr>
                    <tr v-if="!leads.data.length">
                        <td :colspan="trashed ? 8 : 7" class="text-center py-12" style="color:var(--text-muted)">
                            {{ trashed ? 'Corbeille vide.' : 'Aucun lead.' }}
                        </td>
                    </tr>
                </tbody>
            </table>
            </div>
        </div>

        <AdminPagination :links="leads.links" />
    </AdminLayout>
</template>
