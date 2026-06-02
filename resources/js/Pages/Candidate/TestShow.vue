<script setup>
import { computed } from 'vue'
import { router } from '@inertiajs/vue3'
import CandidateLayout from '@/Layouts/CandidateLayout.vue'

const props = defineProps({
    test: Object,
    profile_complete: Boolean,
    already_attempted: Boolean,    // tentative completed existante
    attempt_in_progress: Object,   // tentative in_progress si elle existe
})

const totalQuestions = computed(() =>
    props.test.sections?.reduce((acc, s) => acc + (s.questions?.length ?? 0), 0) ?? 0
)

const start = () => {
    if (props.attempt_in_progress) {
        router.get(route('attempt.show', props.attempt_in_progress.id))
    } else {
        router.post(route('attempt.start', props.test.slug))
    }
}
</script>

<template>
    <CandidateLayout>
        <Head :title="test.name" />

        <div class="max-w-2xl mx-auto">

            <!-- Fil d'Ariane -->
            <Link :href="route('tests.index')" class="text-sm text-slate-500 hover:text-slate-800 transition">
                ← Tous les tests
            </Link>

            <!-- En-tête -->
            <div class="mt-6 mb-8">
                <span class="pt-badge mb-3">{{ test.type }}</span>
                <h1 class="text-3xl font-semibold tracking-tight mt-2">{{ test.name }}</h1>
                <p v-if="test.description" class="text-slate-600 mt-3 text-base leading-relaxed">{{ test.description }}</p>
            </div>

            <!-- Infos pratiques -->
            <div class="pt-card p-6 mb-6 grid grid-cols-3 gap-4 text-center">
                <div>
                    <p class="text-2xl font-bold text-indigo-600">≈ {{ test.estimated_minutes }}</p>
                    <p class="text-xs text-slate-500 mt-1">minutes</p>
                </div>
                <div>
                    <p class="text-2xl font-bold text-indigo-600">{{ totalQuestions }}</p>
                    <p class="text-xs text-slate-500 mt-1">questions</p>
                </div>
                <div>
                    <p class="text-2xl font-bold text-emerald-600">IA</p>
                    <p class="text-xs text-slate-500 mt-1">synthèse incluse</p>
                </div>
            </div>

            <!-- Sections -->
            <div v-if="test.sections?.length" class="pt-card p-6 mb-6">
                <h2 class="font-semibold mb-4 text-sm uppercase tracking-wide text-slate-500">Contenu du test</h2>
                <ul class="space-y-3">
                    <li v-for="(section, i) in test.sections" :key="section.id" class="flex items-start gap-3">
                        <span class="mt-0.5 h-5 w-5 rounded-full bg-indigo-100 text-indigo-700 text-xs font-bold flex items-center justify-center flex-shrink-0">{{ i + 1 }}</span>
                        <div>
                            <p class="text-sm font-medium">{{ section.title }}</p>
                            <p v-if="section.description" class="text-xs text-slate-500 mt-0.5">{{ section.description }}</p>
                        </div>
                    </li>
                </ul>
            </div>

            <!-- Ce que tu vas obtenir -->
            <div class="pt-card p-6 mb-8 bg-indigo-50 border-indigo-100">
                <h2 class="font-semibold mb-3">Ce que tu vas obtenir</h2>
                <ul class="space-y-2 text-sm text-slate-700">
                    <li class="flex items-center gap-2">
                        <span class="text-emerald-600 font-bold">✓</span>
                        Une synthèse personnalisée de ton profil (générée par IA)
                    </li>
                    <li class="flex items-center gap-2">
                        <span class="text-emerald-600 font-bold">✓</span>
                        15 métiers adaptés à tes résultats
                    </li>
                    <li class="flex items-center gap-2">
                        <span class="text-emerald-600 font-bold">✓</span>
                        Un rapport PDF téléchargeable
                    </li>
                </ul>
            </div>

            <!-- Alerte profil incomplet -->
            <div v-if="!profile_complete" class="pt-card p-5 mb-6 border-l-4 border-amber-400 bg-amber-50">
                <p class="text-sm text-amber-900 font-medium">Ton profil est incomplet.</p>
                <p class="text-sm text-amber-800 mt-1">Tu dois renseigner ton statut et uploader ton CV avant de commencer.</p>
                <Link :href="route('onboarding.show')" class="mt-3 inline-flex items-center text-sm font-semibold text-amber-900 underline">
                    Compléter mon profil →
                </Link>
            </div>

            <!-- Tentative en cours -->
            <div v-else-if="attempt_in_progress" class="pt-card p-5 mb-6 border-l-4 border-indigo-400 bg-indigo-50">
                <p class="text-sm text-indigo-900 font-medium">Tu as une tentative en cours sur ce test.</p>
                <p class="text-sm text-indigo-700 mt-1">Reprends là où tu t'es arrêté·e.</p>
            </div>

            <!-- Déjà complété -->
            <div v-else-if="already_attempted" class="pt-card p-5 mb-6 border-l-4 border-emerald-400 bg-emerald-50">
                <p class="text-sm text-emerald-900 font-medium">Tu as déjà complété ce test.</p>
                <p class="text-sm text-emerald-700 mt-1">Tu peux le repasser pour obtenir une nouvelle analyse.</p>
            </div>

            <!-- CTA -->
            <div class="flex flex-col sm:flex-row gap-3">
                <button
                    v-if="profile_complete"
                    @click="start"
                    class="pt-btn-primary flex-1 py-3 text-base"
                >
                    <span v-if="attempt_in_progress">Reprendre le test →</span>
                    <span v-else-if="already_attempted">Repasser le test →</span>
                    <span v-else>Commencer le test →</span>
                </button>

                <Link :href="route('tests.index')" class="pt-btn-ghost flex-1 py-3 text-base text-center">
                    Voir tous les tests
                </Link>
            </div>

        </div>
    </CandidateLayout>
</template>
