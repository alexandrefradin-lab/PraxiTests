<script setup>
import { computed } from 'vue'
import { Link, Head, router, usePage } from '@inertiajs/vue3'
import CandidateLayout from '@/Layouts/CandidateLayout.vue'
import WelcomeModal from '@/Components/WelcomeModal.vue'
import { useParcours } from '@/composables/useParcours'

const { L, isCorporate, testLabel, vouvoyer } = useParcours()

const props = defineProps({
    tests: Array,
    profile_complete: Boolean,
})

const completedCount = computed(() => props.tests.filter(t => t.completed_at || t.completed).length)

// ── KPIs du tableau de bord (parcours Corporate uniquement) ──
const page = usePage()
const progressPct = computed(() => props.tests.length ? Math.round(completedCount.value / props.tests.length * 100) : 0)
const kpiXpTotal  = computed(() => page.props.gamification?.xp_total ?? 0)
const kpiLevel    = computed(() => page.props.gamification?.level ?? 1)
const kpiLevelName = computed(() => page.props.gamification?.level_name ?? `Niveau ${kpiLevel.value}`)

function goToTest(test) {
    if (props.profile_complete) router.visit(route('tests.show', test.slug))
}

// ── Emblèmes médiévaux (line-art) par test, mappés sur le slug ──
const ICONS = {
    // Orientation Express → boussole
    'orientation-express': '<circle cx="12" cy="12" r="9"/><polygon points="12,6.5 14,12 12,17.5 10,12" fill="currentColor" stroke="currentColor"/><path d="M12 2.5V4M12 20v1.5M2.5 12H4M20 12h1.5"/>',
    // PraxiMet — Quête de la Voie (RIASEC) → poteau-panneau (carrefour)
    'praximet-riasec': '<path d="M12 3.5V21"/><path d="M12 6h7l-2 2 2 2h-7"/><path d="M12 12H5l-2 2 2 2h7"/>',
    // PraxiMum — Grande Cartographie (Big Five) → carte dépliée
    'praximum': '<path d="M3 6.5l6-2 6 2 6-2v13l-6 2-6-2-6 2z"/><path d="M9 4.5v13M15 6.5v13"/>',
    // Praxis360 — Constellation des Talents → constellation
    'praxis360': '<path d="M4 8l5 5 6-4 5 4"/><circle cx="4" cy="8" r="1.1" fill="currentColor" stroke="none"/><circle cx="9" cy="13" r="1.1" fill="currentColor" stroke="none"/><circle cx="15" cy="9" r="1.1" fill="currentColor" stroke="none"/><circle cx="20" cy="13" r="1.1" fill="currentColor" stroke="none"/><circle cx="12" cy="4" r="1.1" fill="currentColor" stroke="none"/>',
    // PraxiEmo — Boussole des Émotions → cœur dans une boussole
    'praxiemo': '<circle cx="12" cy="12" r="9"/><path d="M12 16.5c-2.2-1.6-3.8-2.9-3.8-4.6 0-1.2 1-2.1 2.1-2.1.8 0 1.3.4 1.7 1 .4-.6.9-1 1.7-1 1.1 0 2.1.9 2.1 2.1 0 1.7-1.6 3-3.8 4.6z"/>',
    // PraxiCare — Sentinelle Intérieure → écu au cœur
    'praxicare': '<path d="M12 3l7 3v5c0 4.5-3 7.6-7 9-4-1.4-7-4.5-7-9V6z"/><path d="M12 14.5c-1.7-1.2-3-2.2-3-3.5 0-1 .8-1.6 1.6-1.6.6 0 1.1.3 1.4.8.3-.5.8-.8 1.4-.8.8 0 1.6.6 1.6 1.6 0 1.3-1.3 2.3-3 3.5z"/>',
    // PraxiSelf — Forge du Soi → marteau de forgeron
    'praxiself-affirmation': '<path d="M16.5 3.5l4 4-2.2 2.2-4-4z"/><path d="M14 6L4.5 15.5 3 19l3.5-1.5L16 8z"/>',
    // PraxiSpeak — Voix du Héros → porte-voix / héraut
    'praxispeak': '<path d="M4 10.5v3l3 .8 9 4V5.7L7 9.7l-3 .8z"/><path d="M17 9c1.8.3 2.8 1.6 2.8 3s-1 2.7-2.8 3"/>',
    // PraxiFlow — Maître du Temps → sablier
    'praxiflow-productivite': '<path d="M6 3h12M6 21h12"/><path d="M7 3v3c0 2 2 4 5 6 3-2 5-4 5-6V3"/><path d="M7 21v-3c0-2 2-4 5-6 3 2 5 4 5 6v3"/>',
    // PraxiTempo — Maître du Temps (conversationnel) → cadran solaire
    'praxitempo': '<path d="M3.5 20h17"/><path d="M6 20a6 6 0 0 1 12 0"/><path d="M12 20L9.5 9"/><path d="M7.5 16l-1.2-.6M16.5 16l1.2-.6"/>',
    // PraxiValeurs — Source des Valeurs → balance
    'praxivaleurs': '<path d="M12 4v17"/><path d="M7 21h10"/><path d="M5 7l7-1.5L19 7"/><path d="M5 7l-2 5a3 3 0 0 0 4 0z"/><path d="M19 7l-2 5a3 3 0 0 0 4 0z"/>',
    // PraxiZen — Refuge Intérieur → lotus
    'praxizen-stress': '<path d="M12 5c1.6 2.6 1.6 5.4 0 8-1.6-2.6-1.6-5.4 0-8z"/><path d="M12 13C9.8 11.4 7 11.4 4.5 13c1.4 2.4 4 3.4 7.5 3"/><path d="M12 13c2.2-1.6 5-1.6 7.5 0-1.4 2.4-4 3.4-7.5 3"/>',
    // PraxiLink — Art des Liens → maillons de chaîne
    'praxilink-assertivite': '<rect x="3" y="9" width="11" height="6" rx="3"/><rect x="10" y="9" width="11" height="6" rx="3"/>',
    // PraxiBoost → étincelle / éclat
    'praxiboost': '<path d="M12 2.5l1.8 6.7 6.7 1.8-6.7 1.8L12 19.5l-1.8-6.7L3.5 11l6.7-1.8z" fill="currentColor" stroke="currentColor"/>',
    // PraxiFocus — Boussole de l'Attention (TDAH) → cible / mire (focus)
    'praxifocus': '<circle cx="12" cy="12" r="8.5"/><circle cx="12" cy="12" r="4"/><circle cx="12" cy="12" r="1" fill="currentColor" stroke="none"/><path d="M12 1.5V4M12 20v2.5M1.5 12H4M20 12h2.5"/>',
    // PraxiBiais — Cartographe Mental (biais cognitifs) → loupe sur une pensée
    'praxibiais': '<circle cx="10" cy="10" r="6"/><path d="M14.5 14.5L20 20"/><path d="M7.5 10.4c0-1.4 1.1-1.9 2.3-1.1 1.2.8 2.4.3 2.4-1.1"/>',
    // PraxiSens — Radar des Sens (hypersensibilité) → ondes radar
    'praxisens': '<circle cx="12" cy="18" r="1.2" fill="currentColor" stroke="none"/><path d="M8.5 14.5a5 5 0 0 1 7 0"/><path d="M6 12a8.5 8.5 0 0 1 12 0"/><path d="M3.5 9.5a12 12 0 0 1 17 0"/>',
    // L'Étoffe du Bâtisseur — compétences entrepreneuriales → fusée (élan / lancement)
    'competences-entrepreneuriales': '<path d="M12 2.5c2.4 2 3.8 4.8 3.8 7.7 0 2-.8 3.6-1.8 4.8H10c-1-1.2-1.8-2.8-1.8-4.8 0-2.9 1.4-5.7 3.8-7.7z"/><circle cx="12" cy="9" r="1.4"/><path d="M10 15l-2.5 2.5.5 3M14 15l2.5 2.5-.5 3"/><path d="M11 18.5c.4 1 .4 2 1 3 .6-1 .6-2 1-3"/>',
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
        <Head :title="L.titleTests" />
        <WelcomeModal />

        <!-- ── En-tête page ── -->
        <div class="mb-8">
            <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-3">
                <div>
                    <h1
                        class="font-bold tracking-tight leading-none"
                        style="font-family:var(--font-display); color:var(--text-primary); font-size:2.5rem;"
                    >
                        {{ L.titleTests }}
                    </h1>
                    <p class="mt-2 text-sm" style="color:var(--text-secondary); font-family:var(--font-body);">
                        {{ L.subtitleTests }}
                    </p>
                </div>
                <span
                    class="text-sm whitespace-nowrap self-start sm:self-end pb-0.5"
                    style="font-family:'Space Mono',monospace; color:var(--text-secondary);"
                >
                    {{ tests.length }} {{ L.countTests }}
                </span>
            </div>

            <!-- KPIs tableau de bord (parcours Corporate) -->
            <div v-if="isCorporate && tests.length > 0" class="mt-4 tk-kpis">
                <div class="tk-kpi">
                    <div class="tk-kpi-label">Progression</div>
                    <div class="tk-kpi-value">{{ progressPct }}<span class="tk-kpi-unit">%</span></div>
                    <div class="tk-kpi-track"><div :style="{ width: progressPct + '%' }"></div></div>
                </div>
                <div class="tk-kpi">
                    <div class="tk-kpi-label">Évaluations terminées</div>
                    <div class="tk-kpi-value">{{ completedCount }}<span class="tk-kpi-unit">/{{ tests.length }}</span></div>
                </div>
                <div class="tk-kpi">
                    <div class="tk-kpi-label">Points acquis</div>
                    <div class="tk-kpi-value">{{ kpiXpTotal }}</div>
                </div>
                <div class="tk-kpi">
                    <div class="tk-kpi-label">Niveau</div>
                    <div class="tk-kpi-value">{{ kpiLevel }}</div>
                    <div class="tk-kpi-sub">{{ kpiLevelName }}</div>
                </div>
            </div>

            <!-- Barre de progression globale (parcours Médiéval) -->
            <div v-if="!isCorporate && tests.length > 0" class="mt-3" style="display:flex;align-items:center;gap:0.75rem;">
                <div style="flex:1;height:5px;border-radius:99px;background:var(--bg-elevated);overflow:hidden;">
                    <div :style="{ width: progressPct + '%', height:'100%', background:'var(--color-primary)', borderRadius:'99px', transition:'width 0.4s ease' }"></div>
                </div>
                <span style="font-size:0.72rem;font-weight:600;color:var(--text-secondary);flex-shrink:0;white-space:nowrap;">
                    {{ completedCount }}/{{ tests.length }} accomplies
                </span>
            </div>

            <!-- Ligne décorative or (parcours Médiéval) -->
            <div v-if="!isCorporate" class="flex items-center gap-3 mt-5">
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
                <p class="text-sm font-semibold mb-1" style="color:var(--text-primary); font-family:var(--font-display);">
                    {{ isCorporate ? 'Votre profil est incomplet.' : "Ton Identité n'est pas encore forgée." }}
                </p>
                <p class="text-sm" style="color:var(--text-secondary); font-family:'Inter',sans-serif;">
                    {{ isCorporate ? 'Complétez votre profil pour accéder aux évaluations.' : 'Complète ton profil pour débloquer les Épreuves.' }}
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
                v-for="(test, idx) in tests"
                :key="test.id"
                class="pt-card ac-card-ornate p-6 flex flex-col transition-all duration-200 group"
                :style="{ cursor: profile_complete ? 'pointer' : 'default' }"
                :role="profile_complete ? 'link' : null"
                :tabindex="profile_complete ? 0 : null"
                :aria-label="profile_complete ? `${testLabel(test)} — ${L.ctaTest}` : null"
                @click="goToTest(test)"
                @keydown.enter="goToTest(test)"
                @keydown.space.prevent="goToTest(test)"
            >
                <!-- Badge type + emblème + complété (numéro en mode Corporate) -->
                <div class="flex items-start justify-between mb-3 gap-3">
                    <div style="display:flex;align-items:center;gap:0.5rem;flex-wrap:wrap;">
                        <span
                            v-if="isCorporate"
                            class="mt-1"
                            style="font-family:'Space Mono',monospace;font-size:10px;font-weight:700;color:var(--color-primary);letter-spacing:0.08em;"
                        >{{ String(idx + 1).padStart(2, '0') }}</span>
                        <span
                            class="inline-block px-2 py-0.5 rounded text-[10px] uppercase tracking-widest mt-1"
                            style="font-family:'Space Mono',monospace; color:var(--text-secondary); background:var(--bg-elevated);"
                        >
                            {{ test.type ?? L.typeFallback }}
                        </span>
                        <span
                            v-if="test.completed_at || test.completed"
                            class="mt-1"
                            style="font-size:10px;font-weight:700;border-radius:20px;padding:2px 8px;display:inline-flex;align-items:center;gap:3px;color:var(--color-success);background:rgba(var(--color-success-rgb),0.12);"
                        >
                            ✓ {{ L.badgeDone }}
                        </span>
                    </div>
                    <span class="pt-emblem" v-html="emblem(test.slug)"></span>
                </div>

                <!-- Titre -->
                <h3
                    class="font-bold mb-2 leading-snug"
                    style="font-family:var(--font-display); font-size:16px; color:var(--text-primary);"
                >
                    {{ testLabel(test) }}
                </h3>

                <!-- Description (2 lignes max) -->
                <p
                    class="text-[13px] leading-relaxed flex-1 overflow-hidden"
                    style="font-family:'Inter',sans-serif; color:var(--text-secondary); display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical;"
                >
                    {{ vouvoyer(test.description) }}
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
                        {{ L.ctaTest }}
                    </span>
                </div>
            </div>
        </div>

        <!-- ── Liste vide ── -->
        <div v-else class="pt-card p-12 text-center">
            <i class="ti block text-6xl mb-4" :class="L.iconTests" style="color:var(--text-secondary);"></i>
            <p
                class="text-base font-semibold mb-1"
                style="font-family:var(--font-display); color:var(--text-primary);"
            >
                {{ isCorporate ? 'Aucune évaluation disponible pour le moment.' : 'Aucune Épreuve disponible pour le moment.' }}
            </p>
            <p class="text-sm" style="color:var(--text-secondary); font-family:'Inter',sans-serif;">
                {{ isCorporate ? 'Le catalogue sera bientôt disponible. Revenez dans quelques instants.' : "L'Armurerie se remplit bientôt. Reviens dans quelques instants." }}
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
html[data-theme="corporate"] .pt-card:hover {
    box-shadow: 0 4px 10px -4px rgba(21, 34, 56, 0.10), 0 20px 44px -20px rgba(21, 34, 56, 0.28);
}

/* ── KPIs tableau de bord (Corporate) ── */
.tk-kpis {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
    gap: 10px;
}
.tk-kpi {
    background: #FFFFFF;
    border: 1px solid var(--border-light);
    border-radius: var(--r-lg);
    padding: 12px 14px;
    box-shadow: 0 1px 2px rgba(21,34,56,0.03), 0 10px 24px -16px rgba(21,34,56,0.16);
}
.tk-kpi-label {
    font-family: var(--font-body);
    font-size: 10px;
    color: var(--text-muted);
}
.tk-kpi-value {
    font-family: var(--font-display);
    font-size: 20px;
    font-weight: 700;
    letter-spacing: -0.03em;
    color: var(--text-primary);
    margin-top: 3px;
    line-height: 1;
}
.tk-kpi-unit {
    font-size: 12px;
    font-weight: 600;
    color: var(--text-muted);
    margin-left: 1px;
}
.tk-kpi-sub {
    font-family: var(--font-body);
    font-size: 9.5px;
    color: var(--color-primary-dark);
    margin-top: 6px;
}
.tk-kpi-track {
    height: 3px;
    border-radius: 99px;
    background: var(--bg-elevated);
    overflow: hidden;
    margin-top: 8px;
}
.tk-kpi-track > div {
    height: 100%;
    border-radius: 99px;
    background: var(--color-primary);
    transition: width 0.4s ease;
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
/* Focus clavier visible sur les cartes de test navigables */
.ac-card-ornate[role="link"]:focus-visible {
    outline: 2px solid var(--color-primary);
    outline-offset: 2px;
}
</style>
