<script setup>
import { Link, router } from '@inertiajs/vue3'
import { onUnmounted, ref, watch } from 'vue'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import AdminPagination from '@/Components/Admin/AdminPagination.vue'
import ConfirmModal from '@/Components/Admin/ConfirmModal.vue'
import FlashAlert from '@/Components/Admin/FlashAlert.vue'
import SortableTh from '@/Components/Admin/SortableTh.vue'

const props = defineProps({ campaigns: Object, filters: Object })

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
const reload = () => router.get(route('admin.campaigns.index'), query(), {
    preserveState: true, preserveScroll: true, replace: true,
})
watch([search, status, trashed], () => {
    clearTimeout(timer)
    timer = setTimeout(reload, 250)
})
onUnmounted(() => clearTimeout(timer))

// Tri serveur : re-clic sur la même colonne inverse le sens
const sortBy = (field) => {
    if (sort.value === field) {
        dir.value = dir.value === 'asc' ? 'desc' : 'asc'
    } else {
        sort.value = field
        dir.value = field === 'created_at' || field === 'sent_at' ? 'desc' : 'asc'
    }
    reload()
}

const confirmingSend   = ref(null)
const confirmingDelete = ref(null)

const send = () => {
    if (confirmingSend.value) {
        router.post(route('admin.campaigns.send', confirmingSend.value.id), {}, { preserveScroll: true })
    }
}
const destroy = () => {
    if (confirmingDelete.value) {
        router.delete(route('admin.campaigns.destroy', confirmingDelete.value.id), { preserveScroll: true })
    }
}
const restore = (c) => router.post(route('admin.campaigns.restore', c.id), {}, { preserveScroll: true })

const statusColor = {
    draft: 'ac-badge-neutral',
    scheduled: 'ac-badge-warning',
    sending: 'ac-badge-signal',
    sent: 'ac-badge-success',
    partial: 'ac-badge-warning',
    failed: 'ac-badge-danger',
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

        <FlashAlert />

        <div class="flex flex-wrap items-center gap-3 mb-4">
            <label for="cmp-search" class="sr-only">Rechercher</label>
            <input id="cmp-search" v-model="search" placeholder="Rechercher nom ou sujet…" class="pt-input">
            <label for="cmp-status" class="sr-only">Filtrer par statut</label>
            <select id="cmp-status" v-model="status" class="pt-input">
                <option value="">Tous les statuts</option>
                <option value="draft">Brouillons</option>
                <option value="scheduled">Programmées</option>
                <option value="sending">En cours d'envoi</option>
                <option value="sent">Envoyées</option>
                <option value="partial">Partielles</option>
                <option value="failed">Échouées</option>
            </select>
            <label class="flex items-center gap-2 text-xs cursor-pointer" style="color:var(--text-muted)">
                <input type="checkbox" v-model="trashed" class="ac-checkbox">
                Corbeille
            </label>
        </div>

        <div class="pt-card overflow-hidden">
            <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr>
                        <SortableTh field="name" :sort="sort" :dir="dir" @sort="sortBy">Nom</SortableTh>
                        <SortableTh field="subject" :sort="sort" :dir="dir" @sort="sortBy">Sujet</SortableTh>
                        <SortableTh field="status" :sort="sort" :dir="dir" @sort="sortBy">Statut</SortableTh>
                        <th class="ac-th text-right px-5 py-3">Délivrés</th>
                        <th class="ac-th text-right px-5 py-3">Ouverts</th>
                        <SortableTh field="sent_at" :sort="sort" :dir="dir" @sort="sortBy">Envoi</SortableTh>
                        <th class="ac-th px-5 py-3"><span class="sr-only">Actions</span></th>
                    </tr>
                </thead>
                <tbody class="divide-y" style="border-color:var(--border-light)">
                    <tr v-for="c in campaigns.data" :key="c.id" class="ac-row-hover">
                        <td class="px-5 py-3 font-medium" style="color:var(--text-primary)">{{ c.name }}</td>
                        <td class="px-5 py-3" style="color:var(--text-secondary)">{{ c.subject }}</td>
                        <td class="px-5 py-3">
                            <span :class="statusColor[c.status] ?? 'ac-badge-neutral'">{{ c.status }}</span>
                        </td>
                        <td class="px-5 py-3 text-right" style="color:var(--text-secondary)">{{ c.delivered }}</td>
                        <td class="px-5 py-3 text-right" style="color:var(--text-secondary)">{{ c.opened }}</td>
                        <td class="px-5 py-3 text-xs whitespace-nowrap" style="color:var(--text-muted)">{{ c.sent_at ?? c.scheduled_at ?? '—' }}</td>
                        <td class="px-5 py-3 text-right space-x-3 whitespace-nowrap">
                            <template v-if="!c.deleted_at">
                                <Link :href="route('admin.campaigns.edit', c.id)" class="ac-link-primary text-xs">Éditer</Link>
                                <button v-if="c.status === 'draft'" @click="confirmingSend = c" class="ac-link-success text-xs">Envoyer</button>
                                <button @click="confirmingDelete = c" class="text-xs" style="color:var(--color-danger)">Supprimer</button>
                            </template>
                            <button v-else @click="restore(c)" class="ac-link-success text-xs">Restaurer</button>
                        </td>
                    </tr>
                    <tr v-if="!campaigns.data.length">
                        <td colspan="7" class="text-center py-12" style="color:var(--text-muted)">
                            {{ trashed ? 'Corbeille vide.' : 'Aucune campagne. Crée la première.' }}
                        </td>
                    </tr>
                </tbody>
            </table>
            </div>
        </div>

        <AdminPagination :links="campaigns.links" />

        <ConfirmModal :show="!!confirmingSend" @update:show="confirmingSend = null"
            title="Envoyer cette campagne ?" confirm-label="Envoyer" @confirm="send">
            « {{ confirmingSend?.name }} » partira immédiatement vers son audience. Cette action ne peut pas être interrompue.
        </ConfirmModal>

        <ConfirmModal :show="!!confirmingDelete" @update:show="confirmingDelete = null"
            title="Supprimer cette campagne ?" confirm-label="Supprimer" danger @confirm="destroy">
            « {{ confirmingDelete?.name }} » sera placée dans la corbeille (restaurable).
        </ConfirmModal>
    </AdminLayout>
</template>
