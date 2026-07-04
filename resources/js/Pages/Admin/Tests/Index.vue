<script setup>
import { Link, router } from '@inertiajs/vue3'
import { onUnmounted, ref, watch } from 'vue'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import AdminPagination from '@/Components/Admin/AdminPagination.vue'
import FlashAlert from '@/Components/Admin/FlashAlert.vue'

const props = defineProps({ tests: Object, filters: Object })

const search    = ref(props.filters?.search ?? '')
const published = ref(props.filters?.published ?? '')
const trashed   = ref(!!props.filters?.trashed)

let timer = null
watch([search, published, trashed], () => {
    clearTimeout(timer)
    timer = setTimeout(() => {
        router.get(route('admin.tests.index'),
            { search: search.value, published: published.value, trashed: trashed.value ? 1 : undefined },
            { preserveState: true, preserveScroll: true, replace: true })
    }, 250)
})
onUnmounted(() => clearTimeout(timer))

const restore = (t) => router.post(route('admin.tests.restore', t.id), {}, { preserveScroll: true })
</script>

<template>
    <AdminLayout>
        <Head title="Tests" />

        <div class="flex items-end justify-between mb-6">
            <div>
                <h1 class="text-2xl font-semibold" style="font-family:var(--font-display);color:var(--text-primary)">Tests</h1>
                <p class="text-sm mt-1" style="color:var(--text-muted)">Tous les tests de ta plateforme — natifs et issus de plugins.</p>
            </div>
            <Link :href="route('admin.tests.create')" class="pt-btn-primary">+ Nouveau test</Link>
        </div>

        <FlashAlert />

        <div class="flex flex-wrap items-center gap-3 mb-4">
            <label for="tst-search" class="sr-only">Rechercher</label>
            <input id="tst-search" v-model="search" placeholder="Rechercher nom ou slug…" class="pt-input">
            <label for="tst-published" class="sr-only">Filtrer par statut de publication</label>
            <select id="tst-published" v-model="published" class="pt-input">
                <option value="">Publiés et brouillons</option>
                <option value="yes">Publiés</option>
                <option value="no">Brouillons</option>
            </select>
            <label class="flex items-center gap-2 text-xs cursor-pointer" style="color:var(--text-muted)">
                <input type="checkbox" v-model="trashed" class="ac-checkbox">
                Corbeille
            </label>
        </div>

        <div class="pt-card overflow-hidden">
            <table class="w-full text-sm">
                <thead>
                    <tr>
                        <th class="ac-th text-left px-5 py-3">Nom</th>
                        <th class="ac-th text-left px-5 py-3">Type</th>
                        <th class="ac-th text-left px-5 py-3">Plugin</th>
                        <th class="ac-th text-left px-5 py-3">Durée</th>
                        <th class="ac-th text-left px-5 py-3">Statut</th>
                        <th class="ac-th px-5 py-3"><span class="sr-only">Actions</span></th>
                    </tr>
                </thead>
                <tbody class="divide-y" style="border-color:var(--border-light)">
                    <tr v-for="t in tests.data" :key="t.id" class="pt-row-hover">
                        <td class="px-5 py-3 font-medium" style="color:var(--text-primary)">{{ t.name }}</td>
                        <td class="px-5 py-3" style="color:var(--text-secondary)">{{ t.type }}</td>
                        <td class="px-5 py-3" style="color:var(--text-secondary)">{{ t.plugin?.name ?? '— natif —' }}</td>
                        <td class="px-5 py-3" style="color:var(--text-secondary)">{{ t.estimated_minutes }} min</td>
                        <td class="px-5 py-3">
                            <span :class="t.published ? 'ac-badge-success' : 'ac-badge-neutral'">
                                {{ t.published ? 'Publié' : 'Brouillon' }}
                            </span>
                        </td>
                        <td class="px-5 py-3 text-right">
                            <Link v-if="!t.deleted_at" :href="route('admin.tests.edit', t.id)" class="ac-link-primary text-xs">Éditer</Link>
                            <button v-else @click="restore(t)" class="ac-link-success text-xs">Restaurer</button>
                        </td>
                    </tr>
                    <tr v-if="!tests.data.length">
                        <td colspan="6" class="text-center py-12" style="color:var(--text-muted)">
                            {{ trashed ? 'Corbeille vide.' : 'Aucun test pour ces filtres.' }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <AdminPagination :links="tests.links" />
    </AdminLayout>
</template>

<style scoped>
.pt-row-hover { transition: background-color .15s ease; }
.pt-row-hover:hover { background-color: var(--bg-elevated); }
</style>
