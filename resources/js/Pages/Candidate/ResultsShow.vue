<script setup>
import { Link } from '@inertiajs/vue3'
import CandidateLayout from '@/Layouts/CandidateLayout.vue'

defineProps({
    attempt: Object,
    result: Object,
    ai_pending: Boolean,
})
</script>

<template>
    <CandidateLayout>
        <Head title="Tes résultats" />

        <div class="max-w-3xl mx-auto">
            <div class="text-center mb-12">
                <span class="pt-badge">Profil cartographié</span>
                <h1 class="text-4xl font-semibold tracking-tight mt-4">Voilà ce qui te ressemble.</h1>
                <p class="text-slate-600 mt-2">Synthèse personnalisée par notre IA, à partir de tes réponses et de ton CV.</p>
            </div>

            <div v-if="ai_pending" class="pt-card p-12 text-center">
                <div class="inline-block h-10 w-10 rounded-full border-4 border-indigo-200 border-t-indigo-600 animate-spin"></div>
                <p class="mt-4 text-slate-600">Génération en cours… (1 à 2 min)</p>
                <p class="text-xs text-slate-400 mt-2">Cette page se mettra à jour automatiquement.</p>
            </div>

            <template v-else>
                <!-- Synthèse -->
                <section class="pt-card p-8 mb-8">
                    <h2 class="text-xl font-semibold mb-4">Ta synthèse</h2>
                    <div class="prose prose-slate max-w-none whitespace-pre-line text-[15px] leading-relaxed">{{ result.ai_synthesis }}</div>
                </section>

                <!-- Scoring dimensions -->
                <section v-if="result.scoring?.dimensions" class="pt-card p-8 mb-8">
                    <h2 class="text-xl font-semibold mb-6">Tes dimensions</h2>
                    <div class="space-y-4">
                        <div v-for="(value, key) in result.scoring.dimensions" :key="key">
                            <div class="flex justify-between text-sm mb-1">
                                <span class="font-medium capitalize">{{ key }}</span>
                                <span class="text-slate-500">{{ value }}/100</span>
                            </div>
                            <div class="pt-progress-track">
                                <div class="pt-progress-fill" :style="{ width: value + '%' }"></div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Métiers suggérés -->
                <section v-if="result.suggested_jobs?.length" class="pt-card p-8 mb-8">
                    <h2 class="text-xl font-semibold mb-6">{{ result.suggested_jobs.length }} métiers à explorer</h2>
                    <div class="grid md:grid-cols-2 gap-4">
                        <article v-for="(job, i) in result.suggested_jobs" :key="i" class="border border-slate-100 rounded-xl p-5 hover:border-indigo-300 transition">
                            <div class="flex items-start justify-between gap-3 mb-2">
                                <h3 class="font-semibold">{{ job.titre || job.title }}</h3>
                                <span class="text-xs px-2 py-1 rounded-full bg-emerald-50 text-emerald-700 font-medium whitespace-nowrap">{{ job.fit_score }}%</span>
                            </div>
                            <p class="text-xs uppercase tracking-wide text-slate-400 mb-3">{{ job.secteur || job.sector }}</p>
                            <p class="text-sm text-slate-700">{{ job.pourquoi || job.why }}</p>
                            <p v-if="job.prochaine_étape || job.next_step" class="text-xs text-indigo-700 mt-3 font-medium">→ {{ job.prochaine_étape || job.next_step }}</p>
                        </article>
                    </div>
                </section>

                <div class="text-center mt-12">
                    <a :href="route('results.pdf', attempt.id)" class="pt-btn-ghost">Télécharger en PDF</a>
                </div>
            </template>
        </div>
    </CandidateLayout>
</template>
