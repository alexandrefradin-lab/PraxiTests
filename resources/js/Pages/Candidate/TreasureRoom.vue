<script setup>
import { Link, Head } from '@inertiajs/vue3'
import CandidateLayout from '@/Layouts/CandidateLayout.vue'

defineProps({
    treasure: {
        type: Object,
        default: () => ({ total: 0, unlocked_count: 0, total_count: 0, items: [] }),
    },
    profile_complete: { type: Boolean, default: false },
})
</script>

<template>
    <CandidateLayout>
        <Head title="La Salle du Trésor" />

        <!-- ── En-tête page ── -->
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
                        Tes Éclats ouvrent des modules d'entraînement offerts. Chaque palier franchi révèle un nouveau trésor — pour toujours.
                    </p>
                </div>
                <span
                    class="text-sm whitespace-nowrap self-start sm:self-end pb-0.5"
                    style="font-family:'Space Mono',monospace; color:var(--text-secondary);"
                >
                    {{ treasure.unlocked_count }}/{{ treasure.total_count }} trésors révélés
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

        <!-- ── Compteur d'Éclats ── -->
        <div
            class="rounded-xl border-2 p-4 mb-8 flex items-center gap-3"
            style="background:var(--bg-elevated); border-color:var(--color-primary);"
        >
            <i class="ti ti-diamond text-2xl shrink-0" style="color:var(--color-primary);"></i>
            <p class="text-sm" style="color:var(--text-primary); font-family:'Inter',sans-serif;">
                Tu détiens
                <strong style="font-family:'Space Mono',monospace;">{{ treasure.total }} Éclats</strong>.
                Continue tes Épreuves pour en accumuler et débloquer la suite.
            </p>
        </div>

        <!-- ── Grille des trésors ── -->
        <div v-if="treasure.items.length > 0" class="grid md:grid-cols-2 gap-4">
            <div
                v-for="item in treasure.items"
                :key="item.plugin_slug"
                class="pt-card p-6 flex flex-col transition-all duration-200"
                :style="{ opacity: item.unlocked ? 1 : 0.72 }"
            >
                <!-- Icône + statut -->
                <div class="flex items-start justify-between mb-3">
                    <span
                        class="inline-flex items-center justify-center rounded-lg"
                        style="width:44px; height:44px; background:var(--bg-elevated);"
                    >
                        <i
                            class="ti text-2xl"
                            :class="item.unlocked ? (item.icon || 'ti-gift') : 'ti-lock'"
                            :style="{ color: item.unlocked ? 'var(--color-primary)' : 'var(--text-secondary)' }"
                        ></i>
                    </span>
                    <span
                        v-if="item.unlocked"
                        class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] uppercase tracking-widest"
                        style="font-family:'Space Mono',monospace; color:var(--color-primary); background:var(--bg-elevated);"
                    >
                        <i class="ti ti-check"></i> Débloqué
                    </span>
                    <span
                        v-else
                        class="px-2 py-0.5 rounded text-[10px] uppercase tracking-widest"
                        style="font-family:'Space Mono',monospace; color:var(--text-secondary); background:var(--bg-elevated);"
                    >
                        Verrouillé
                    </span>
                </div>

                <!-- Titre -->
                <h3
                    class="font-bold mb-1 leading-snug"
                    style="font-family:'Space Grotesk',sans-serif; font-size:16px; color:var(--text-primary);"
                >
                    {{ item.name }}
                </h3>

                <!-- Usage (à quoi sert l'app) -->
                <p
                    v-if="item.purpose"
                    class="mb-2 text-[11px] uppercase tracking-wider"
                    style="font-family:'Space Mono',monospace; color:var(--color-primary);"
                >
                    {{ item.purpose }}
                </p>

                <!-- Description / teaser -->
                <p
                    class="text-[13px] leading-relaxed flex-1 overflow-hidden"
                    style="font-family:'Inter',sans-serif; color:var(--text-secondary); display:-webkit-box; -webkit-line-clamp:3; -webkit-box-orient:vertical;"
                >
                    {{ item.unlocked ? item.description : item.teaser }}
                </p>

                <!-- Barre de progression (verrouillé) -->
                <div v-if="!item.unlocked" class="mt-4">
                    <div class="flex items-center justify-between mb-1.5 text-xs" style="color:var(--text-secondary); font-family:'Space Mono',monospace;">
                        <span>{{ item.progress_pct }}%</span>
                        <span>{{ item.threshold }} Éclats</span>
                    </div>
                    <div style="height:6px; background:var(--bg-elevated); border-radius:999px; overflow:hidden;">
                        <div
                            :style="{ width: item.progress_pct + '%', height:'100%', background:'var(--color-primary)', transition:'width .4s' }"
                        ></div>
                    </div>
                    <p class="mt-2 text-xs font-semibold" style="color:var(--color-primary); font-family:'Inter',sans-serif;">
                        <i class="ti ti-lock"></i>
                        Encore {{ item.remaining }} Éclats pour le révéler
                    </p>
                </div>

                <!-- Footer (débloqué) -->
                <div
                    v-else
                    class="flex items-center justify-between mt-5 pt-4"
                    style="border-top:1px solid var(--glass-border);"
                >
                    <span class="text-xs" style="font-family:'Space Mono',monospace; color:var(--text-secondary);">
                        <template v-if="item.estimated_minutes">≈ {{ item.estimated_minutes }} min</template>
                        <template v-else>Module offert</template>
                    </span>
                    <div class="flex flex-col items-end gap-1">
                        <Link
                            v-if="item.url"
                            :href="item.url"
                            class="pt-btn-primary text-xs px-4 py-2"
                            :class="{ 'pointer-events-none opacity-40': !profile_complete }"
                        >
                            Ouvrir le trésor →
                        </Link>
                        <p
                            v-if="!profile_complete"
                            style="font-family:'Space Mono',monospace; font-size:10px; color:var(--text-secondary); text-align:right;"
                        >
                            Complete ton profil pour accéder à ce trésor
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- ── Liste vide ── -->
        <div v-else class="pt-card p-12 text-center">
            <i class="ti ti-diamond block text-6xl mb-4" style="color:var(--text-secondary);"></i>
            <p class="text-base font-semibold mb-1" style="font-family:'Space Grotesk',sans-serif; color:var(--text-primary);">
                La Salle du Trésor est encore scellée.
            </p>
            <p class="text-sm" style="color:var(--text-secondary); font-family:'Inter',sans-serif;">
                Aucun trésor n'est disponible pour le moment. Reviens après quelques Épreuves.
            </p>
        </div>

    </CandidateLayout>
</template>
