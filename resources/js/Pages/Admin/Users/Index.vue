<script setup>
import { router } from '@inertiajs/vue3'
import { onUnmounted, ref, watch } from 'vue'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import AdminPagination from '@/Components/Admin/AdminPagination.vue'
import ConfirmModal from '@/Components/Admin/ConfirmModal.vue'
import FlashAlert from '@/Components/Admin/FlashAlert.vue'

const props = defineProps({ users: Object, roles: Array, filters: Object })

const search   = ref(props.filters?.search ?? '')
const role     = ref(props.filters?.role ?? '')
const verified = ref(props.filters?.verified ?? '')
const trashed  = ref(!!props.filters?.trashed)

let timer = null
watch([search, role, verified, trashed], () => {
    clearTimeout(timer)
    timer = setTimeout(() => {
        router.get(route('admin.users.index'), {
            search: search.value, role: role.value, verified: verified.value,
            trashed: trashed.value ? 1 : undefined,
        }, { preserveState: true, preserveScroll: true, replace: true })
    }, 250)
})
onUnmounted(() => clearTimeout(timer))

const roleBadge = { admin: 'ac-badge-danger', professional: 'ac-badge-signal', candidate: 'ac-badge-neutral' }
const roleLabel = { admin: 'Admin', professional: 'Professionnel', candidate: 'Candidat' }

const confirmingSuspend = ref(null)
const confirmingRole    = ref(null)   // { user, role }

const changeRole = (user, event) => {
    const newRole = event.target.value
    event.target.value = ''   // le select revient au placeholder ; la valeur part au serveur
    if (newRole) confirmingRole.value = { user, role: newRole }
}
const applyRole = () => {
    const { user, role: r } = confirmingRole.value
    router.put(route('admin.users.role', user.id), { role: r }, { preserveScroll: true })
}
const suspend = () => {
    if (confirmingSuspend.value) {
        router.delete(route('admin.users.destroy', confirmingSuspend.value.id), { preserveScroll: true })
    }
}
const restore = (user) => router.post(route('admin.users.restore', user.id), {}, { preserveScroll: true })
const resendVerification = (user) => router.post(route('admin.users.resend-verification', user.id), {}, { preserveScroll: true })
</script>

<template>
    <AdminLayout>
        <Head title="Utilisateurs" />

        <div class="flex items-end justify-between mb-6">
            <div>
                <h1 class="text-2xl font-semibold" style="color:var(--text-primary);font-family:var(--font-display)">Utilisateurs</h1>
                <p class="text-sm mt-1" style="color:var(--text-muted)">Comptes de la plateforme : rôles, vérification email, suspension.</p>
            </div>
        </div>

        <FlashAlert />

        <div class="flex flex-wrap items-center gap-3 mb-4">
            <label for="usr-search" class="sr-only">Rechercher</label>
            <input id="usr-search" v-model="search" placeholder="Rechercher email ou nom…" class="pt-input">
            <label for="usr-role" class="sr-only">Filtrer par rôle</label>
            <select id="usr-role" v-model="role" class="pt-input">
                <option value="">Tous les rôles</option>
                <option v-for="r in roles" :key="r" :value="r">{{ roleLabel[r] ?? r }}</option>
            </select>
            <label for="usr-verified" class="sr-only">Filtrer par vérification email</label>
            <select id="usr-verified" v-model="verified" class="pt-input">
                <option value="">Email : tous</option>
                <option value="yes">Email vérifié</option>
                <option value="no">Email non vérifié</option>
            </select>
            <label class="flex items-center gap-2 text-xs cursor-pointer" style="color:var(--text-muted)">
                <input type="checkbox" v-model="trashed" class="ac-checkbox">
                Suspendus
            </label>
        </div>

        <div class="pt-card overflow-hidden">
            <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr>
                        <th class="ac-th text-left px-5 py-3">Utilisateur</th>
                        <th class="ac-th text-left px-5 py-3">Rôle</th>
                        <th class="ac-th text-center px-5 py-3">Email vérifié</th>
                        <th class="ac-th text-center px-5 py-3">2FA</th>
                        <th class="ac-th text-left px-5 py-3">Dernière connexion</th>
                        <th class="ac-th text-left px-5 py-3">Inscrit le</th>
                        <th class="ac-th px-5 py-3"><span class="sr-only">Actions</span></th>
                    </tr>
                </thead>
                <tbody class="divide-y" style="border-color:var(--border-light)">
                    <tr v-for="u in users.data" :key="u.id" class="ac-row-hover">
                        <td class="px-5 py-3">
                            <p class="font-medium" style="color:var(--text-primary)">
                                {{ u.name }}
                                <span v-if="u.is_self" class="text-xs font-normal" style="color:var(--text-muted)">(vous)</span>
                            </p>
                            <p class="text-xs" style="color:var(--text-muted)">{{ u.email }}</p>
                        </td>
                        <td class="px-5 py-3">
                            <span v-for="r in u.roles" :key="r" :class="roleBadge[r] ?? 'ac-badge-neutral'" class="mr-1">{{ roleLabel[r] ?? r }}</span>
                            <span v-if="!u.roles.length" class="ac-badge-neutral">—</span>
                        </td>
                        <td class="px-5 py-3 text-center">
                            <span :class="u.verified ? 'ac-badge-success' : 'ac-badge-warning'">{{ u.verified ? 'oui' : 'non' }}</span>
                        </td>
                        <td class="px-5 py-3 text-center">
                            <span :class="u.two_factor ? 'ac-badge-success' : 'ac-badge-neutral'">{{ u.two_factor ? 'activé' : '—' }}</span>
                        </td>
                        <td class="px-5 py-3 text-xs whitespace-nowrap" style="color:var(--text-muted)">{{ u.last_login_at ?? '—' }}</td>
                        <td class="px-5 py-3 text-xs whitespace-nowrap" style="color:var(--text-muted)">{{ u.created_at }}</td>
                        <td class="px-5 py-3 text-right space-x-3 whitespace-nowrap">
                            <template v-if="!u.deleted_at">
                                <button v-if="!u.verified" @click="resendVerification(u)" class="ac-link-primary text-xs">Renvoyer l'email</button>
                                <select v-if="!u.is_self" class="pt-input text-xs py-1 inline-block w-auto"
                                    :aria-label="`Changer le rôle de ${u.email}`"
                                    @change="changeRole(u, $event)">
                                    <option value="">Rôle…</option>
                                    <option v-for="r in roles" :key="r" :value="r">{{ roleLabel[r] ?? r }}</option>
                                </select>
                                <button v-if="!u.is_self" @click="confirmingSuspend = u" class="text-xs" style="color:var(--color-danger)">Suspendre</button>
                            </template>
                            <button v-else @click="restore(u)" class="ac-link-success text-xs">Restaurer</button>
                        </td>
                    </tr>
                    <tr v-if="!users.data.length">
                        <td colspan="7" class="text-center py-12" style="color:var(--text-muted)">
                            {{ trashed ? 'Aucun compte suspendu.' : 'Aucun utilisateur ne correspond à ces filtres.' }}
                        </td>
                    </tr>
                </tbody>
            </table>
            </div>
        </div>

        <AdminPagination :links="users.links" />

        <ConfirmModal :show="!!confirmingSuspend" @update:show="confirmingSuspend = null"
            title="Suspendre ce compte ?" confirm-label="Suspendre" danger @confirm="suspend">
            {{ confirmingSuspend?.email }} ne pourra plus se connecter. Ses données sont
            conservées et le compte reste restaurable (case « Suspendus »).
        </ConfirmModal>

        <ConfirmModal :show="!!confirmingRole" @update:show="confirmingRole = null"
            title="Changer le rôle ?" confirm-label="Changer" @confirm="applyRole">
            {{ confirmingRole?.user?.email }} deviendra « {{ roleLabel[confirmingRole?.role] ?? confirmingRole?.role }} ».
            Ses rôles actuels seront remplacés.
        </ConfirmModal>
    </AdminLayout>
</template>
