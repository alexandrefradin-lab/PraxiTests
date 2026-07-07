<script setup>
/**
 * Restitution dédiée — L'Étoffe du Bâtisseur (compétences entrepreneuriales).
 * Archétype calculé sur les 3 aires EntreComp + radar 8 compétences +
 * forces/leviers + synthèse IA. Réutilise les composants partagés.
 */
import { computed } from 'vue'
import { Head } from '@inertiajs/vue3'
import CandidateLayout from '@/Layouts/CandidateLayout.vue'
import { useParcours } from '@/composables/useParcours'

const { isCorporate } = useParcours()
import RestitutionHeader from '@/Components/RestitutionHeader.vue'
import ResultPanel from '@/Components/ResultPanel.vue'
import RadarChart from '@/Components/RadarChart.vue'
import SynthesisCard from '@/Components/SynthesisCard.vue'
import ResultPdfButton from '@/Components/ResultPdfButton.vue'

const props = defineProps({
    attempt: Object,
    result:  Object,
})

const dims = computed(() => props.result?.scoring?.dimensions ?? {})

// Métadonnées des 8 compétences (libellé, aire EntreComp, définition brève).
const DIMS = {
    opportunites:    { label: "Repérage d'opportunités",   area: 'idees',      def: "Détecter des besoins non satisfaits et des occasions à saisir." },
    vision:          { label: 'Créativité & vision',        area: 'idees',      def: "Générer des idées et projeter ce qu'un projet pourrait devenir." },
    proactivite:     { label: "Prise d'initiative",         area: 'action',     def: "Prendre les devants et lancer les choses soi-même." },
    prise_risque:    { label: 'Tolérance au risque',        area: 'action',     def: "Avancer dans l'incertitude et engager sans garantie." },
    gestion:         { label: 'Ressources & gestion',       area: 'action',     def: "Planifier, organiser et gérer les moyens (temps, argent)." },
    resilience:      { label: 'Persévérance & résilience',  area: 'ressources', def: "Rebondir après un revers et tenir dans la durée." },
    leadership:      { label: 'Mobilisation & leadership',  area: 'ressources', def: "Fédérer, convaincre et s'entourer autour d'un projet." },
    auto_efficacite: { label: 'Auto-efficacité',            area: 'ressources', def: "Croire en sa capacité à mener un projet et à apprendre." },
}

const AREAS = {
    idees:      { label: 'Idées & opportunités',   keys: ['opportunites', 'vision'] },
    action:     { label: "Passage à l'action",     keys: ['proactivite', 'prise_risque', 'gestion'] },
    ressources: { label: 'Ressources & relations', keys: ['resilience', 'leadership', 'auto_efficacite'] },
}

const ARCHETYPES = {
    idees:      { icon: 'ti-compass',        name: "L'Éclaireur",   tagline: "Tu vois les occasions avant les autres.",
        desc: "Ton moteur, c'est le flair : tu repères les besoins et les occasions que d'autres n'ont pas vus, et tu imagines ce qu'un projet pourrait devenir. Ton défi : passer de l'idée à l'exécution et t'entourer pour concrétiser." },
    action:     { icon: 'ti-rocket',         name: 'Le Fonceur',    tagline: "Tu transformes les idées en actes.",
        desc: "Tu ne restes pas dans l'intention : tu lances, tu oses l'incertitude et tu organises. Ton moteur, c'est l'action. Ton défi : prendre le temps de la vision et de mobiliser durablement les autres." },
    ressources: { icon: 'ti-building-arch',  name: 'Le Bâtisseur',  tagline: "Tu tiens dans la durée et tu fédères.",
        desc: "Tu tiens dans la durée, tu crois en ta capacité et tu sais fédérer. Ton moteur, c'est la solidité. Ton défi : oser davantage le risque et l'exploration de nouvelles pistes." },
    polyvalent: { icon: 'ti-hexagon',        name: "L'Entrepreneur polyvalent", tagline: "Un profil complet, sans zone faible marquée.",
        desc: "Aucune aire ne domine nettement : tu combines flair, action et solidité. Ton moteur, c'est la polyvalence. Ton défi : choisir un angle d'attaque clair plutôt que de te disperser." },
}

const val = (key) => Math.round(Number(dims.value[key] ?? 0))

const radarAxes = computed(() =>
    Object.keys(DIMS).map((key) => ({ label: DIMS[key].label, value: val(key) }))
)

const areaScores = computed(() =>
    Object.fromEntries(Object.entries(AREAS).map(([k, a]) => {
        const vals = a.keys.map(val)
        const avg = vals.length ? Math.round(vals.reduce((s, v) => s + v, 0) / vals.length) : 0
        return [k, avg]
    }))
)

const archetypeKey = computed(() => {
    const s = areaScores.value
    const entries = Object.entries(s).sort((a, b) => b[1] - a[1])
    const spread = (entries[0]?.[1] ?? 0) - (entries[entries.length - 1]?.[1] ?? 0)
    return spread < 8 ? 'polyvalent' : entries[0][0]
})
const archetype = computed(() => ARCHETYPES[archetypeKey.value])

const sortedDims = computed(() =>
    Object.keys(DIMS)
        .map((key) => ({ key, ...DIMS[key], value: val(key) }))
        .sort((a, b) => b.value - a.value)
)
const topForces = computed(() => sortedDims.value.slice(0, 2))
const leviers   = computed(() => sortedDims.value.slice(-2).reverse())

const areaColor = (v) => v >= 66 ? '#4ade80' : (v >= 40 ? '#fbbf24' : '#f87171')
</script>

<template>
    <CandidateLayout>
        <Head title="Ton profil d'entrepreneur — L'Étoffe du Bâtisseur" />

        <div class="max-w-3xl mx-auto">
            <RestitutionHeader
                kicker="L'Étoffe du Bâtisseur · Compétences entrepreneuriales"
                title="Ton étoffe d'entrepreneur"
                subtitle="D'après le référentiel européen EntreComp. Auto-positionnement, pas une mesure normée."
            />

            <!-- Blason archétype -->
            <section class="pt-card ac-card-ornate ac-card-dark p-8 mb-8">
                <p class="text-xs uppercase tracking-wide" style="color:var(--pt-gold)">{{ isCorporate ? 'Votre archétype' : '✦ Ton archétype' }}</p>
                <div style="display:flex;gap:1.25rem;align-items:flex-start;margin-top:0.75rem">
                    <div class="ent-crest" aria-hidden="true">
                        <i class="ti" :class="archetype.icon"></i>
                    </div>
                    <div style="flex:1;min-width:0">
                        <h2 style="font-family:var(--font-display);font-size:1.7rem;font-weight:700;color:#F4ECD8;line-height:1.15">
                            {{ archetype.name }}
                        </h2>
                        <p style="color:var(--pt-gold-hover, #E6BE5A);font-size:0.95rem;margin-top:2px">{{ archetype.tagline }}</p>
                        <p style="color:rgba(240,232,212,0.82);font-size:0.9rem;line-height:1.65;margin-top:0.75rem">
                            {{ archetype.desc }}
                        </p>
                        <p v-if="topForces.length" style="margin-top:0.85rem;font-size:0.85rem;color:rgba(240,232,212,0.72)">
                            Ta plus grande force : <strong style="color:#F4ECD8">{{ topForces[0].label }}</strong>.
                        </p>
                    </div>
                </div>
            </section>

            <!-- Radar 8 compétences -->
            <ResultPanel label="Tes 8 compétences en un coup d'œil" class="mb-8">
                <div class="flex justify-center">
                    <RadarChart :axes="radarAxes" dark />
                </div>
            </ResultPanel>

            <!-- 3 moteurs (aires EntreComp) -->
            <ResultPanel class="mb-8">
                <h2 class="ac-panel-title mb-6">Tes 3 moteurs</h2>
                <div class="space-y-5">
                    <div v-for="(area, key) in AREAS" :key="key" class="ac-dark-item">
                        <div class="flex justify-between items-baseline mb-1">
                            <span class="ac-dark-name">{{ area.label }}</span>
                            <span class="text-sm font-semibold" :style="{ color: areaColor(areaScores[key]) }">{{ areaScores[key] }} %</span>
                        </div>
                        <div class="ac-dark-track">
                            <div :style="{ width: areaScores[key] + '%', backgroundColor: areaColor(areaScores[key]) }"></div>
                        </div>
                    </div>
                </div>
            </ResultPanel>

            <!-- Détail des 8 compétences -->
            <ResultPanel class="mb-8">
                <h2 class="ac-panel-title mb-6">Le détail de tes compétences</h2>
                <div class="space-y-5">
                    <div v-for="d in sortedDims" :key="d.key" class="ac-dark-item">
                        <div class="flex justify-between items-center mb-1 gap-3">
                            <span class="ac-dark-name">
                                {{ d.label }}
                                <span v-if="topForces.some(f => f.key === d.key)"
                                      class="text-xs px-2 py-0.5 rounded-full" style="background:rgba(74,222,128,0.15);color:#4ade80;white-space:nowrap">Force</span>
                                <span v-else-if="leviers.some(l => l.key === d.key)"
                                      class="text-xs px-2 py-0.5 rounded-full" style="background:rgba(251,191,36,0.15);color:#fbbf24;white-space:nowrap">À muscler</span>
                            </span>
                            <span class="text-sm font-semibold" style="color:#F4ECD8">{{ d.value }} %</span>
                        </div>
                        <div class="ac-dark-track">
                            <div :style="{ width: d.value + '%', background: 'var(--color-primary)' }"></div>
                        </div>
                        <p class="ac-dark-def">{{ d.def }}</p>
                    </div>
                </div>
            </ResultPanel>

            <!-- Forces / leviers -->
            <div class="grid md:grid-cols-2 gap-6 mb-8">
                <section class="pt-card ac-card-dark p-6">
                    <h3 class="ac-panel-title mb-3" style="font-size:1rem">Tes 2 forces</h3>
                    <ol class="space-y-2">
                        <li v-for="(f, i) in topForces" :key="f.key" class="flex justify-between ac-dark-name">
                            <span><span style="color:#4ade80;font-weight:700">{{ i + 1 }}.</span> {{ f.label }}</span>
                            <span class="ac-dark-muted">{{ f.value }} %</span>
                        </li>
                    </ol>
                </section>
                <section class="pt-card ac-card-dark p-6">
                    <h3 class="ac-panel-title mb-3" style="font-size:1rem">Tes 2 leviers</h3>
                    <ol class="space-y-2">
                        <li v-for="(l, i) in leviers" :key="l.key" class="flex justify-between ac-dark-name">
                            <span><span style="color:#fbbf24;font-weight:700">{{ i + 1 }}.</span> {{ l.label }}</span>
                            <span class="ac-dark-muted">{{ l.value }} %</span>
                        </li>
                    </ol>
                </section>
            </div>

            <!-- Synthèse IA — après les graphiques -->
            <SynthesisCard v-if="result?.ai_synthesis" :source="result.ai_synthesis" title="Ta synthèse" />
            <div v-else class="pt-card ac-card-dark" style="padding:3rem;text-align:center;margin-bottom:1rem">
                <div style="width:36px;height:36px;border-radius:50%;border:3px solid var(--pt-cream-dark);border-top-color:var(--pt-gold);animation:spin 1s linear infinite;margin:0 auto"></div>
                <p style="margin-top:1rem;color:var(--pt-text-muted)">Analyse en cours… (1 à 2 minutes)</p>
            </div>

            <ResultPdfButton :attempt-id="attempt.id" />
        </div>
    </CandidateLayout>
</template>

<style scoped>
@keyframes spin { to { transform: rotate(360deg) } }
.ent-crest {
    width: 64px;
    height: 64px;
    flex-shrink: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.9rem;
    color: var(--pt-gold, #C99030);
    border-radius: 50%;
    background: radial-gradient(circle at 50% 32%, rgba(230,190,90,0.22), rgba(0,0,0,0.28));
    border: 1px solid rgba(230,190,90,0.4);
    box-shadow: inset 0 1px 0 rgba(230,190,90,0.28), 0 4px 14px rgba(0,0,0,0.25);
}
</style>
