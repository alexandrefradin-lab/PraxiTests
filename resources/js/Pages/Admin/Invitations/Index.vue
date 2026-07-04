<script setup>
import { Link, router } from '@inertiajs/vue3'
import { onUnmounted, ref, watch } from 'vue'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import AdminPagination from '@/Components/Admin/AdminPagination.vue'
import ConfirmModal from '@/Components/Admin/ConfirmModal.vue'
import FlashAlert from '@/Components/Admin/FlashAlert.vue'

const props = defineProps({ invitations: Object, filters: Object })

const search  = ref(props.filters?.search ?? '')
const status  = ref(props.filters?.status ?? '')
const trashed = ref(!!props.filters?.trashed)

let timer = null
watch([search, status, trashed], () => {
    clearTimeout(timer)
    timer = setTimeout(() => {
        router.get(route('admin.invitations.index'),
            { search: search.value, status: status.value, trashed: trashed.value ? 1 : undefined },
            { preserveState: true, preserveScroll: true, replace: true })
    }, 250)
})
onUnmounted(() => clearTimeout(timer))

const statusColor = {
    pending:   'ac-badge-neutral',
    sent:      'ac-badge-signal',
    opened:    'ac-badge-warning',
    started:   'ac-badge-warning',
    completed: 'ac-badge-success',
    expired:   'ac-badge-danger',
}
const statusLabel = {
    pending: 'En attente', sent: 'Envoyée', opened: 'Ouverte',
    started: 'Commencée', completed: 'Terminée', expired: 'Expirée',
}

const confirmingDelete = ref(null)   // invitation à supprimer
const resend = (inv) => router.post(route('admin.invitations.resend', inv.id), {}, { preserveScroll: true })
const destroy = () => {
    if (confirmingDelete.value) {
        router.delete(route('admin.invitations.destroy', confirmingDelete.value.id), { preserveScroll: true })
    }
}
const restore = (inv) => router.post(route('admin.invitations.restore', inv.id), {}, { preserveScroll: true })

const exportUrl = () => route('admin.invitations.export', {
    search: search.value || undefined,
    status: status.value || undefined,
    trashed: trashed.value ? 1 : undefined,
})
</script>

<template>
    <AdminLayout>
        <Head title="Invitations" />

        <div class="flex flex-wrap items-end justify-between gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-semibold" style="color:var(--text-primary);font-family:var(--font-display)">Invitations</h1>
                <p class="text-sm mt-1" style="color:var(--text-muted)">Suivi des invitations candidat : relance, expiration, consentement.</p>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <a :href="exportUrl()" class="ac-btn-ghost text-xs">Exporter CSV</a>
                <Link :href="route('admin.invitations.create')" class="ac-btn-primary">+ Inviter un candidat</Link>
            </div>
        </div>

        <FlashAlert />

        <div class="flex flex-wrap items-center gap-3 mb-4">
            <label for="inv-search" class="sr-only">Rechercher</label>
            <input id="inv-search" v-model="search" placeholder="Rechercher email ou nom…" class="pt-input">
            <label for="inv-status" class="sr-only">Filtrer par statut</label>
            <select id="inv-status" v-model="status" class="pt-input">
                <option value="">Tous les statuts</option>
                <option value="pending">En attente</option>
                <option value="sent">Envoyées</option>
                <option value="opened">Ouvertes</option>
                <option value="started">Commencées</option>
                <option value="completed">Terminées</option>
                <option value="expired">Expirées</option>
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
                        <th class="ac-th text-left px-5 py-3">Candidat</th>
                        <th class="ac-th text-left px-5 py-3">Épreuve(s)</th>
                        <th class="ac-th text-left px-5 py-3">Statut</th>
                        <th class="ac-th text-left px-5 py-3">Envoyée</th>
                        <th class="ac-th text-left px-5 py-3">Expire</th>
                        <th class="ac-th text-center px-5 py-3">Partage</th>
                        <th class="ac-th px-5 py-3"><span class="sr-only">Actions</span></th>
                    </tr>
                </thead>
                <tbody class="divide-y" style="border-color:var(--border-light)">
                    <tr v-for="inv in invitations.data" :key="inv.id" class="ac-row-hover">
                        <td class="px-5 py-3">
                            <p class="font-medium" style="color:var(--text-primary)">{{ inv.name ?? '—' }}</p>
                            <p class="text-xs" style="color:var(--text-muted)">{{ inv.email }}</p>
                        </td>
                        <td class="px-5 py-3" style="color:var(--text-secondary)">
                            {{ inv.test ?? '—' }}
                            <span v-if="inv.tests_count > 1" class="text-xs" style="color:var(--text-muted)">+{{ inv.tests_count - 1 }}</span>
                        </td>
                        <td class="px-5 py-3">
                            <span :class="inv.is_expired ? statusColor.expired : (statusColor[inv.status] ?? 'ac-badge-neutral')">
                                {{ inv.is_expired ? statusLabel.expired : (statusLabel[inv.status] ?? inv.status) }}
                            </span>
                        </td>
                        <td class="px-5 py-3 text-xs whitespace-nowrap" style="color:var(--text-muted)">{{ inv.sent_at ?? '—' }}</td>
                        <td class="px-5 py-3 text-xs whitespace-nowrap" style="color:var(--text-muted)">{{ inv.expires_at ?? '—' }}</td>
                        <td class="px-5 py-3 text-center">
                            <span :class="inv.consent ? 'ac-badge-success' : 'ac-badge-neutral'">{{ inv.consent ? 'oui' : 'non' }}</span>
                        </td>
                        <td class="px-5 py-3 text-right space-x-3 whitespace-nowrap">
                            <template v-if="!inv.deleted_at">
                                <button v-if="inv.status !== 'completed'" @click="resend(inv)" class="ac-link-primary text-xs">Relancer</button>
                                <button @click="confirmingDelete = inv" class="text-xs" style="color:var(--color-danger)">Supprimer</button>
                            </template>
                            <button v-else @click="restore(inv)" class="ac-link-success text-xs">Restaurer</button>
                        </td>
                    </tr>
                    <tr v-if="!invitations.data.length">
                        <td colspan="7" class="text-center py-12" style="color:var(--text-muted)">
                            {{ trashed ? 'Corbeille vide.' : 'Aucune invitation. Invitez votre premier candidat.' }}
                        </td>
                    </tr>
                </tbody>
            </table>
            </div>
        </div>

        <AdminPagination :links="invitations.links" />

        <ConfirmModal :show="!!confirmingDelete" @update:show="confirmingDelete = null"
            title="Supprimer cette invitation ?" confirm-label="Supprimer" danger @confirm="destroy">
            Le lien envoyé à {{ confirmingDelete?.email }} deviendra inutilisable.
            L'invitation reste restaurable depuis la corbeille.
        </ConfirmModal>
    </AdminLayout>
</template>
