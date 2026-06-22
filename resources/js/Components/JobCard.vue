<script setup>
/**
 * JobCard — carte « métier suggéré » réutilisable.
 *
 * Tolère les clés FR (titre, secteur, pourquoi, prochaine_étape) et EN
 * (title, sector, why, next_step) renvoyées par l'IA. Mutualise le markup
 * dupliqué dans praximet, praxisens, _template, praxis360…
 *
 * Usage : <JobCard v-for="(job,i) in result.suggested_jobs" :key="i" :job="job" />
 */
import { computed } from 'vue'

const props = defineProps({
    job: { type: Object, required: true },
})

const title    = computed(() => props.job.titre || props.job.title || '')
const sector   = computed(() => props.job.secteur || props.job.sector || '')
const why      = computed(() => props.job.pourquoi || props.job.why || '')
const nextStep = computed(() => props.job.prochaine_étape || props.job.next_step || '')
const fit      = computed(() => props.job.fit_score ?? null)
</script>

<template>
    <article class="job-card">
        <div class="job-card__head">
            <h3 class="job-card__title">{{ title }}</h3>
            <span v-if="fit !== null" class="job-card__fit">{{ fit }}%</span>
        </div>
        <p v-if="sector" class="job-card__sector">{{ sector }}</p>
        <p v-if="why" class="job-card__why">{{ why }}</p>
        <p v-if="nextStep" class="job-card__next">→ {{ nextStep }}</p>
    </article>
</template>

<style scoped>
.job-card {
    border: .5px solid var(--pt-border);
    border-radius: 10px;
    padding: 1rem;
    background: var(--pt-surface, #fff);
}
.job-card__head { display: flex; align-items: flex-start; justify-content: space-between; gap: 8px; margin-bottom: 4px; }
.job-card__title { font-size: 14px; font-weight: 600; color: var(--pt-navy); margin: 0; }
.job-card__fit {
    flex-shrink: 0;
    font-size: 11px;
    font-weight: 600;
    padding: 2px 8px;
    border-radius: 99px;
    background: var(--pt-gold-pale);
    color: var(--pt-gold-hover);
    border: .5px solid var(--pt-gold-border);
    white-space: nowrap;
}
.job-card__sector { font-size: 10px; text-transform: uppercase; letter-spacing: .05em; color: var(--pt-text-light); margin: 0 0 6px; }
.job-card__why { font-size: 13px; color: var(--pt-text); line-height: 1.5; margin: 0; }
.job-card__next { font-size: 12px; font-weight: 500; color: var(--pt-gold-hover); margin: .6rem 0 0; }
</style>
