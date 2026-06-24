<script setup>
/**
 * TestPistesSection — 15 pistes métiers issues d'UN test spécifique.
 *
 * Les pistes sont éphémères (calculées en live, pas stockées en BDD).
 * Elles combinent : dimensions de CE test + contexte profil (CV, secteur, quête)
 * + filtre ≤ 1 an de formation.
 *
 * Réutilise PathCard pour l'affichage de chaque piste.
 */
import { computed } from 'vue'
import PathCard from '@/Components/PathCard.vue'

const props = defineProps({
    // Tableau plat de 15 pistes (computeForAttempt)
    pistes:      { type: Array,   default: () => [] },
    // true si le candidat est salarié → mention PTP
    ptpEligible: { type: Boolean, default: false },
})

// On sépare accessible (0 mois) et ptp (1–12 mois) pour deux blocs visuels.
const accessible = computed(() => props.pistes.filter(p => p.tier === 'accessible'))
const ptp        = computed(() => props.pistes.filter(p => p.tier === 'ptp'))
</script>

<template>
    <section v-if="pistes.length" class="tps-section" aria-label="Pistes métiers issues de ce test">
        <header class="tps-header">
            <h2 class="tps-title">Tes pistes métiers</h2>
            <p class="tps-subtitle">
                {{ pistes.length }} pistes sélectionnées d'après tes résultats à ce test et ton profil —
                toutes accessibles avec <strong>au maximum 1 an de formation</strong>.
                <template v-if="ptpEligible">
                    En tant que salarié, une reconversion courte peut être financée via un
                    <strong>PTP (Projet de Transition Professionnelle)</strong>.
                </template>
            </p>
        </header>

        <!-- Bloc « Accessible maintenant » -->
        <div v-if="accessible.length" class="tps-tier">
            <div class="tps-tier-label tps-tier-label--accessible">
                <span class="tps-tier-badge">0 formation</span>
                <span class="tps-tier-name">Accessible maintenant</span>
                <span class="tps-tier-hint">Aucune formation supplémentaire requise.</span>
            </div>
            <div class="tps-grid">
                <PathCard
                    v-for="path in accessible"
                    :key="path.slug"
                    :path="path"
                />
            </div>
        </div>

        <!-- Bloc « À portée via PTP » (≤ 12 mois) -->
        <div v-if="ptp.length" class="tps-tier tps-tier--featured">
            <div class="tps-tier-label tps-tier-label--ptp">
                <span class="tps-tier-badge tps-tier-badge--ptp">≤ 1 an · finançable</span>
                <span class="tps-tier-name">À portée via une formation courte</span>
                <span class="tps-tier-hint">
                    {{ ptpEligible
                        ? 'Finançable via PTP pour les salariés, ou CPF selon ta situation.'
                        : 'Formations courtes finançables selon ta situation (CPF, AIF, …).'
                    }}
                </span>
            </div>
            <div class="tps-grid">
                <PathCard
                    v-for="path in ptp"
                    :key="path.slug"
                    :path="path"
                />
            </div>
        </div>

        <p class="tps-disclaimer">
            Ces pistes sont calculées à partir de tes réponses à ce test et de ton profil (secteur, rôle, quête).
            Elles sont données à titre indicatif — les données de marché et de formation sont des estimations.
            Consulte un conseiller pour un projet personnalisé.
        </p>
    </section>
</template>

<style scoped>
.tps-section {
    margin-top: 3rem;
    padding: 2.5rem;
    background: var(--bg-elevated, #FAFAF7);
    border: 1px solid var(--glass-border, rgba(0,0,0,.08));
    border-radius: 16px;
}

.tps-header {
    margin-bottom: 2rem;
}

.tps-title {
    font-family: 'Playfair Display', 'Georgia', serif;
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--color-primary, #2C3E2D);
    margin: 0 0 .5rem;
}

.tps-subtitle {
    font-family: 'Inter', sans-serif;
    font-size: .9rem;
    color: var(--pt-text-muted, #6B7280);
    line-height: 1.6;
    max-width: 680px;
    margin: 0;
}

/* ── Tier ─────────────────────────────────── */
.tps-tier {
    margin-bottom: 2rem;
}

.tps-tier--featured .tps-grid {
    border-radius: 12px;
    padding: 1rem;
    background: linear-gradient(135deg, rgba(58,107,72,.04) 0%, transparent 60%);
    border: 1px solid rgba(58,107,72,.12);
}

.tps-tier-label {
    display: flex;
    align-items: baseline;
    gap: .75rem;
    margin-bottom: 1rem;
    flex-wrap: wrap;
}

.tps-tier-name {
    font-family: 'Space Grotesk', 'Inter', sans-serif;
    font-size: 1rem;
    font-weight: 600;
    color: var(--color-primary, #2C3E2D);
}

.tps-tier-hint {
    font-family: 'Inter', sans-serif;
    font-size: .8rem;
    color: var(--pt-text-muted, #6B7280);
}

.tps-tier-badge {
    font-family: 'Space Mono', monospace;
    font-size: .7rem;
    font-weight: 700;
    letter-spacing: .04em;
    padding: .2em .6em;
    border-radius: 20px;
    background: var(--bg-base, #F5F2E8);
    color: var(--pt-text-muted, #6B7280);
    border: 1px solid var(--glass-border, rgba(0,0,0,.1));
    white-space: nowrap;
}

.tps-tier-badge--ptp {
    background: rgba(58,107,72,.1);
    color: #3A6B48;
    border-color: rgba(58,107,72,.2);
}

/* ── Grid ─────────────────────────────────── */
.tps-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
    gap: 1rem;
}

/* ── Disclaimer ──────────────────────────── */
.tps-disclaimer {
    font-family: 'Inter', sans-serif;
    font-size: .75rem;
    color: var(--pt-text-light, #9CA3AF);
    line-height: 1.5;
    margin: 1.5rem 0 0;
    padding-top: 1rem;
    border-top: 1px solid var(--glass-border, rgba(0,0,0,.06));
}

/* ── Responsive ───────────────────────────── */
@media (max-width: 640px) {
    .tps-section {
        padding: 1.5rem;
    }
    .tps-grid {
        grid-template-columns: 1fr;
    }
}
</style>
