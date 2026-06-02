<script setup>
import { computed } from 'vue'
import CandidateLayout from '@/Layouts/CandidateLayout.vue'

const props = defineProps({ attempt: Object, result: Object })

const scoring = computed(() => props.result?.scoring ?? {})
const top5 = computed(() => Object.entries(scoring.value.top5 ?? {}))
const all = computed(() => Object.entries(scoring.value.dimensions ?? {}))
const meta = computed(() => scoring.value.meta ?? {})
</script>

<template>
    <CandidateLayout>
        <Head title="Tes valeurs prioritaires" />

        <div class="max-w-3xl mx-auto">
            <div class="text-center mb-12">
                <span class="pt-badge">Schwartz · 10 valeurs universelles</span>
                <h1 class="text-4xl font-semibold tracking-tight mt-4">Voici ce qui te porte.</h1>
                <p class="text-slate-600 mt-2">Tes 5 valeurs prioritaires, classées de la plus à la moins importante pour toi.</p>
            </div>

            <!-- Top 5 -->
            <section class="pt-card p-8 mb-8">
                <h2 class="text-xl font-semibold mb-6">Ton top 5</h2>
                <ol class="space-y-4">
                    <li v-for="([key, score], i) in top5" :key="key" class="flex items-center gap-4 p-4 rounded-xl border border-slate-100">
                        <span class="h-10 w-10 rounded-full flex items-center justify-center font-bold text-white shadow-sm" :style="{ backgroundColor: meta[key]?.color }">{{ i + 1 }}</span>
                        <div class="flex-1">
                            <p class="font-semibold" :style="{ color: meta[key]?.color }">{{ meta[key]?.label }}</p>
                            <p class="text-sm text-slate-600 mt-0.5">{{ meta[key]?.court }}</p>
                        </div>
                        <span class="text-2xl font-semibold text-slate-700">{{ score }}</span>
                    </li>
                </ol>
            </section>

            <!-- Toutes les dimensions -->
            <section class="pt-card p-8 mb-8">
                <h2 class="text-xl font-semibold mb-6">Tes 10 valeurs</h2>
                <div class="space-y-4">
                    <div v-for="([key, score]) in all" :key="key">
                        <div class="flex justify-between text-sm mb-1">
                            <span class="font-medium" :style="{ color: meta[key]?.color }">{{ meta[key]?.label }}</span>
                            <span class="text-slate-500">{{ score }}/100</span>
                        </div>
                        <div class="pt-progress-track">
                            <div class="h-full rounded-full transition-all duration-700" :style="{ width: score + '%', backgroundColor: meta[key]?.color }"></div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Définitions -->
            <section class="pt-card p-8 mb-8">
                <h2 class="text-xl font-semibold mb-6">Comprendre tes valeurs</h2>
                <div class="space-y-5">
                    <div v-for="([key]) in top5" :key="key" class="border-l-4 pl-4 py-1" :style="{ borderColor: meta[key]?.color }">
                        <h3 class="font-semibold" :style="{ color: meta[key]?.color }">{{ meta[key]?.label }}</h3>
                        <p class="text-sm text-slate-700 mt-1">{{ meta[key]?.definition }}</p>
                    </div>
                </div>
            </section>

            <div class="text-center mt-12">
                <a :href="route('results.pdf', attempt.id)" class="pt-btn-ghost">Télécharger en PDF</a>
            </div>
        </div>
    </CandidateLayout>
</template>
