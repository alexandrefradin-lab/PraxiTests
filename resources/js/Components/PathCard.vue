<script setup>
/**
 * PathCard — carte d'une piste métier dynamique (PTP).
 *
 * Affiche le fit (tests), le bloc marché (indicatif), l'écart de formation et
 * l'action de déblocage. Données fournies par PtpPathService (back).
 * Voir PLAN-PISTES-DYNAMIQUES-PTP.md.
 */
import { computed } from 'vue'

const props = defineProps({
    // Objet « piste » = ProfilePathMatch fusionné avec son CareerPath.
    path: { type: Object, required: true },
})

const emit = defineEmits(['unlock'])

const demandLabel = {
    faible: 'Peu de tension',
    moyen:  'Demande modérée',
    fort:   'Forte demande',
}
const trendLabel = {
    declin:     'en repli',
    stable:     'stable',
    croissance: 'en croissance',
}
const trendColor = {
    declin:     'var(--pt-text-light)',
    stable:     'var(--pt-text-muted)',
    croissance: '#3A6B48',
}

const gapLabel = computed(() => {
    const m = props.path.formation_gap_months ?? 0
    if (m <= 0) return 'Aucune formation requise'
    if (m === 1) return '≈ 1 mois de formation'
    return `≈ ${m} mois de formation`
})

const salary = computed(() => {
    const s = props.path.salary_indicative
    if (!s || !s.median) return null
    const fmt = (n) => new Intl.NumberFormat('fr-FR').format(n)
    return `≈ ${fmt(s.median)} ${s.currency || 'EUR'}/an`
})
</script>

<template>
    <article class="path-card">
        <header class="path-card__head">
            <div>
                <h3 class="path-card__title">{{ path.title }}</h3>
                <p class="path-card__family">{{ path.family }}</p>
            </div>
            <span class="path-card__opp" :title="`Indice d'opportunité : ${path.opportunity_index}/100`">
                {{ path.opportunity_index }}
            </span>
        </header>

        <!-- Fit (tests) -->
        <div class="path-card__row">
            <span class="path-card__label">Correspondance</span>
            <div class="pt-progress-track" style="flex:1">
                <div class="pt-progress-fill" :style="{ width: (path.fit_score ?? 0) + '%' }"></div>
            </div>
            <span class="path-card__val">{{ path.fit_score ?? 0 }}%</span>
        </div>

        <!-- Bloc marché (indicatif) -->
        <div class="path-card__market">
            <span>{{ demandLabel[path.market_demand] ?? '—' }}</span>
            <span :style="{ color: trendColor[path.market_trend] }">· {{ trendLabel[path.market_trend] ?? '' }}</span>
            <span v-if="salary" class="path-card__salary">· {{ salary }}</span>
        </div>

        <!-- Chemin de déblocage -->
        <footer class="path-card__foot">
            <span class="path-card__gap">{{ gapLabel }}</span>
            <button v-if="(path.formation_gap_months ?? 0) > 0 && !path.unlocked" type="button" class="path-card__unlock" @click="emit('unlock', path)">
                J'ai / je vise cette formation
            </button>
            <span v-else-if="path.unlocked" class="path-card__unlocked">✓ Acquis déclaré</span>
        </footer>
    </article>
</template>

<style scoped>
.path-card {
    border: .5px solid var(--pt-border, rgba(166,117,32,0.2));
    border-radius: 12px;
    padding: 1rem 1.1rem;
    background: var(--pt-surface, #fff);
    display: flex;
    flex-direction: column;
    gap: .7rem;
}
.path-card__head { display: flex; align-items: flex-start; justify-content: space-between; gap: .75rem; }
.path-card__title { font-size: 15px; font-weight: 600; color: var(--pt-navy, #2A1E08); margin: 0; }
.path-card__family { font-size: 11px; text-transform: uppercase; letter-spacing: .05em; color: var(--pt-text-light, #8C7A5E); margin: 2px 0 0; }
.path-card__opp {
    flex-shrink: 0;
    font-family: 'Space Mono', monospace;
    font-size: 15px;
    font-weight: 600;
    color: var(--pt-gold-hover, #A67520);
    background: var(--pt-gold-pale, #F5E6C8);
    border: .5px solid var(--pt-gold-border, rgba(166,117,32,0.3));
    border-radius: 8px;
    padding: 3px 9px;
}
.path-card__row { display: flex; align-items: center; gap: 10px; }
.path-card__label { font-size: 12px; color: var(--pt-text-muted, #6B5A3E); min-width: 110px; }
.path-card__val { font-size: 12px; font-weight: 600; color: var(--pt-text, #2A1E08); min-width: 36px; text-align: right; }
.path-card__market { font-size: 12.5px; color: var(--pt-text-muted, #6B5A3E); display: flex; flex-wrap: wrap; gap: 5px; }
.path-card__salary { color: var(--pt-text, #2A1E08); }
.path-card__foot { display: flex; align-items: center; justify-content: space-between; gap: .5rem; margin-top: .15rem; }
.path-card__gap { font-size: 12.5px; font-weight: 500; color: var(--pt-text, #2A1E08); }
.path-card__unlock {
    font-size: 12px;
    font-weight: 500;
    color: var(--pt-gold-hover, #A67520);
    background: none;
    border: .5px solid var(--pt-gold-border, rgba(166,117,32,0.3));
    border-radius: 99px;
    padding: 3px 12px;
    cursor: pointer;
}
.path-card__unlock:hover { background: var(--pt-gold-pale, #F5E6C8); }
.path-card__unlocked { font-size: 12px; font-weight: 500; color: #3A6B48; }
</style>
