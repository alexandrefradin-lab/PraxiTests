<script setup>
import { router } from '@inertiajs/vue3'
import { ref } from 'vue'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import AdminPagination from '@/Components/Admin/AdminPagination.vue'
import ConfirmModal from '@/Components/Admin/ConfirmModal.vue'
import FlashAlert from '@/Components/Admin/FlashAlert.vue'

const props = defineProps({ results: Object, zombie_count: { type: Number, default: 0 } })

const confirmingRetryAll  = ref(false)
const confirmingRetryZombies = ref(false)
const retry       = (attemptId) => router.post(route('admin.attempts.retry-insights', attemptId), {}, { preserveScroll: true })
const retryAll    = () => router.post(route('admin.attempts.retry-all-insights'), {}, { preserveScroll: true })
const retryZombies = () => router.post(route('admin.attempts.retry-zombie-insights'), {}, { preserveScroll: true })
</script>

<template>
    <AdminLayout>
        <Head title="Synthèses IA en échec" />

        <div class="flex flex-wrap items-end justify-between gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-semibold" style="color:var(--text-primary);font-family:var(--font-display)">Synthèses IA en échec</h1>
                <p class="text-sm mt-1" style="color:var(--text-muted)">
                    Générations qui ont échoué (timeout, clé API, quota…). La relance remet la synthèse en file.
                </p>
            </div>
            <div class="flex gap-3 flex-wrap">
                <!-- Zombies : ai_synthesis=null + ai_failed=false + completed > 5 min (process PHP tué sans fallback) -->
                <button v-if="zombie_count > 0" @click="confirmingRetryZombies = true"
                    class="ac-btn-secondary text-sm"
                    title="Tentatives sans synthèse ni erreur — process PHP tué avant le fallback">
                    Zombies ({{ zombie_count }})
                </button>
                <button v-if="results.data.length" @click="confirmingRetryAll = true" class="ac-btn-primary">
                    Tout relancer ({{ results.total }})
                </button>
            </div>
        </div>

        <FlashAlert />

        <div class="pt-card overflow-hidden">
            <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr>
                        <th class="ac-th text-left px-5 py-3">Candidat</th>
                        <th class="ac-th text-left px-5 py-3">Épreuve</th>
                        <th class="ac-th text-left px-5 py-3">Terminée le</th>
                        <th class="ac-th text-left px-5 py-3">Erreur</th>
                        <th class="ac-th px-5 py-3"><span class="sr-only">Actions</span></th>
                    </tr>
                </thead>
                <tbody class="divide-y" style="border-color:var(--border-light)">
                    <tr v-for="r in results.data" :key="r.id" class="ac-row-hover">
                        <td class="px-5 py-3">
                            <p class="font-medium" style="color:var(--text-primary)">{{ r.user ?? '—' }}</p>
                            <p class="text-xs" style="color:var(--text-muted)">{{ r.email }}</p>
                        </td>
                        <td class="px-5 py-3" style="color:var(--text-secondary)">{{ r.test ?? '—' }}</td>
                        <td class="px-5 py-3 text-xs whitespace-nowrap" style="color:var(--text-muted)">{{ r.completed_at ?? '—' }}</td>
                        <td class="px-5 py-3">
                            <code v-if="r.ai_error" class="text-xs px-2 py-1 rounded block max-w-md truncate" :title="r.ai_error"
                                style="background:color-mix(in srgb, var(--color-danger) 8%, transparent);color:var(--color-danger);font-family:var(--font-data)">
                                {{ r.ai_error }}
                            </code>
                            <span v-else class="text-xs" style="color:var(--text-muted)">—</span>
                        </td>
                        <td class="px-5 py-3 text-right">
                            <button @click="retry(r.attempt_id)" class="ac-link-primary text-xs">Relancer</button>
                        </td>
                    </tr>
                    <tr v-if="!results.data.length">
                        <td colspan="5" class="text-center py-12" style="color:var(--text-muted)">
                            Aucune synthèse en échec — tout roule. ✦
                        </td>
                    </tr>
                </tbody>
            </table>
            </div>
        </div>

        <AdminPagination :links="results.links" />

        <ConfirmModal v-model:show="confirmingRetryAll" title="Tout relancer ?" confirm-label="Relancer tout" @confirm="retryAll">
            {{ results.total }} génération(s) repartiront en file de traitement. Chaque relance
            consomme des crédits IA.
        </ConfirmModal>

        <ConfirmModal v-model:show="confirmingRetryZombies" title="Relancer les zombies ?" confirm-label="Relancer" @confirm="retryZombies">
            {{ zombie_count }} tentative(s) sans synthèse ni erreur (process PHP tué avant le fallback).
            Chaque relance consomme des crédits IA.
        </ConfirmModal>
    </AdminLayout>
</template>
