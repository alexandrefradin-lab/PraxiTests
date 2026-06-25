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

        <div class="trs-shell">

            <!-- ── En-tête ── -->
            <header class="trs-header">
                <div class="trs-flourish">&#10087;&nbsp;&nbsp;&#10022;&nbsp;&nbsp;&#10087;</div>
                <h1 class="trs-title">La Salle du Trésor</h1>
                <div class="trs-rule"><span>&#10022;</span></div>
                <p class="trs-sub">
                    Tes Éclats ouvrent des modules d'entraînement offerts.
                    Chaque palier franchi révèle un nouveau trésor — <strong>pour toujours</strong>.
                </p>
                <span class="trs-count">
                    {{ treasure.unlocked_count }}/{{ treasure.total_count }} trésors révélés
                </span>
            </header>

            <!-- ── Compteur d'Éclats ── -->
            <div class="trs-eclats">
                <i class="ti ti-diamond trs-eclats-icon"></i>
                <p class="trs-eclats-text">
                    Tu détiens
                    <strong class="trs-eclats-value">{{ treasure.total }} Éclats</strong>.
                    Continue tes Épreuves pour en accumuler et débloquer la suite.
                </p>
            </div>

            <!-- ── Grille des trésors ── -->
            <div v-if="treasure.items.length > 0" class="trs-grid">
                <article
                    v-for="item in treasure.items"
                    :key="item.plugin_slug"
                    class="trs-card"
                    :class="{ 'trs-card--locked': !item.unlocked }"
                >
                    <!-- Icône + badge statut -->
                    <div class="trs-card-head">
                        <span class="trs-card-icon">
                            <i
                                class="ti text-2xl"
                                :class="item.unlocked ? (item.icon || 'ti-gift') : 'ti-lock'"
                                :style="{ color: item.unlocked ? 'var(--trs-gold)' : 'var(--trs-muted)' }"
                            ></i>
                        </span>
                        <span v-if="item.unlocked" class="trs-badge trs-badge--unlocked">
                            <i class="ti ti-check"></i> Débloqué
                        </span>
                        <span v-else class="trs-badge trs-badge--locked">
                            Verrouillé
                        </span>
                    </div>

                    <!-- Nom + purpose -->
                    <h3 class="trs-card-name">{{ item.name }}</h3>
                    <p v-if="item.purpose" class="trs-card-purpose">{{ item.purpose }}</p>

                    <!-- Description / teaser -->
                    <p class="trs-card-desc">
                        {{ item.unlocked ? item.description : item.teaser }}
                    </p>

                    <!-- Barre de progression (verrouillé) -->
                    <div v-if="!item.unlocked" class="trs-progress">
                        <div class="trs-progress-meta">
                            <span>{{ item.progress_pct }}%</span>
                            <span>{{ item.threshold }} Éclats</span>
                        </div>
                        <div class="trs-progress-track">
                            <div
                                class="trs-progress-fill"
                                :style="{ width: item.progress_pct + '%' }"
                            ></div>
                        </div>
                        <p class="trs-progress-label">
                            <i class="ti ti-lock"></i>
                            Encore {{ item.remaining }} Éclats pour le révéler
                        </p>
                    </div>

                    <!-- Footer (débloqué) -->
                    <div v-else class="trs-card-footer">
                        <span class="trs-duration">
                            <template v-if="item.estimated_minutes">≈ {{ item.estimated_minutes }} min</template>
                            <template v-else>Module offert</template>
                        </span>
                        <div class="trs-card-actions">
                            <Link
                                v-if="item.url"
                                :href="item.url"
                                class="trs-btn-primary"
                                :class="{ 'trs-btn--disabled': !profile_complete }"
                            >
                                Ouvrir le trésor →
                            </Link>
                            <p v-if="!profile_complete" class="trs-profile-notice">
                                Complete ton profil pour accéder à ce trésor
                            </p>
                        </div>
                    </div>
                </article>
            </div>

            <!-- ── Liste vide ── -->
            <div v-else class="trs-empty">
                <div class="trs-flourish">&#10087;&nbsp;&nbsp;&#10022;&nbsp;&nbsp;&#10087;</div>
                <i class="ti ti-diamond trs-empty-icon"></i>
                <p class="trs-empty-title">La Salle du Trésor est encore scellée.</p>
                <p class="trs-empty-sub">
                    Aucun trésor n'est disponible pour le moment. Reviens après quelques Épreuves.
                </p>
            </div>

        </div>
    </CandidateLayout>
</template>

<style scoped>
.trs-shell {
    max-width: 1040px;
    margin: 0 auto;
    padding: 1rem 1.25rem 4rem;
    --trs-gold:      var(--color-primary, #A67520);
    --trs-gold-dark: var(--color-primary-dark, #7D5510);
    --trs-red:       var(--color-secondary, #7B1515);
    --trs-ink:       var(--text-primary, #2A1E08);
    --trs-muted:     var(--text-muted, #8C7A5E);
}

/* ── Décorations ────────────────────────────────────────────────────────── */
.trs-flourish {
    text-align: center;
    color: var(--trs-gold);
    font-size: 1.05rem;
    letter-spacing: .35em;
    opacity: .8;
    margin-bottom: 1.1rem;
}
.trs-rule {
    position: relative;
    height: 1px;
    max-width: 320px;
    margin: 1.1rem auto;
    background: linear-gradient(90deg, transparent, var(--trs-gold) 18%, var(--trs-gold) 82%, transparent);
    opacity: .55;
}
.trs-rule span {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background: var(--bg-base);
    padding: 0 .55rem;
    color: var(--trs-gold);
    font-size: .8rem;
}

/* ── En-tête ────────────────────────────────────────────────────────────── */
.trs-header {
    text-align: center;
    margin-bottom: 2.75rem;
}
.trs-title {
    font-family: var(--font-display, 'Space Grotesk', sans-serif);
    font-size: clamp(2.1rem, 5vw, 2.9rem);
    font-weight: 700;
    letter-spacing: .03em;
    margin: 0;
    color: var(--trs-ink);
    text-shadow: 0 1px 0 rgba(255,255,255,.5);
}
.trs-sub {
    font-family: var(--font-body, 'Inter', sans-serif);
    font-size: 1.05rem;
    color: var(--text-secondary, #6B5A3E);
    margin: 0 0 1.1rem;
}
.trs-sub strong { color: var(--trs-red); font-weight: 600; }
.trs-count {
    display: inline-block;
    font-family: var(--font-data, monospace);
    font-size: 10px;
    letter-spacing: .22em;
    text-transform: uppercase;
    color: var(--trs-gold-dark);
    border: 1px solid var(--trs-gold);
    border-radius: 2px;
    padding: 5px 14px;
    background: rgba(166,117,32,0.06);
}

/* ── Compteur d'Éclats ──────────────────────────────────────────────────── */
.trs-eclats {
    display: flex;
    align-items: center;
    gap: .85rem;
    border: 1px solid var(--border-mid, rgba(166,117,32,0.25));
    border-left: 3px solid var(--trs-gold);
    border-radius: var(--r-lg, 12px);
    padding: 1.1rem 1.4rem;
    margin-bottom: 2.5rem;
    background: linear-gradient(180deg, #FBF6EA, #F2E8D1);
    box-shadow: var(--shadow-xs, 0 1px 3px rgba(42,30,8,0.06));
}
.trs-eclats-icon {
    font-size: 1.6rem;
    color: var(--trs-gold);
    flex-shrink: 0;
}
.trs-eclats-text {
    font-family: var(--font-body, 'Inter', sans-serif);
    font-size: .98rem;
    color: var(--text-secondary, #6B5A3E);
    margin: 0;
}
.trs-eclats-value {
    font-family: var(--font-data, monospace);
    color: var(--trs-ink);
    font-weight: 700;
}

/* ── Grille ─────────────────────────────────────────────────────────────── */
.trs-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(290px, 1fr));
    gap: 1.1rem;
}

/* ── Carte trésor ───────────────────────────────────────────────────────── */
.trs-card {
    position: relative;
    display: flex;
    flex-direction: column;
    border: 1px solid var(--border-mid, rgba(166,117,32,0.25));
    border-top: 2px solid var(--trs-gold);
    border-radius: var(--r-lg, 12px);
    padding: 1.4rem 1.4rem 1.5rem;
    background: linear-gradient(180deg, #FBF6EA, #F1E7CF);
    box-shadow: var(--shadow-card, 0 2px 12px rgba(42,30,8,0.10));
    transition: box-shadow .18s ease, transform .18s ease, border-color .18s ease;
}
.trs-card:hover {
    box-shadow: var(--shadow-elevated, 0 8px 32px rgba(42,30,8,0.15));
    transform: translateY(-3px);
    border-color: var(--trs-gold);
}
.trs-card--locked {
    opacity: .75;
}

/* Tête de carte */
.trs-card-head {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    margin-bottom: .85rem;
}
.trs-card-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 44px;
    height: 44px;
    border-radius: var(--r, 8px);
    border: 1px solid var(--border-mid, rgba(166,117,32,0.25));
    background: radial-gradient(circle at 35% 30%, #FBF3DF, #E9D9B4);
    box-shadow: inset 0 1px 2px rgba(255,255,255,.6);
}

/* Badges */
.trs-badge {
    font-family: var(--font-data, monospace);
    font-size: 10px;
    letter-spacing: .18em;
    text-transform: uppercase;
    padding: 4px 10px;
    border-radius: 2px;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}
.trs-badge--unlocked {
    color: var(--trs-gold-dark);
    background: rgba(166,117,32,0.10);
    border: 1px solid rgba(166,117,32,0.35);
}
.trs-badge--locked {
    color: var(--trs-muted);
    background: rgba(140,122,94,0.10);
    border: 1px solid rgba(140,122,94,0.25);
}

/* Contenu */
.trs-card-name {
    font-family: var(--font-display, 'Space Grotesk', sans-serif);
    font-size: 1.12rem;
    font-weight: 600;
    color: var(--trs-ink);
    line-height: 1.3;
    margin: 0 0 .2rem;
}
.trs-card-purpose {
    font-family: var(--font-data, monospace);
    font-size: 10px;
    text-transform: uppercase;
    letter-spacing: .1em;
    color: var(--trs-red);
    margin: 0 0 .65rem;
}
.trs-card-desc {
    font-family: var(--font-body, 'Inter', sans-serif);
    font-size: .97rem;
    line-height: 1.6;
    color: var(--text-secondary, #6B5A3E);
    flex: 1;
    overflow: hidden;
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    margin: 0;
}

/* ── Barre de progression ───────────────────────────────────────────────── */
.trs-progress { margin-top: 1.1rem; }
.trs-progress-meta {
    display: flex;
    justify-content: space-between;
    font-family: var(--font-data, monospace);
    font-size: 11px;
    color: var(--text-secondary, #6B5A3E);
    margin-bottom: .45rem;
}
.trs-progress-track {
    height: 6px;
    background: rgba(140,122,94,0.2);
    border-radius: 999px;
    overflow: hidden;
}
.trs-progress-fill {
    height: 100%;
    background: linear-gradient(90deg, var(--trs-gold), var(--trs-gold-dark));
    border-radius: 999px;
    transition: width .4s ease;
}
.trs-progress-label {
    font-family: var(--font-body, 'Inter', sans-serif);
    font-size: .8rem;
    font-weight: 600;
    color: var(--trs-gold-dark);
    margin: .5rem 0 0;
}

/* ── Footer carte (débloqué) ────────────────────────────────────────────── */
.trs-card-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: .75rem;
    margin-top: 1.25rem;
    padding-top: .9rem;
    border-top: 1px solid rgba(166,117,32,0.25);
}
.trs-duration {
    font-family: var(--font-data, monospace);
    font-size: 11px;
    color: var(--trs-muted);
    flex-shrink: 0;
}
.trs-card-actions {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: .35rem;
}
.trs-btn-primary {
    font-family: var(--font-display, 'Space Grotesk', sans-serif);
    font-size: 13px;
    font-weight: 600;
    color: #FBF6EA;
    background: linear-gradient(180deg, var(--trs-gold), var(--trs-gold-dark));
    text-decoration: none;
    padding: 8px 16px;
    border-radius: var(--r-sm, 6px);
    box-shadow: inset 0 1px 0 rgba(255,255,255,.25), 0 1px 3px rgba(42,30,8,0.18);
    transition: filter .15s;
    white-space: nowrap;
}
.trs-btn-primary:hover { filter: brightness(1.09); }
.trs-btn--disabled {
    pointer-events: none;
    opacity: .4;
}
.trs-profile-notice {
    font-family: var(--font-data, monospace);
    font-size: 10px;
    color: var(--trs-muted);
    text-align: right;
    margin: 0;
    max-width: 180px;
    line-height: 1.4;
}

/* ── État vide ──────────────────────────────────────────────────────────── */
.trs-empty {
    text-align: center;
    padding: 4.5rem 1rem;
}
.trs-empty-icon {
    display: block;
    font-size: 3.5rem;
    color: var(--trs-muted);
    margin: 0 auto 1.1rem;
}
.trs-empty-title {
    font-family: var(--font-display, 'Space Grotesk', sans-serif);
    font-size: 1.25rem;
    font-weight: 700;
    color: var(--trs-ink);
    margin: 0 0 .5rem;
}
.trs-empty-sub {
    font-family: var(--font-body, 'Inter', sans-serif);
    font-size: 1rem;
    color: var(--text-secondary, #6B5A3E);
    max-width: 420px;
    margin: 0 auto;
    line-height: 1.65;
}

@media (max-width: 640px) {
    .trs-grid { grid-template-columns: 1fr; }
    .trs-card-footer { flex-wrap: wrap; }
    .trs-card-actions { align-items: stretch; width: 100%; }
    .trs-btn-primary { text-align: center; }
}
</style>
