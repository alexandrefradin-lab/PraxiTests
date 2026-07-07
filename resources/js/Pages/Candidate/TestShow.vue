<script setup>
import { computed } from 'vue'
import { router, Link, Head } from '@inertiajs/vue3'
import CandidateLayout from '@/Layouts/CandidateLayout.vue'
import { useParcours } from '@/composables/useParcours'

const { L, isCorporate, testLabel, vouvoyer } = useParcours()

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
        <Head :title="testLabel(test)" />

        <div class="max-w-2xl mx-auto px-4 py-6">

            <!-- ── Fil d'Ariane ── -->
            <Link
                :href="route('tests.index')"
                class="inline-flex items-center gap-1.5 text-sm transition-colors mb-8"
                style="color:var(--text-secondary); font-family:'Inter',sans-serif;"
                onmouseover="this.style.color='var(--text-primary)'"
                onmouseout="this.style.color='var(--text-secondary)'"
            >
                ← {{ L.navTests }}
            </Link>

            <!-- ── En-tête ── -->
            <div class="mb-8">
                <!-- Badge type -->
                <span
                    class="inline-block px-2.5 py-0.5 rounded text-[11px] uppercase tracking-widest mb-3"
                    style="font-family:'Space Mono',monospace; color:var(--text-secondary); background:var(--bg-elevated); border:1px solid var(--glass-border);"
                >
                    {{ test.type ?? L.typeFallback }}
                </span>

                <h1
                    class="font-bold tracking-tight leading-tight mt-1 mb-3"
                    style="font-family:var(--font-display); font-size:2rem; color:var(--text-primary);"
                >
                    {{ testLabel(test) }}
                </h1>

                <p
                    v-if="test.description"
                    class="text-base leading-relaxed"
                    style="color:var(--text-secondary); font-family:'Inter',sans-serif;"
                >
                    {{ vouvoyer(test.description) }}
                </p>
            </div>

            <!-- ── Carte infos pratiques ── -->
            <div class="pt-card mb-6 overflow-hidden">
                <div class="grid grid-cols-3 divide-x text-center py-6" style="--tw-divide-opacity:1; border-color:var(--glass-border);">

                    <!-- Minutes -->
                    <div class="px-4">
                        <p
                            class="font-bold leading-none"
                            style="font-family:'Space Mono',monospace; font-size:2rem; color:var(--color-primary);"
                        >
                            {{ test.estimated_minutes }}
                        </p>
                        <p class="text-xs mt-2" style="color:var(--text-secondary); font-family:'Inter',sans-serif;">
                            {{ isCorporate ? 'minutes environ' : "minutes d'Épreuve" }}
                        </p>
                    </div>

                    <!-- Séparateur + Questions -->
                    <div class="px-4" style="border-left:1px solid var(--glass-border);">
                        <p
                            class="font-bold leading-none"
                            style="font-family:'Space Mono',monospace; font-size:2rem; color:var(--color-primary);"
                        >
                            {{ totalQuestions }}
                        </p>
                        <p class="text-xs mt-2" style="color:var(--text-secondary); font-family:'Inter',sans-serif;">
                            Questions
                        </p>
                    </div>

                    <!-- Séparateur + IA -->
                    <div class="px-4" style="border-left:1px solid var(--glass-border);">
                        <p
                            class="font-bold leading-none"
                            style="font-family:'Space Mono',monospace; font-size:2rem; color:var(--color-success);"
                        >
                            IA
                        </p>
                        <p class="text-xs mt-2" style="color:var(--text-secondary); font-family:'Inter',sans-serif;">
                            {{ isCorporate ? 'Synthèse incluse' : 'Grimoire inclus' }}
                        </p>
                    </div>

                </div>
            </div>

            <!-- ── Sections du test ── -->
            <div v-if="test.sections?.length" class="pt-card p-6 mb-6">
                <div class="flex items-center gap-3 mb-4">
                    <span
                        class="text-xs font-bold uppercase tracking-widest"
                        style="font-family:'Space Mono',monospace; color:var(--color-primary);"
                    >Parchemins de l'Épreuve</span>
                    <div class="h-px flex-1" style="background:var(--glass-border);"></div>
                </div>
                <ul class="space-y-3">
                    <li
                        v-for="section in test.sections"
                        :key="section.id"
                        class="flex items-center gap-3"
                    >
                        <i class="ti ti-scroll text-base shrink-0" style="color:var(--color-primary);"></i>
                        <span
                            class="font-semibold flex-1"
                            style="font-family:'Space Grotesk',sans-serif; font-size:13px; color:var(--text-primary);"
                        >
                            {{ section.title }}
                        </span>
                        <span
                            v-if="section.questions?.length"
                            class="text-xs"
                            style="font-family:'Space Mono',monospace; color:var(--text-secondary);"
                        >
                            {{ section.questions.length }} q.
                        </span>
                    </li>
                </ul>
            </div>

            <!-- ── Zone CTA ── -->

            <!-- Déjà complété (et pas en cours) -->
            <template v-if="already_attempted && !attempt_in_progress">
                <div class="pt-card p-6 mb-4 flex items-center gap-3" style="border-color:var(--color-success);">
                    <span
                        class="inline-flex items-center gap-2 text-sm font-semibold"
                        style="color:var(--color-success); font-family:'Space Grotesk',sans-serif;"
                    >
                        <i class="ti ti-circle-check text-lg"></i>
                        Épreuve complétée ✓
                    </span>
                </div>
                <div class="flex flex-col sm:flex-row gap-3">
                    <Link
                        v-if="already_attempted.result_id"
                        :href="route('results.show', already_attempted.result_id)"
                        class="pt-btn-ghost flex-1 py-3 text-sm text-center"
                        style="font-family:'Space Grotesk',sans-serif;"
                    >
                        Voir ma Révélation
                    </Link>
                    <button
                        @click="start"
                        class="pt-btn-primary flex-1 py-3 text-sm font-semibold"
                        style="font-family:'Space Grotesk',sans-serif;"
                    >
                        Repasser l'Épreuve →
                    </button>
                </div>
            </template>

            <!-- Épreuve en cours -->
            <template v-else-if="attempt_in_progress">
                <div
                    class="rounded-xl border-2 p-5 mb-4 flex items-start gap-4"
                    style="background:var(--bg-elevated); border-color:var(--color-primary);"
                >
                    <i class="ti ti-clock-play text-xl mt-0.5 shrink-0" style="color:var(--color-primary);"></i>
                    <div>
                        <p class="text-sm font-semibold mb-0.5" style="color:var(--text-primary); font-family:'Space Grotesk',sans-serif;">
                            Tu as une Épreuve en cours.
                        </p>
                        <p class="text-xs" style="color:var(--text-secondary); font-family:'Inter',sans-serif;">
                            Reprends là où tu t'es arrêté·e — ta progression est sauvegardée.
                        </p>
                    </div>
                </div>
                <button
                    @click="start"
                    class="pt-btn-primary w-full py-3 text-base font-semibold"
                    style="font-family:'Space Grotesk',sans-serif;"
                >
                    Reprendre l'Épreuve →
                </button>
            </template>

            <!-- Démarrage initial -->
            <template v-else>
                <div class="flex flex-col gap-3">
                    <button
                        v-if="profile_complete"
                        @click="start"
                        class="pt-btn-primary w-full py-3 text-base font-semibold"
                        style="font-family:'Space Grotesk',sans-serif;"
                    >
                        Commencer l'Épreuve
                    </button>
                    <Link
                        v-else
                        :href="route('onboarding.show')"
                        class="pt-btn-primary w-full py-3 text-base font-semibold text-center"
                        style="font-family:'Space Grotesk',sans-serif;"
                    >
                        Forger mon Identité pour commencer
                    </Link>
                    <p
                        class="text-center text-xs"
                        style="color:var(--text-secondary); font-family:'Inter',sans-serif;"
                    >
                        Tu peux faire une pause à tout moment.
                    </p>
                </div>
            </template>

        </div>
    </CandidateLayout>
</template>

<style scoped>
.pt-card {
    transition: border-color 0.2s ease;
}
</style>
