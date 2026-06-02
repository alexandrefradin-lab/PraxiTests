<script setup>
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import CandidateLayout from '@/Layouts/CandidateLayout.vue'

const props = defineProps({
    attempts: Array,
})

const completed = computed(() => props.attempts.filter(a => a.status === 'completed'))
const inProgress = computed(() => props.attempts.filter(a => a.status === 'in_progress'))

const formatDate = (iso) => {
    if (!iso) return '—'
    return new Intl.DateTimeFormat('fr-FR', { day: '2-digit', month: 'short', year: 'numeric' }).format(new Date(iso))
}
</script>

<template>
    <CandidateLayout>
        <Head title="Mon historique" />

        <div class="max-w-3xl mx-auto">
            <div class="flex items-end justify-between mb-8">
                <div>
                    <h1 class="text-3xl font-semibold tracking-tight">Mon historique</h1>
                    <p class="text-slate-600 mt-1">Tous tes tests et résultats.</p>
                </div>
                <Link :href="route('tests.index')" class="pt-btn-ghost text-sm">
                    Voir les tests disponibles →
                </Link>
            </div>

            <!-- Tentatives en cours -->
            <section v-if="inProgress.length" class="mb-10">
                <h2 class="text-xs font-semibold uppercase tracking-wider text-slate-500 mb-4">En cours</h2>
                <div class="space-y-3">
                    <div v-for="a in inProgress" :key="a.id" class="pt-card p-5 flex items-center justify-between gap-4 border-l-4 border-indigo-400">
                        <div>
                            <p class="font-medium">{{ a.test_name }}</p>
                            <p class="text-xs text-slate-500 mt-1">Commencé le {{ formatDate(a.started_at) }}</p>
                        </div>
                        <Link :href="route('attempt.show', a.id)" class="pt-btn-primary text-sm flex-shrink-0">
                            Reprendre →
                        </Link>
                    </div>
                </div>
            </section>

            <!-- Tests complétés -->
            <section v-if="completed.length">
                <h2 class="text-xs font-semibold uppercase tracking-wider text-slate-500 mb-4">Complétés</h2>
                <div class="space-y-3">
                    <div v-for="a in completed" :key="a.id" class="pt-card p-5 flex items-center justify-between gap-4">
                        <div class="flex-1 min-w-0">
                            <p class="font-medium">{{ a.test_name }}</p>
                            <p class="text-xs text-slate-500 mt-1">Complété le {{ formatDate(a.completed_at) }}</p>
                        </div>

                        <div class="flex items-center gap-3 flex-shrink-0">
                            <!-- Badge IA -->
                            <span v-if="a.ai_ready && a.jobs_count" class="text-xs px-2 py-1 rounded-full bg-emerald-50 text-emerald-700 font-medium">
                                {{ a.jobs_count }} métiers
                            </span>
                            <span v-else-if="!a.ai_ready" class="text-xs px-2 py-1 rounded-full bg-amber-50 text-amber-700">
                                IA en cours…
                            </span>

                            <Link :href="route('results.show', a.id)" class="pt-btn-ghost text-sm">
                                Voir les résultats
                            </Link>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Aucun résultat -->
            <div v-if="!attempts.length" class="pt-card p-12 text-center">
                <p class="text-slate-500">Tu n'as pas encore passé de test.</p>
                <Link :href="route('tests.index')" class="pt-btn-primary mt-4 inline-block">
                    Découvrir les tests →
                </Link>
            </div>
        </div>
    </CandidateLayout>
</template>
