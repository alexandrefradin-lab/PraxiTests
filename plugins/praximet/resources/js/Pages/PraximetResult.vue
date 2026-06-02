<script setup>
import { computed } from 'vue'
import CandidateLayout from '@/Layouts/CandidateLayout.vue'

const props = defineProps({
    attempt: Object,
    result: Object,
})

const scoring = computed(() => props.result?.scoring ?? {})
const dimensions = computed(() => scoring.value.dimensions ?? {})
const code = computed(() => scoring.value.code ?? '')
const meta = computed(() => scoring.value.types_meta ?? {})

const codeLetters = computed(() => code.value.split(''))
</script>

<template>
    <CandidateLayout>
        <Head title="Tes résultats RIASEC" />

        <div class="max-w-4xl mx-auto">
            <div class="text-center mb-12">
                <span class="pt-badge">Test RIASEC · Modèle Holland</span>
                <h1 class="text-4xl font-semibold tracking-tight mt-4">Ton code RIASEC</h1>
                <div class="flex items-center justify-center gap-3 mt-6">
                    <span v-for="(letter, i) in codeLetters" :key="i" class="h-16 w-16 rounded-2xl flex items-center justify-center text-3xl font-bold text-white shadow-md" :style="{ backgroundColor: meta[letter]?.color ?? '#4F46E5' }">
                        {{ letter }}
                    </span>
                </div>
                <p class="text-slate-600 mt-4">{{ scoring.profile_label }}</p>
            </div>

            <!-- Synthèse IA si dispo -->
            <section v-if="attempt.result?.ai_synthesis" class="pt-card p-8 mb-8">
                <h2 class="text-xl font-semibold mb-4">Ta synthèse</h2>
                <div class="prose prose-slate max-w-none whitespace-pre-line text-[15px] leading-relaxed">{{ attempt.result.ai_synthesis }}</div>
            </section>

            <!-- 6 types RIASEC -->
            <section class="pt-card p-8 mb-8">
                <h2 class="text-xl font-semibold mb-6">Tes 6 dimensions</h2>
                <div class="space-y-5">
                    <div v-for="(score, key) in dimensions" :key="key">
                        <div class="flex items-center justify-between mb-1">
                            <div>
                                <span class="font-semibold" :style="{ color: meta[key]?.color }">{{ meta[key]?.label ?? key }}</span>
                                <p class="text-xs text-slate-500 mt-0.5">{{ meta[key]?.desc }}</p>
                            </div>
                            <span class="text-sm font-medium text-slate-700">{{ score }}%</span>
                        </div>
                        <div class="pt-progress-track">
                            <div class="h-full rounded-full transition-all duration-700" :style="{ width: score + '%', backgroundColor: meta[key]?.color }"></div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Sous-domaines -->
            <section v-if="scoring.sous_domaines" class="pt-card p-8 mb-8">
                <h2 class="text-xl font-semibold mb-6">Détail par sous-domaine</h2>
                <div class="grid md:grid-cols-2 gap-5">
                    <div v-for="(subs, type) in scoring.sous_domaines" :key="type" class="border border-slate-100 rounded-xl p-5">
                        <h3 class="font-semibold mb-3" :style="{ color: meta[type]?.color }">{{ meta[type]?.label }}</h3>
                        <div v-for="(value, label) in subs" :key="label" class="flex justify-between text-sm py-1">
                            <span class="text-slate-600">{{ label }}</span>
                            <span class="font-medium">{{ value }}/7</span>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Métiers IA -->
            <section v-if="result?.suggested_jobs?.length" class="pt-card p-8 mb-8">
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
        </div>
    </CandidateLayout>
</template>
