<script setup>
import { computed } from 'vue'
import { Link, Head, router } from '@inertiajs/vue3'
import CandidateLayout from '@/Layouts/CandidateLayout.vue'
import WelcomeModal from '@/Components/WelcomeModal.vue'

const props = defineProps({
    tests: Array,
    profile_complete: Boolean,
})

const completedCount = computed(() => props.tests.filter(t => t.completed_at || t.completed).length)

function goToTest(test) {
    if (props.profile_complete) router.visit(route('tests.show', test.slug))
}

// ── Emblèmes médiévaux (line-art) par test, mappés sur le slug ──
const ICONS = {
    // Orientation Express → boussole
    'orientation-express': '<circle cx="12" cy="12" r="9"/><polygon points="12,6.5 14,12 12,17.5 10,12" fill="currentColor" stroke="currentColor"/><path d="M12 2.5V4M12 20v1.5M2.5 12H4M20 12h1.5"/>',
    // PraxiMet — Quête de la Voie (RIASEC) → poteau-panneau (carrefour)
    'praximet': '<path d="M12 3.5V21"/><path d="M12 6h7l-2 2 2 2h-7"/><path d="M12 12H5l-2 2 2 2h7"/>',
    // PraxiMum — Grande Cartographie (Big Five) → carte dépliée
    'praximum': '<path d="M3 6.5l6-2 6 2 6-2v13l-6 2-6-2-6 2z"/><path d="M9 4.5v13M15 6.5v13"/>',
    // Praxis360 — Constellation des Talents → constellation
    'praxis360': '<path d="M4 8l5 5 6-4 5 4"/><circle cx="4" cy="8" r="1.1" fill="currentColor" stroke="none"/><circle cx="9" cy="13" r="1.1" fill="currentColor" stroke="none"/><circle cx="15" cy="9" r="1.1" fill="currentColor" stroke="none"/><circle cx="20" cy="13" r="1.1" fill="currentColor" stroke="none"/><circle cx="12" cy="4" r="1.1" fill="currentColor" stroke="none"/>',
    // PraxiEmo — Boussole des Émotions → cœur dans une boussole
    'praxiemo': '<circle cx="12" cy="12" r="9"/><path d="M12 16.5c-2.2-1.6-3.8-2.9-3.8-4.6 0-1.2 1-2.1 2.1-2.1.8 0 1.3.4 1.7 1 .4-.6.9-1 1.7-1 1.1 0 2.1.9 2.1 2.1 0 1.7-1.6 3-3.8 4.6z"/>',
    // PraxiCare — Sentinelle Intérieure → écu au cœur
    'praxicare': '<path d="M12 3l7 3v5c0 4.5-3 7.6-7 9-4-1.4-7-4.5-7-9V6z"/><path d="M12 14.5c-1.7-1.2-3-2.2-3-3.5 0-1 .8-1.6 1.6-1.6.6 0 1.1.3 1.4.8.3-.5.8-.8 1.4-.8.8 0 1.6.6 1.6 1.6 0 1.3-1.3 2.3-3 3.5z"/>',
    // PraxiSelf — Forge du Soi → marteau de forgeron
    'praxiself': '<path d="M16.5 3.5l4 4-2.2 2.2-4-4z"/><path d="M14 6L4.5 15.5 3 19l3.5-1.5L16 8z"/>',
    // PraxiSpeak — Voix du Héros → porte-voix / héraut
    'praxispeak': '<path d="M4 10.5v3l3 .8 9 4V5.7L7 9.7l-3 .8z"/><path d="M17 9c1.8.3 2.8 1.6 2.8 3s-1 2.7-2.8 3"/>',
    // PraxiFlow — Maître du Temps → sablier
    'praxiflow': '<path d="M6 3h12M6 21h12"/><path d="M7 3v3c0 2 2 4 5 6 3-2 5-4 5-6V3"/><path d="M7 21v-3c0-2 2-4 5-6 3 2 5 4 5 6v3"/>',
    // PraxiTempo — Maître du Temps (conversationnel) → cadran solaire
    'praxitempo': '<path d="M3.5 20h17"/><path d="M6 20a6 6 0 0 1 12 0"/><path d="M12 20L9.5 9"/><path d="M7.5 16l-1.2-.6M16.5 16l1.2-.6"/>',
    // PraxiValeurs — Source des Valeurs → balance
    'praxivaleurs': '<path d="M12 4v17"/><path d="M7 21h10"/><path d="M5 7l7-1.5L19 7"/><path d="M5 7l-2 5a3 3 0 0 0 4 0z"/><path d="M19 7l-2 5a3 3 0 0 0 4 0z"/>',
    // PraxiZen — Refuge Intérieur → lotus
    'praxizen': '<path d="M12 5c1.6 2.6 1.6 5.4 0 8-1.6-2.6-1.6-5.4 0-8z"/><path d="M12 13C9.8 11.4 7 11.4 4.5 13c1.4 2.4 4 3.4 7.5 3"/><path d="M12 13c2.2-1.6 5-1.6 7.5 0-1.4 2.4-4 3.4-7.5 3"/>',
    // PraxiLink — Art des Liens → maillons de chaîne
    'praxilink': '<rect x="3" y="9" width="11" height="6" rx="3"/><rect x="10" y="9" width="11" height="6" rx="3"/>',
    // PraxiBoost → étincelle / éclat
    'praxiboost': '<path d="M12 2.5l1.8 6.7 6.7 1.8-6.7 1.8L12 19.5l-1.8-6.7L3.5 11l6.7-1.8z" fill="currentColor" stroke="currentColor"/>',
}
// Fallback : écu
const DEFAULT_ICON = '<path d="M12 3l7 3v5c0 4.5-3 7.6-7 9-4-1.4-7-4.5-7-9V6z"/>'

function emblem(slug) {
    const inner = ICONS[slug] ?? DEFAULT_ICON
    return `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">${inner}</svg>`
}
</script>

<template>
    <CandidateLayout>
        <Head title="L'Armurerie — Épreuves" />
        <WelcomeModal />

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

            <!-- Barre de progression globale -->
            <div v-if="tests.length > 0" class="mt-3" style="display:flex;align-items:center;gap:0.75rem;">
                <div style="flex:1;height:5px;border-radius:99px;background:var(--bg-elevated);overflow:hidden;">
                    <div :style="{ width: Math.round(completedCount / tests.length * 100) + '%', height:'100%', background:'var(--color-primary)', borderRadius:'99px', transition:'width 0.4s ease' }"></div>
                </div>
                <span style="font-size:0.72rem;font-weight:600;color:var(--text-secondary);flex-shrink:0;white-space:nowrap;">
                    {{ completedCount }}/{{ tests.length }} accomplies
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
                :style="{ cursor: profile_complete ? 'pointer' : 'default' }"
                @click="goToTest(test)"
            >
                <!-- Badge type + emblème médiéval + complété -->
                <div class="flex items-start justify-between mb-3 gap-3">
                    <div style="display:flex;align-items:center;gap:0.5rem;flex-wrap:wrap;">
                        <span
                            class="inline-block px-2 py-0.5 rounded text-[10px] uppercase tracking-widest mt-1"
                            style="font-family:'Space Mono',monospace; color:var(--text-secondary); background:var(--bg-elevated);"
                        >
                            {{ test.type ?? 'Épreuve' }}
                        </span>
                        <span
                            v-if="test.completed_at || test.completed"
                            class="mt-1"
                            style="font-size:10px;font-weight:700;color:#10B981;background:#D1FAE5;border-radius:20px;padding:2px 8px;display:inline-flex;align-items:center;gap:3px;"
                        >
                            ✓ Accomplie
                        </span>
                    </div>
                    <span class="pt-emblem" v-html="emblem(test.slug)"></span>
                </div>

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
                    <span
                        class="pt-btn-primary text-xs px-4 py-2"
                        :class="{ 'opacity-40': !profile_complete }"
                        style="pointer-events:none;"
                    >
                        Entrer dans l'Épreuve →
                    </span>
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
    transition: box-shadow 0.2s ease, border-color 0.2s ease, transform 0.2s ease;
}
.pt-card:hover {
    border-color: var(--color-primary) !important;
    box-shadow: 0 8px 28px rgba(166, 117, 32, 0.16);
    transform: translateY(-3px);
}
.pt-emblem {
    width: 44px;
    height: 44px;
    flex: none;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    color: var(--color-primary);
    background: var(--bg-elevated);
    border: 1px solid var(--glass-border);
    transition: border-color 0.2s ease, transform 0.2s ease;
}
.pt-emblem :deep(svg) {
    width: 24px;
    height: 24px;
}
.group:hover .pt-emblem {
    border-color: var(--color-primary);
    transform: rotate(-4deg) scale(1.05);
}
</style>
