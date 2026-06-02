<script setup>
import { computed } from 'vue'
import CandidateLayout from '@/Layouts/CandidateLayout.vue'

const props = defineProps({ attempt: Object, result: Object })
const scoring = computed(() => props.result?.scoring ?? {})
const karasek = computed(() => scoring.value.karasek ?? {})
const mbi = computed(() => scoring.value.mbi ?? {})
const profile = computed(() => scoring.value.profile)
const meta = computed(() => scoring.value.meta_profiles?.[profile.value] ?? {})

const severityColor = {
    faible: 'text-emerald-700 bg-emerald-50',
    modere: 'text-amber-700 bg-amber-50',
    eleve:  'text-rose-700 bg-rose-50',
}
const severityLabel = { faible: 'Faible', modere: 'Modéré', eleve: 'Élevé' }
</script>

<template>
    <CandidateLayout>
        <Head title="Tes résultats — PraxiCare" />

        <div class="max-w-3xl mx-auto">
            <div class="text-center mb-12">
                <span class="pt-badge">PraxiCare · Karasek + MBI</span>
                <h1 class="text-4xl font-semibold tracking-tight mt-4">Ce que ton travail te coûte aujourd'hui.</h1>
                <p class="text-slate-600 mt-2 max-w-xl mx-auto text-sm">Outil d'aide à la prise de conscience. Ne remplace pas un accompagnement humain ni un diagnostic médical.</p>
            </div>

            <!-- Profil Karasek -->
            <section class="pt-card p-8 mb-8 border-l-4" :style="{ borderColor: meta.color }">
                <p class="text-xs uppercase tracking-wide text-slate-400">Profil Karasek</p>
                <h2 class="text-2xl font-semibold mt-1" :style="{ color: meta.color }">{{ scoring.profile_label }}</h2>
                <p class="text-sm text-slate-700 mt-2">{{ meta.desc }}</p>
            </section>

            <!-- Karasek scores -->
            <section class="pt-card p-8 mb-8">
                <h2 class="text-xl font-semibold mb-6">Karasek — Job strain</h2>
                <div class="grid md:grid-cols-3 gap-5">
                    <div v-for="(label, key) in { demandes: 'Demandes psychologiques', latitude: 'Latitude décisionnelle', soutien: 'Soutien social' }" :key="key">
                        <p class="text-sm font-medium text-slate-700">{{ label }}</p>
                        <div class="flex items-baseline gap-1 mt-1">
                            <span class="text-3xl font-semibold">{{ karasek[key] }}</span>
                            <span class="text-sm text-slate-400">/ {{ karasek[key + '_max'] }}</span>
                        </div>
                        <div class="pt-progress-track mt-2">
                            <div class="pt-progress-fill" :style="{ width: ((karasek[key] / karasek[key + '_max']) * 100) + '%' }"></div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- MBI scores -->
            <section class="pt-card p-8 mb-8">
                <h2 class="text-xl font-semibold mb-6">MBI — Maslach Burnout Inventory</h2>
                <div class="space-y-5">
                    <div v-for="(item, idx) in [
                        { key: 'ee', label: 'Épuisement émotionnel', desc: 'Plus le score est élevé, plus tu es vidé·e émotionnellement par ton travail.' },
                        { key: 'dp', label: 'Dépersonnalisation', desc: 'Plus le score est élevé, plus tu prends de la distance émotionnelle avec les autres.' },
                        { key: 'ap', label: 'Accomplissement personnel', desc: 'Plus le score est élevé, moins tu te sens accompli·e dans ton travail.' },
                    ]" :key="item.key" class="border-b border-slate-100 pb-5 last:border-0">
                        <div class="flex justify-between items-start gap-3 mb-2">
                            <div>
                                <p class="font-medium">{{ item.label }}</p>
                                <p class="text-xs text-slate-500 mt-1">{{ item.desc }}</p>
                            </div>
                            <span :class="severityColor[mbi[item.key + '_severite']]" class="text-xs px-2 py-1 rounded-full font-medium whitespace-nowrap">{{ severityLabel[mbi[item.key + '_severite']] }}</span>
                        </div>
                        <div class="flex items-baseline gap-1">
                            <span class="text-2xl font-semibold">{{ mbi[item.key] }}</span>
                            <span class="text-sm text-slate-400">/ {{ mbi[item.key + '_max'] }}</span>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Synthèse IA -->
            <section v-if="attempt.result?.ai_synthesis" class="pt-card p-8 mb-8">
                <h2 class="text-xl font-semibold mb-4">Ta synthèse</h2>
                <div class="prose prose-slate max-w-none whitespace-pre-line text-[15px] leading-relaxed">{{ attempt.result.ai_synthesis }}</div>
            </section>

            <div class="text-center mt-12">
                <a :href="route('results.pdf', attempt.id)" class="pt-btn-ghost">Télécharger en PDF</a>
            </div>
        </div>
    </CandidateLayout>
</template>
