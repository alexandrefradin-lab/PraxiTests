<script setup>
import { computed } from 'vue'
import { Link, Head } from '@inertiajs/vue3'
import CandidateLayout from '@/Layouts/CandidateLayout.vue'

const props = defineProps({
    treasure: {
        type: Object,
        default: () => ({ total: 0, unlocked_count: 0, total_count: 0, items: [] }),
    },
    profile_complete: { type: Boolean, default: false },
})

const unlockedPct = computed(() => {
    const total = props.treasure.total_count || 0
    return total > 0 ? Math.round((props.treasure.unlocked_count / total) * 100) : 0
})
</script>

<template>
    <CandidateLayout>
        <Head title="La Salle du Trésor" />

        <!-- ── En-tête page (calqué sur L'Armurerie) ── -->
        <div class="mb-8">
            <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-3">
                <div>
                    <h1
                        class="font-bold tracking-tight leading-none"
                        style="font-family:'Space Grotesk',sans-serif; color:var(--text-primary); font-size:2.5rem;"
                    >
                        La Salle du Trésor
                    </h1>
                    <p class="mt-2 text-sm" style="color:var(--text-secondary); font-family:'Inter',sans-serif;">
                        Tes Éclats ouvrent des modules d'entraînement offerts — révélés pour toujours.
                    </p>
                </div>
                <span
                    class="text-sm whitespace-nowrap self-start sm:self-end pb-0.5"
                    style="font-family:'Space Mono',monospace; color:var(--text-secondary);"
                >
                    {{ treasure.unlocked_count }}/{{ treasure.total_count }} trésors révélés
                </span>
            </div>

            <!-- Barre de progression globale -->
            <div v-if="treasure.total_count > 0" class="mt-3" style="display:flex;align-items:center;gap:0.75rem;">
                <div style="flex:1;height:5px;border-radius:99px;background:var(--bg-elevated);overflow:hidden;">
                    <div :style="{ width: unlockedPct + '%', height:'100%', background:'var(--color-primary)', borderRadius:'99px', transition:'width 0.4s ease' }"></div>
                </div>
                <span style="font-size:0.72rem;font-weight:600;color:var(--text-secondary);flex-shrink:0;white-space:nowrap;">
                    {{ treasure.unlocked_count }}/{{ treasure.total_count }} révélés
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

        <!-- ── Bandeau Éclats détenus ── -->
        <div class="trs-eclats mb-8">
            <i class="ti ti-diamond text-xl shrink-0" style="color:var(--color-primary);"></i>
            <p class="text-sm" style="color:var(--text-secondary); font-family:'Inter',sans-serif; margin:0;">
                Tu détiens
                <strong style="font-family:'Space Mono',monospace; color:var(--text-primary); font-weight:700;">{{ treasure.total }} Éclats</strong>.
                Continue tes Épreuves pour en accumuler et débloquer la suite.
            </p>
        </div>

        <!-- ── Alerte profil incomplet (calqué sur L'Armurerie) ── -->
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
                    → La compléter maintenant
                </Link>
            </div>
        </div>

        <!-- ── Grille des trésors ── -->
        <div v-if="treasure.items.length > 0" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <article
                v-for="item in treasure.items"
                :key="item.plugin_slug"
                class="pt-card trs-card p-6 flex flex-col group"
                :class="{ 'trs-card--locked': !item.unlocked }"
            >
                <!-- Badge statut + emblème -->
                <div class="flex items-start justify-between mb-3 gap-3">
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
                    {{ item.name }}
                </h3>
                <p
                    v-if="item.purpose"
                    class="mb-2"
                    style="font-family:'Space Mono',monospace; font-size:10px; text-transform:uppercase; letter-spacing:0.1em; color:var(--color-secondary);"
                >
                    {{ item.purpose }}
                </p>

                <!-- Description / teaser -->
                <p
                    class="text-[13px] leading-relaxed flex-1 overflow-hidden"
                    style="font-family:'Inter',sans-serif; color:var(--text-secondary); display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical;"
                >
                    {{ item.unlocked ? item.description : item.teaser }}
                </p>

                <!-- Progression (verrouillé) -->
                <div v-if="!item.unlocked" class="mt-4">
                    <div class="flex justify-between mb-1.5" style="font-family:'Space Mono',monospace; font-size:11px; color:var(--text-secondary);">
                        <span>{{ item.progress_pct }}%</span>
                        <span>{{ item.threshold }} Éclats</span>
                    </div>
                    <div style="height:6px;background:rgba(140,122,94,0.2);border-radius:999px;overflow:hidden;">
                        <div :style="{ width: item.progress_pct + '%', height:'100%', background:'var(--color-primary)', borderRadius:'999px', transition:'width 0.4s ease' }"></div>
                    </div>
                    <p class="mt-2" style="font-family:'Inter',sans-serif; font-size:0.8rem; font-weight:600; color:var(--color-primary-dark);">
                        <i class="ti ti-lock"></i>
                        Encore {{ item.remaining }} Éclats pour le révéler
                    </p>
                </div>

                <!-- Footer (débloqué) -->
                <div
                    v-else
                    class="flex items-center justify-between mt-5 pt-4 gap-3"
                    style="border-top:1px solid var(--glass-border);"
                >
                    <span style="font-family:'Space Mono',monospace; font-size:11px; color:var(--text-muted); flex-shrink:0;">
                        <template v-if="item.estimated_minutes">≈ {{ item.estimated_minutes }} min</template>
                        <template v-else>Module offert</template>
                    </span>
                    <Link
                        v-if="item.url"
                        :href="item.url"
                        class="pt-btn-primary text-xs px-4 py-2"
                        :class="{ 'opacity-40 pointer-events-none': !profile_complete }"
                    >
                        Ouvrir le trésor →
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

    </CandidateLayout>
</template>

<style scoped>
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

/* ── Emblème circulaire (calqué sur .pt-emblem de l'Armurerie) ── */
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

</style>
