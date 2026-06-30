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
        <div v-if="tests.length > 0" class="parch-grid">
            <div
                v-for="test in tests"
                :key="test.id"
                class="parch-card"
                :class="{ 'parch-card--done': test.completed_at || test.completed, 'parch-card--locked': !profile_complete }"
                @click="goToTest(test)"
            >
                <!-- ── Emblème grand format ── -->
                <div class="parch-emblem-wrap">
                    <div class="parch-emblem" v-html="emblem(test.slug)"></div>
                    <!-- Halo derrière l'emblème si accomplie -->
                    <div v-if="test.completed_at || test.completed" class="parch-halo"></div>
                </div>

                <!-- ── Corps ── -->
                <div class="parch-body">
                    <!-- Badges -->
                    <div class="parch-badges">
                        <span class="parch-tag">{{ test.type ?? 'Épreuve' }}</span>
                        <span v-if="test.completed_at || test.completed" class="parch-done-badge">✓ Accomplie</span>
                    </div>

                    <!-- Titre -->
                    <h3 class="parch-title">{{ test.name }}</h3>

                    <!-- Description -->
                    <p class="parch-desc">{{ test.description }}</p>
                </div>

                <!-- ── Pied de carte ── -->
                <div class="parch-footer">
                    <span class="parch-time">≈ {{ test.estimated_minutes }} min</span>
                    <!-- Carte accomplie : score si dispo, sinon badge or -->
                    <div v-if="test.completed_at || test.completed" class="parch-score-ring">
                        <span v-if="test.score != null" class="parch-score-val">{{ test.score }}<small>%</small></span>
                        <span v-else class="parch-score-check">✓</span>
                    </div>
                    <!-- Carte disponible -->
                    <span v-else class="parch-cta" :class="{ 'parch-cta--off': !profile_complete }">
                        Entrer dans l'Épreuve →
                    </span>
                </div>

                <!-- Trait doré bas -->
                <div class="parch-bar"></div>
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
/* ═══════════════════════════════════
   GRILLE PARCHEMIN
═══════════════════════════════════ */
.parch-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 1.25rem;
}
@media (min-width: 768px) {
    .parch-grid { grid-template-columns: repeat(2, 1fr); }
}

/* ── Carte ── */
.parch-card {
    position: relative;
    display: flex;
    flex-direction: column;
    background: linear-gradient(145deg, #FDFBF4 0%, #F7EDCF 60%, #F2E5BA 100%);
    border: 1px solid rgba(180, 130, 20, 0.22);
    border-radius: 14px;
    padding: 1.4rem 1.4rem 1rem;
    cursor: pointer;
    transition: box-shadow 0.22s ease, border-color 0.22s ease, transform 0.22s ease;
    overflow: hidden;
    /* Texture papier subtile via pseudo */
}
.parch-card::before {
    content: '';
    position: absolute;
    inset: 0;
    border-radius: 14px;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='300' height='300'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.65' numOctaves='3' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='300' height='300' filter='url(%23n)' opacity='0.045'/%3E%3C/svg%3E");
    pointer-events: none;
    opacity: 0.6;
}
.parch-card:hover {
    transform: translateY(-4px);
    border-color: rgba(196, 134, 10, 0.55);
    box-shadow: 0 10px 32px rgba(150, 100, 10, 0.18), 0 2px 8px rgba(150, 100, 10, 0.1);
}
.parch-card--done {
    background: linear-gradient(145deg, #FDFAF0 0%, #F5E8B8 55%, #EDD98A 100%);
    border-color: rgba(196, 134, 10, 0.38);
}
.parch-card--done:hover {
    border-color: rgba(196, 134, 10, 0.7);
    box-shadow: 0 10px 32px rgba(150, 100, 10, 0.22), 0 0 0 2px rgba(196,134,10,0.12);
}
.parch-card--locked {
    cursor: default;
    opacity: 0.82;
}
.parch-card--locked:hover {
    transform: none;
    box-shadow: none;
    border-color: rgba(180, 130, 20, 0.22);
}

/* ── Emblème ── */
.parch-emblem-wrap {
    position: relative;
    width: 56px;
    height: 56px;
    margin-bottom: 1rem;
    flex-shrink: 0;
}
.parch-halo {
    position: absolute;
    inset: -6px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(196,134,10,0.22) 0%, transparent 70%);
    animation: halo-pulse 3s ease-in-out infinite;
}
@keyframes halo-pulse {
    0%, 100% { opacity: 0.6; transform: scale(1); }
    50%       { opacity: 1;   transform: scale(1.12); }
}
.parch-emblem {
    position: relative;
    z-index: 1;
    width: 56px;
    height: 56px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    background: rgba(196, 134, 10, 0.10);
    border: 1.5px solid rgba(196, 134, 10, 0.30);
    color: #A36808;
    transition: transform 0.22s ease, background 0.22s ease;
}
.parch-card:hover .parch-emblem {
    background: rgba(196, 134, 10, 0.16);
    transform: rotate(-5deg) scale(1.08);
}
.parch-emblem :deep(svg) {
    width: 28px;
    height: 28px;
}

/* ── Corps ── */
.parch-body {
    flex: 1;
    display: flex;
    flex-direction: column;
}
.parch-badges {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 0.4rem;
    margin-bottom: 0.55rem;
}
.parch-tag {
    font-family: 'Space Mono', monospace;
    font-size: 9px;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    color: #8B6914;
    background: rgba(196, 134, 10, 0.10);
    border: 1px solid rgba(196, 134, 10, 0.20);
    border-radius: 4px;
    padding: 2px 7px;
}
.parch-done-badge {
    font-family: 'Space Mono', monospace;
    font-size: 9px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    color: #166534;
    background: #DCFCE7;
    border-radius: 20px;
    padding: 2px 8px;
    display: inline-flex;
    align-items: center;
}
.parch-title {
    font-family: 'Space Grotesk', sans-serif;
    font-size: 18px;
    font-weight: 700;
    line-height: 1.25;
    color: #3D2A00;
    margin-bottom: 0.5rem;
}
.parch-desc {
    font-family: 'Inter', sans-serif;
    font-size: 13px;
    line-height: 1.65;
    color: #7A6030;
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
    flex: 1;
}

/* ── Pied de carte ── */
.parch-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-top: 1.1rem;
    padding-top: 0.85rem;
    border-top: 1px solid rgba(196, 134, 10, 0.18);
}
.parch-time {
    font-family: 'Space Mono', monospace;
    font-size: 10px;
    color: #9B7A2E;
}
/* Score anneau circulaire */
.parch-score-ring {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: radial-gradient(circle at 40% 35%, #FFF8E1, #E8C44A);
    border: 2px solid rgba(196, 134, 10, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 2px 8px rgba(196, 134, 10, 0.25);
    flex-shrink: 0;
}
.parch-score-val {
    font-family: 'Space Mono', monospace;
    font-size: 11px;
    font-weight: 700;
    color: #5A3E00;
    line-height: 1;
}
.parch-score-val small {
    font-size: 7px;
    vertical-align: super;
}
.parch-score-check {
    font-size: 16px;
    color: #8B6914;
    font-weight: 900;
}
/* CTA */
.parch-cta {
    font-family: 'Space Grotesk', sans-serif;
    font-size: 11px;
    font-weight: 700;
    color: #7A5200;
    background: linear-gradient(135deg, rgba(196,134,10,0.15), rgba(196,134,10,0.08));
    border: 1px solid rgba(196,134,10,0.3);
    border-radius: 20px;
    padding: 5px 12px;
    transition: background 0.18s ease, border-color 0.18s ease;
    white-space: nowrap;
}
.parch-card:hover .parch-cta {
    background: linear-gradient(135deg, rgba(196,134,10,0.28), rgba(196,134,10,0.16));
    border-color: rgba(196,134,10,0.55);
}
.parch-cta--off {
    opacity: 0.4;
}
/* Trait doré bas de carte */
.parch-bar {
    position: absolute;
    bottom: 0;
    left: 10%;
    right: 10%;
    height: 2px;
    background: linear-gradient(to right, transparent, rgba(196,134,10,0.5), transparent);
    border-radius: 0 0 2px 2px;
    transition: opacity 0.22s ease;
    opacity: 0;
}
.parch-card:hover .parch-bar {
    opacity: 1;
}
.parch-card--done .parch-bar {
    opacity: 0.6;
}
</style>
