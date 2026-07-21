<script setup>
import { computed, ref } from 'vue'
import { Link, Head, router } from '@inertiajs/vue3'
import CandidateLayout from '@/Layouts/CandidateLayout.vue'
import { useParcours } from '@/composables/useParcours'

const { L, isCorporate, testLabel, vouvoyer } = useParcours()

const props = defineProps({
    treasure: {
        type: Object,
        default: () => ({
            total: 0, spent: 0, available: 0,
            choice_enabled: false,
            unlocked_count: 0, total_count: 0, has_profile: false, items: [],
        }),
    },
    profile_complete: { type: Boolean, default: false },
    badges: {
        type: Object,
        default: () => ({ items: [], earned_count: 0, total_count: 0 }),
    },
})

// ── Distinctions ─────────────────────────────────────────────────────────
// Les badges secrets non obtenus arrivent déjà masqués du serveur (name
// « ??? ») : rien à cacher ici, le front n'en sait pas plus que l'affichage.
const badgeItems = computed(() => props.badges?.items ?? [])
const badgesLabel = computed(() => isCorporate.value ? 'Distinctions' : 'Hauts faits')

const recommendedCount = computed(() =>
    props.treasure.items?.filter(i => i.recommended).length ?? 0
)

const unlockedPct = computed(() => {
    const total = props.treasure.total_count || 0
    return total > 0 ? Math.round((props.treasure.unlocked_count / total) * 100) : 0
})

// Interrupteur serveur (PRAXIQUEST_TREASURE_CHOICE_ENABLED). Off = régime
// historique : déblocage automatique au palier, aucun achat, aucune porte.
const choiceEnabled = computed(() => props.treasure.choice_enabled === true)
// Déblocage en cours : neutralise le bouton pour que le double-clic ne parte
// pas deux fois (le service est déjà idempotent côté serveur, c'est la ceinture).
const unlocking = ref(null)

function unlock(item) {
    if (unlocking.value || !item.affordable) return

    unlocking.value = item.plugin_slug

    router.post(route('treasure.unlock', item.plugin_slug), {}, {
        preserveScroll: true,
        onFinish: () => { unlocking.value = null },
    })
}
</script>

<template>
    <CandidateLayout>
        <Head :title="L.titleTreasure" />

        <!-- ── En-tête page ── -->
        <div class="mb-8">
            <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-3">
                <div>
                    <h1
                        class="font-bold tracking-tight leading-none"
                        style="font-family:var(--font-display); color:var(--text-primary); font-size:2.5rem;"
                    >
                        {{ L.titleTreasure }}
                    </h1>
                    <p class="mt-2 text-sm" style="color:var(--text-secondary); font-family:var(--font-body);">
                        {{ L.subtitleTreasure }}
                    </p>
                </div>
                <span
                    class="text-sm whitespace-nowrap self-start sm:self-end pb-0.5"
                    style="font-family:'Space Mono',monospace; color:var(--text-secondary);"
                >
                    {{ treasure.unlocked_count }}/{{ treasure.total_count }} {{ L.countTreasure }}
                </span>
            </div>

            <!-- Barre de progression globale -->
            <div v-if="treasure.total_count > 0" class="mt-3" style="display:flex;align-items:center;gap:0.75rem;">
                <div style="flex:1;height:5px;border-radius:99px;background:var(--bg-elevated);overflow:hidden;">
                    <div :style="{ width: unlockedPct + '%', height:'100%', background:'var(--color-primary)', borderRadius:'99px', transition:'width 0.4s ease' }"></div>
                </div>
                <span style="font-size:0.72rem;font-weight:600;color:var(--text-secondary);flex-shrink:0;white-space:nowrap;">
                    {{ treasure.unlocked_count }}/{{ treasure.total_count }} {{ L.countTreasureShort }}
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

        <!-- ── Portefeuille d'Éclats (régime « choix ») ── -->
        <div v-if="choiceEnabled" class="trs-eclats mb-8">
            <i class="ti ti-diamond text-xl shrink-0" style="color:var(--color-primary);"></i>
            <p class="text-sm" style="color:var(--text-secondary); font-family:'Inter',sans-serif; margin:0;">
                {{ isCorporate ? 'Vous disposez de' : 'Tu disposes de' }}
                <strong style="font-family:'Space Mono',monospace; color:var(--text-primary); font-weight:700;">{{ treasure.available }} {{ L.xpName }}</strong>
                à dépenser<template v-if="treasure.spent > 0"> ({{ treasure.total }} gagnés, {{ treasure.spent }} déjà investis)</template>.
                {{ isCorporate ? 'Choisissez le module que vous souhaitez débloquer.' : "Choisis le trésor que tu veux ouvrir." }}
            </p>
        </div>

        <!-- ── Éclats détenus (régime historique) ── -->
        <div v-else class="trs-eclats mb-8">
            <i class="ti ti-diamond text-xl shrink-0" style="color:var(--color-primary);"></i>
            <p class="text-sm" style="color:var(--text-secondary); font-family:'Inter',sans-serif; margin:0;">
                {{ isCorporate ? 'Vous détenez' : 'Tu détiens' }}
                <strong style="font-family:'Space Mono',monospace; color:var(--text-primary); font-weight:700;">{{ treasure.total }} {{ L.xpName }}</strong>.
                {{ isCorporate ? 'Poursuivez vos évaluations pour en accumuler et débloquer la suite.' : 'Continue tes Épreuves pour en accumuler et débloquer la suite.' }}
            </p>
        </div>

        <!-- ── Bandeau recommandations personnalisées ── -->
        <div v-if="treasure.has_profile && recommendedCount > 0" class="trs-reco mb-8">
            <i class="ti ti-sparkles text-lg shrink-0" style="color:var(--color-primary);"></i>
            <p class="text-sm leading-relaxed" style="color:var(--text-secondary); font-family:'Inter',sans-serif; margin:0;">
                D'après ton Grimoire,
                <strong style="color:var(--text-primary);">{{ recommendedCount }} module{{ recommendedCount > 1 ? 's correspondent' : ' correspond' }} à ton profil</strong>
                — ils apparaissent en premier, signalés par une étoile.
            </p>
        </div>

        <div v-else-if="!treasure.has_profile" class="trs-reco trs-reco--muted mb-8">
            <i class="ti ti-wand text-lg shrink-0" style="color:var(--text-muted);"></i>
            <p class="text-sm leading-relaxed" style="color:var(--text-secondary); font-family:'Inter',sans-serif; margin:0;">
                Passe davantage d'épreuves pour que ton Grimoire se forge —
                <strong style="color:var(--text-primary);">les modules te seront ensuite recommandés selon ton profil</strong>.
            </p>
        </div>

        <!-- ── Explication fonctionnement mini-apps ── -->
        <div class="trs-explainer mb-8">
            <i class="ti ti-info-circle text-lg shrink-0" style="color:var(--text-muted); margin-top:1px;"></i>
            <p class="text-sm leading-relaxed" style="color:var(--text-secondary); font-family:'Inter',sans-serif; margin:0;">
                {{ isCorporate ? 'Chaque module est une' : 'Chaque trésor est une' }} <strong style="color:var(--text-primary);">mini-application indépendante</strong> :
                un parcours de pratiques guidées à raison d'<strong style="color:var(--text-primary);">une par jour</strong>.
                {{ isCorporate
                    ? `Vous avancez à votre rythme, vous gagnez des ${L.xpName.toLowerCase()} à chaque pratique accomplie,`
                    : 'Tu avances à ton rythme, tu gagnes des Éclats à chaque pratique accomplie,' }}
                {{ isCorporate ? "et vous conservez l'accès au module" : "et tu conserves l'accès au module" }} <strong style="color:var(--text-primary);">pour toujours</strong> {{ isCorporate ? 'une fois débloqué.' : 'une fois révélé.' }}
                <template v-if="choiceEnabled">{{ isCorporate
                    ? 'Chaque déblocage vous coûte des points : à vous de choisir dans quel ordre les investir.'
                    : "Chaque ouverture te coûte des Éclats : à toi de choisir dans quel ordre les dépenser." }}</template>
            </p>
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
                    Complète ton profil pour ouvrir les trésors déjà révélés.
                </p>
                <Link
                    :href="route('onboarding.show')"
                    class="inline-flex items-center gap-1 mt-2 text-sm font-semibold transition-opacity hover:opacity-70"
                    style="color:var(--color-primary); font-family:'Inter',sans-serif; text-decoration:underline; text-underline-offset:3px;"
                >
                    &#x2192; La compléter maintenant
                </Link>
            </div>
        </div>

        <!-- ── Grille des trésors ── -->
        <div v-if="treasure.items.length > 0" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <article
                v-for="item in treasure.items"
                :key="item.plugin_slug"
                class="pt-card trs-card p-6 flex flex-col group"
                :class="{
                    'trs-card--locked': !item.unlocked,
                    'trs-card--recommended': item.recommended,
                }"
            >
                <!-- Badge recommandé (sur toute la largeur, en haut) -->
                <div
                    v-if="item.recommended"
                    class="trs-reco-banner mb-3 -mx-6 -mt-6 px-4 py-2 flex items-center gap-2"
                >
                    <i class="ti ti-star-filled text-xs"></i>
                    <span>Recommandé pour ton profil</span>
                </div>

                <!-- Badge statut + emblème -->
                <div class="flex items-start justify-between mb-3 gap-3" :class="{ 'mt-0': item.recommended }">
                    <span
                        v-if="item.unlocked"
                        class="trs-badge mt-1"
                        style="color:var(--color-primary); background:rgba(166,117,32,0.10); border:1px solid rgba(166,117,32,0.35);"
                    >
                        <i class="ti ti-check"></i> Débloqué
                    </span>
                    <span
                        v-else
                        class="trs-badge mt-1"
                        style="color:var(--text-muted); background:rgba(140,122,94,0.10); border:1px solid rgba(140,122,94,0.25);"
                    >
                        <i class="ti ti-lock"></i> Verrouillé
                    </span>

                    <span class="trs-emblem" :class="{ 'trs-emblem--locked': !item.unlocked }">
                        <i class="ti text-2xl" :class="item.unlocked ? (item.icon || 'ti-gift') : 'ti-lock'"></i>
                    </span>
                </div>

                <!-- Titre + purpose -->
                <h3
                    class="font-bold mb-1 leading-snug"
                    style="font-family:'Space Grotesk',sans-serif; font-size:16px; color:var(--text-primary);"
                >
                    {{ testLabel(item) }}
                </h3>
                <p
                    v-if="item.purpose"
                    class="mb-2"
                    style="font-family:'Space Mono',monospace; font-size:10px; text-transform:uppercase; letter-spacing:0.1em; color:var(--color-secondary);"
                >
                    {{ item.purpose }}
                </p>

                <!-- Description / teaser (texte complet, jamais tronqué) -->
                <p
                    class="text-[13px] leading-relaxed flex-1"
                    style="font-family:'Inter',sans-serif; color:var(--text-secondary);"
                >
                    {{ vouvoyer(item.unlocked ? item.description : item.teaser) }}
                </p>

                <!-- Raison du matching (si recommandé) -->
                <p
                    v-if="item.recommended && item.match_reason"
                    class="mt-2 text-[12px] leading-snug"
                    style="font-family:'Inter',sans-serif; color:var(--color-primary-dark); font-style:italic;"
                >
                    <i class="ti ti-sparkles not-italic" style="font-style:normal;"></i>
                    {{ item.match_reason }}
                </p>

                <!-- Coût + ouverture (verrouillé) -->
                <div v-if="!item.unlocked" class="mt-4">
                    <div class="flex justify-between mb-1.5" style="font-family:'Space Mono',monospace; font-size:11px; color:var(--text-secondary);">
                        <span>{{ item.progress_pct }}%</span>
                        <span>{{ item.cost }} {{ L.xpName }}</span>
                    </div>
                    <div style="height:6px;background:rgba(140,122,94,0.2);border-radius:999px;overflow:hidden;">
                        <div :style="{ width: item.progress_pct + '%', height:'100%', background:'var(--color-primary)', borderRadius:'999px', transition:'width 0.4s ease' }"></div>
                    </div>

                    <!-- Solde insuffisant -->
                    <p
                        v-if="!item.affordable"
                        class="mt-2"
                        style="font-family:'Inter',sans-serif; font-size:0.8rem; font-weight:600; color:var(--color-primary-dark);"
                    >
                        <i class="ti ti-lock"></i>
                        {{ isCorporate ? 'Encore' : 'Encore' }} {{ item.missing }} {{ L.xpName }}
                        {{ isCorporate ? 'pour le débloquer' : 'pour le révéler' }}
                    </p>

                    <!-- Porte ouverte, solde suffisant : le choix appartient au candidat -->
                    <button
                        v-else
                        type="button"
                        class="pt-btn-primary text-xs px-4 py-2 mt-3 w-full justify-center"
                        :class="{ 'opacity-40 pointer-events-none': unlocking === item.plugin_slug || !profile_complete }"
                        :disabled="unlocking === item.plugin_slug || !profile_complete"
                        @click="unlock(item)"
                    >
                        <template v-if="unlocking === item.plugin_slug">
                            {{ isCorporate ? 'Déblocage…' : 'Ouverture…' }}
                        </template>
                        <template v-else>
                            <i class="ti ti-key"></i>
                            {{ isCorporate ? 'Débloquer pour' : 'Ouvrir pour' }} {{ item.cost }} {{ L.xpName }}
                        </template>
                    </button>
                </div>

                <!-- Footer (débloqué) -->
                <div
                    v-else
                    class="flex items-center justify-between mt-5 pt-4 gap-3"
                    style="border-top:1px solid var(--glass-border);"
                >
                    <span style="font-family:'Space Mono',monospace; font-size:11px; color:var(--text-muted); flex-shrink:0;">
                        <template v-if="item.estimated_minutes">&#x2248; {{ item.estimated_minutes }} min</template>
                        <template v-else>Module offert</template>
                    </span>
                    <Link
                        v-if="item.url"
                        :href="item.url"
                        class="pt-btn-primary text-xs px-4 py-2"
                        :class="{ 'opacity-40 pointer-events-none': !profile_complete }"
                    >
                        {{ isCorporate ? "Ouvrir l'application" : 'Ouvrir le trésor' }} &#x2192;
                    </Link>
                </div>
            </article>
        </div>

        <!-- ── Liste vide ── -->
        <div v-else class="pt-card p-12 text-center">
            <i class="ti ti-diamond block text-6xl mb-4" style="color:var(--text-secondary);"></i>
            <p
                class="text-base font-semibold mb-1"
                style="font-family:'Space Grotesk',sans-serif; color:var(--text-primary);"
            >
                La Salle du Trésor est encore scellée.
            </p>
            <p class="text-sm" style="color:var(--text-secondary); font-family:'Inter',sans-serif;">
                Aucun trésor n'est disponible pour le moment. Reviens après quelques Épreuves.
            </p>
        </div>

        <!-- ── Distinctions / Hauts faits ────────────────────────────────── -->
        <section v-if="badgeItems.length" class="mt-12">
            <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-2">
                <div>
                    <h2
                        class="font-bold tracking-tight leading-none"
                        style="font-family:var(--font-display); color:var(--text-primary); font-size:1.6rem;"
                    >
                        {{ badgesLabel }}
                    </h2>
                    <p class="mt-2 text-sm" style="color:var(--text-secondary); font-family:var(--font-body);">
                        {{ isCorporate
                            ? 'Les distinctions obtenues au fil de votre parcours.'
                            : 'Ce que tu as accompli en chemin — et ce qu’il te reste à découvrir.' }}
                    </p>
                </div>
                <span
                    class="text-sm whitespace-nowrap self-start sm:self-end pb-0.5"
                    style="font-family:'Space Mono',monospace; color:var(--text-secondary);"
                >
                    {{ badges.earned_count }}/{{ badges.total_count }} {{ isCorporate ? 'obtenues' : 'obtenus' }}
                </span>
            </div>

            <div class="flex items-center gap-3 mt-5 mb-6">
                <div class="h-px flex-1" style="background:linear-gradient(to right, var(--color-primary), transparent);"></div>
                <svg width="12" height="12" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M8 0L9.6 6.4L16 8L9.6 9.6L8 16L6.4 9.6L0 8L6.4 6.4L8 0Z" fill="var(--color-primary)" opacity="0.5"/>
                </svg>
                <div class="h-px flex-1" style="background:linear-gradient(to left, var(--color-primary), transparent);"></div>
            </div>

            <div class="grid gap-4" style="grid-template-columns:repeat(auto-fill,minmax(230px,1fr));">
                <article
                    v-for="b in badgeItems"
                    :key="b.slug"
                    class="trs-badge"
                    :class="{ 'trs-badge--earned': b.earned, 'trs-badge--secret': b.secret }"
                >
                    <i
                        class="ti shrink-0"
                        :class="'ti-' + b.icon"
                        :style="{ fontSize: '22px', color: b.earned ? 'var(--color-primary)' : 'var(--text-muted)' }"
                        aria-hidden="true"
                    ></i>
                    <div style="min-width:0;">
                        <p
                            class="font-semibold"
                            style="font-family:'Space Grotesk',sans-serif; font-size:0.9rem; margin:0 0 0.2rem;"
                            :style="{ color: b.earned ? 'var(--text-primary)' : 'var(--text-muted)' }"
                        >
                            {{ b.name }}
                        </p>
                        <p class="text-xs leading-relaxed" style="color:var(--text-secondary); font-family:'Inter',sans-serif; margin:0;">
                            {{ b.description }}
                        </p>
                        <p
                            v-if="b.earned_at"
                            class="mt-2"
                            style="font-family:'Space Mono',monospace; font-size:10px; color:var(--text-muted); margin-bottom:0;"
                        >
                            {{ isCorporate ? 'Obtenue le' : 'Obtenu le' }} {{ b.earned_at }}
                        </p>
                    </div>
                </article>
            </div>
        </section>

    </CandidateLayout>
</template>

<style scoped>
/* ── Explication fonctionnement ── */
.trs-explainer {
    display: flex;
    align-items: flex-start;
    gap: 0.75rem;
    background: transparent;
    border: 1px solid var(--glass-border);
    border-radius: var(--r-lg);
    padding: 0.9rem 1.25rem;
}

/* ── Bandeau Éclats ── */
.trs-eclats {
    display: flex;
    align-items: center;
    gap: 0.85rem;
    background: var(--bg-elevated);
    border: 1px solid var(--glass-border);
    border-left: 3px solid var(--color-primary);
    border-radius: var(--r-lg);
    padding: 1rem 1.25rem;
}

/* ── Carte trésor (base .pt-card + accent or) ── */
.trs-card {
    border-top: 2px solid var(--color-primary);
    transition: box-shadow 0.2s ease, border-color 0.2s ease, transform 0.2s ease;
}
.trs-card:hover {
    border-color: var(--color-primary) !important;
    box-shadow: 0 8px 28px rgba(166, 117, 32, 0.16);
    transform: translateY(-3px);
}
.trs-card--locked {
    opacity: 0.8;
}

/* ── Distinctions / Hauts faits ─────────────────────────────────────── */
.trs-badge {
    display: flex;
    align-items: flex-start;
    gap: 0.8rem;
    padding: 1rem 1.1rem;
    border: 1px solid var(--glass-border);
    border-radius: var(--r-lg);
    background: transparent;
    opacity: 0.6;
    transition: opacity 0.2s ease, border-color 0.2s ease;
}
.trs-badge--earned {
    opacity: 1;
    background: var(--bg-elevated);
    border-left: 3px solid var(--color-primary);
}
/* Le secret reste discret : ni bordure d'accent, ni relief. */
.trs-badge--secret {
    border-style: dashed;
    opacity: 0.45;
}

/* ── Badge statut ── */
.trs-badge {
    font-family: 'Space Mono', monospace;
    font-size: 10px;
    letter-spacing: 0.16em;
    text-transform: uppercase;
    padding: 4px 10px;
    border-radius: 2px;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}

/* ── Emblème circulaire ── */
.trs-emblem {
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
.trs-emblem--locked {
    color: var(--text-muted);
}
.group:hover .trs-emblem {
    border-color: var(--color-primary);
    transform: rotate(-4deg) scale(1.05);
}

/* ── Bandeau recommandations globales ── */
.trs-reco {
    display: flex;
    align-items: flex-start;
    gap: 0.75rem;
    background: rgba(166, 117, 32, 0.06);
    border: 1px solid rgba(166, 117, 32, 0.30);
    border-radius: var(--r-lg);
    padding: 0.9rem 1.25rem;
}
.trs-reco--muted {
    background: transparent;
    border-color: var(--glass-border);
}

/* ── Carte recommandée : bordure or renforcée ── */
.trs-card--recommended {
    border-top-width: 3px;
    box-shadow: 0 0 0 1px rgba(166, 117, 32, 0.18);
}

/* ── Bannière "Recommandé" en haut de carte ── */
.trs-reco-banner {
    background: rgba(166, 117, 32, 0.10);
    border-bottom: 1px solid rgba(166, 117, 32, 0.25);
    border-radius: var(--r-lg) var(--r-lg) 0 0;
    font-family: 'Space Mono', monospace;
    font-size: 10px;
    letter-spacing: 0.14em;
    text-transform: uppercase;
    color: var(--color-primary);
    font-weight: 700;
}
</style>
