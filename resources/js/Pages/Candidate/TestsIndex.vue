<script setup>
import { Link, Head } from '@inertiajs/vue3'
import CandidateLayout from '@/Layouts/CandidateLayout.vue'

defineProps({
    tests: Array,
    profile_complete: Boolean,
})
</script>

<template>
    <CandidateLayout>
        <Head title="L'Armurerie — Épreuves" />

        <!-- ── En-tête page ── -->
        <div class="mb-8">
            <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-3">
                <div>
                    <h1
                        class="font-bold tracking-tight leading-none"
                        style="font-family:'Space Grotesk',sans-serif; color:var(--text-primary); font-size:2.5rem;"
                    >
                        L'Armurerie
                    </h1>
                    <p class="mt-2 text-sm" style="color:var(--text-secondary); font-family:'Inter',sans-serif;">
                        Chaque Épreuve est une étape de ta cartographie intérieure.
                    </p>
                </div>
                <span
                    class="text-sm whitespace-nowrap self-start sm:self-end pb-0.5"
                    style="font-family:'Space Mono',monospace; color:var(--text-secondary);"
                >
                    {{ tests.length }} Épreuves disponibles
                </span>
            </div>

            <!-- Ligne décorative or -->
            <div class="flex items-center gap-3 mt-5">
                <div class="h-px flex-1" style="background:linear-gradient(to right, var(--color-primary), transparent);"></div>
                <svg width="12" height="12" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M8 0L9.6 6.4L16 8L9.6 9.6L8 16L6.4 9.6L0 8L6.4 6.4L8 0Z" fill="var(--color-primary)" opacity="0.5"/>
                </svg>
                <div class="h-px flex-1" style="background:linear-gradient(to left, var(--color-primary), transparent);"></div>
            </div>
        </div>

        <!-- ── Alerte profil incomplet ── -->
        <div
            v-if="!profile_complete"
            class="rounded-xl border-2 p-5 mb-8 flex items-start gap-4"
            style="background:var(--bg-elevated); border-color:var(--color-primary);"
        >
            <i class="ti ti-alert-triangle text-xl mt-0.5 shrink-0" style="color:var(--color-primary);"></i>
            <div>
                <p class="text-sm font-semibold mb-1" style="color:var(--text-primary); font-family:'Space Grotesk',sans-serif;">
                    Ton Identité n'est pas encore forgée.
                </p>
                <p class="text-sm" style="color:var(--text-secondary); font-family:'Inter',sans-serif;">
                    Complete ton profil pour débloquer les Épreuves.
                </p>
                <Link
                    :href="route('onboarding.show')"
                    class="inline-flex items-center gap-1 mt-2 text-sm font-semibold transition-opacity hover:opacity-70"
                    style="color:var(--color-primary); font-family:'Inter',sans-serif; text-decoration:underline; text-underline-offset:3px;"
                >
                    → La compléter maintenant
                </Link>
            </div>
        </div>

        <!-- ── Grille des tests ── -->
        <div v-if="tests.length > 0" class="grid md:grid-cols-2 gap-4">
            <div
                v-for="test in tests"
                :key="test.id"
                class="pt-card p-6 flex flex-col transition-all duration-200 group"
                style="cursor:default;"
                :style="{
                    '--hover-border': 'var(--color-primary)',
                }"
            >
                <!-- Badge type -->
                <span
                    class="inline-block self-start px-2 py-0.5 rounded text-[10px] uppercase tracking-widest mb-3"
                    style="font-family:'Space Mono',monospace; color:var(--text-secondary); background:var(--bg-elevated);"
                >
                    {{ test.type ?? 'Épreuve' }}
                </span>

                <!-- Titre -->
                <h3
                    class="font-bold mb-2 leading-snug"
                    style="font-family:'Space Grotesk',sans-serif; font-size:16px; color:var(--text-primary);"
                >
                    {{ test.name }}
                </h3>

                <!-- Description (2 lignes max) -->
                <p
                    class="text-[13px] leading-relaxed flex-1 overflow-hidden"
                    style="font-family:'Inter',sans-serif; color:var(--text-secondary); display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical;"
                >
                    {{ test.description }}
                </p>

                <!-- Footer -->
                <div class="flex items-center justify-between mt-5 pt-4" style="border-top:1px solid var(--glass-border);">
                    <span
                        class="text-xs"
                        style="font-family:'Space Mono',monospace; color:var(--text-secondary);"
                    >
                        ≈ {{ test.estimated_minutes }} min
                    </span>
                    <Link
                        :href="route('tests.show', test.slug)"
                        class="pt-btn-primary text-xs px-4 py-2"
                        :class="{ 'pointer-events-none opacity-40': !profile_complete }"
                    >
                        Entrer dans l'Épreuve →
                    </Link>
                </div>
            </div>
        </div>

        <!-- ── Liste vide ── -->
        <div v-else class="pt-card p-12 text-center">
            <i class="ti ti-sword block text-6xl mb-4" style="color:var(--text-secondary);"></i>
            <p
                class="text-base font-semibold mb-1"
                style="font-family:'Space Grotesk',sans-serif; color:var(--text-primary);"
            >
                Aucune Épreuve disponible pour le moment.
            </p>
            <p class="text-sm" style="color:var(--text-secondary); font-family:'Inter',sans-serif;">
                L'Armurerie se remplit bientôt. Reviens dans quelques instants.
            </p>
        </div>

    </CandidateLayout>
</template>

<style scoped>
.pt-card {
    transition: box-shadow 0.2s ease, border-color 0.2s ease;
}
.pt-card:hover {
    border-color: var(--color-primary) !important;
    box-shadow: 0 4px 20px rgba(166, 117, 32, 0.12);
}
</style>
