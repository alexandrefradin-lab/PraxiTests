<script setup>
import { computed } from 'vue'
import CandidateLayout from '@/Layouts/CandidateLayout.vue'
import TestPistesSection from '@/Components/TestPistesSection.vue'
import RadarChart from '@/Components/RadarChart.vue'

const props = defineProps({
    attempt:      Object,
    result:       Object,
    pistes_test:  { type: Array,   default: () => [] },
    ptp_eligible: { type: Boolean, default: false },
})
const scoring = computed(() => props.result?.scoring ?? {})
const dims = computed(() => scoring.value.scores_dim ?? {})
const facettes = computed(() => scoring.value.scores_facette ?? {})
const metaDim = computed(() => scoring.value.meta_dimensions ?? {})
const metaFac = computed(() => scoring.value.meta_facettes ?? {})
const archetype = computed(() => scoring.value.archetype ?? null)

// Toile d'araignée OCEAN — 5 axes (pct 0–100). Couleur depuis meta_dimensions,
// sinon palette OCEAN locale.
const OCEAN_ORDER = ['O', 'C', 'E', 'A', 'N']
const OCEAN_PALETTE = { O: '#8b5cf6', C: '#0ea5e9', E: '#f59e0b', A: '#10b981', N: '#ef4444' }
const radarAxes = computed(() =>
    OCEAN_ORDER
        .filter((key) => dims.value[key])
        .map((key) => ({
            label: dims.value[key]?.label ?? metaDim.value[key]?.label ?? key,
            value: Number(dims.value[key]?.pct ?? 0),
            color: metaDim.value[key]?.color ?? OCEAN_PALETTE[key],
        }))
)

const facettesByDim = computed(() => {
    const out = {}
    Object.entries(metaFac.value).forEach(([fk, info]) => {
        if (!out[info.dim]) out[info.dim] = []
        out[info.dim].push({ key: fk, label: info.label, ...(facettes.value[fk] ?? {}) })
    })
    return out
})

const niveauLabel = {
    tres_bas: 'Très bas',  bas: 'Bas',  moyen: 'Moyen',
    haut: 'Élevé',         tres_haut: 'Très élevé',
}
const niveauColor = {
    tres_bas: '#dc2626', bas: '#ea580c', moyen: '#64748b',
    haut: '#0ea5e9',     tres_haut: '#16a34a',
}
</script>

<template>
    <CandidateLayout>
        <Head title="Tes résultats — PraxiMum" />

        <div class="max-w-4xl mx-auto">
            <div class="text-center mb-12">
                <span class="pt-badge">PraxiMum · Big Five OCEAN</span>
                <h1 class="text-4xl font-semibold tracking-tight mt-4">Ta personnalité en 5 dimensions</h1>
                <p class="text-slate-600 mt-2 text-sm">Scores T normés (50 = moyenne). 30 facettes détaillées.</p>
            </div>

            <!-- Archétype principal -->
            <section v-if="archetype" class="pt-card overflow-hidden mb-8" :style="{ background: `linear-gradient(135deg, ${archetype.couleur1}, ${archetype.couleur2})` }">
                <div class="p-10 text-white">
                    <div class="flex items-start justify-between gap-6 flex-wrap">
                        <div class="flex items-center gap-4">
                            <span class="text-6xl">{{ archetype.emoji }}</span>
                            <div>
                                <p class="text-xs uppercase tracking-wider text-white/70">Ton archétype</p>
                                <h2 class="text-3xl font-semibold mt-1">{{ archetype.nom }}</h2>
                                <p class="text-white/90 mt-1 text-base">{{ archetype.tagline }}</p>
                            </div>
                        </div>
                        <div class="bg-white/15 backdrop-blur rounded-xl px-4 py-3 text-center">
                            <p class="text-xs uppercase tracking-wider text-white/70">Rareté</p>
                            <p class="text-2xl font-semibold mt-0.5">{{ archetype.rarete }}%</p>
                        </div>
                    </div>
                    <p class="mt-6 text-white/95 leading-relaxed text-[15px]">{{ archetype.description }}</p>
                    <div class="flex flex-wrap gap-2 mt-6">
                        <span v-for="trait in archetype.traits" :key="trait" class="bg-white/20 backdrop-blur text-white text-xs font-medium px-3 py-1 rounded-full">{{ trait }}</span>
                    </div>
                    <p v-if="archetype.distance > 0" class="text-xs text-white/60 mt-4">Profil le plus proche · combinaison {{ archetype.matched_key }}</p>
                </div>
            </section>

            <!-- Alerte DS -->
            <section v-if="scoring.desirabilite?.alert" class="pt-card p-6 mb-8 border-l-4 border-amber-400 bg-amber-50">
                <p class="font-semibold text-amber-900">Désirabilité sociale élevée ({{ scoring.desirabilite.pct }}%)</p>
                <p class="text-sm text-amber-800 mt-1">Tes réponses pourraient refléter une image idéalisée de toi-même. Considère les résultats avec ce filtre.</p>
            </section>

            <!-- Toile d'araignée OCEAN -->
            <section class="pt-card p-8 mb-8">
                <h2 class="text-xl font-semibold mb-1">Ton profil en un coup d'œil</h2>
                <p class="text-sm text-slate-500 mb-6">Tes 5 dimensions OCEAN sur une même toile.</p>
                <div class="flex justify-center">
                    <RadarChart :axes="radarAxes" />
                </div>
            </section>

            <!-- 5 dimensions OCEAN -->
            <section class="pt-card p-8 mb-8">
                <h2 class="text-xl font-semibold mb-6">Tes 5 dimensions OCEAN</h2>
                <div class="space-y-5">
                    <div v-for="(d, key) in dims" :key="key">
                        <div class="flex justify-between items-baseline mb-1">
                            <div>
                                <span class="font-semibold" :style="{ color: metaDim[key]?.color }">{{ d.label }}</span>
                                <span class="text-xs text-slate-500 ml-2">{{ metaDim[key]?.court }}</span>
                            </div>
                            <span class="text-xs font-medium" :style="{ color: niveauColor[d.niveau] }">{{ niveauLabel[d.niveau] }} · T={{ d.T }}</span>
                        </div>
                        <div class="pt-progress-track">
                            <div class="h-full rounded-full transition-all duration-700" :style="{ width: d.pct + '%', backgroundColor: metaDim[key]?.color }"></div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Facettes par dimension -->
            <section v-for="(facs, dimKey) in facettesByDim" :key="dimKey" class="pt-card p-8 mb-6">
                <h2 class="text-xl font-semibold mb-1" :style="{ color: metaDim[dimKey]?.color }">{{ metaDim[dimKey]?.label }}</h2>
                <p class="text-sm text-slate-500 mb-6">{{ metaDim[dimKey]?.court }}</p>
                <div class="grid md:grid-cols-2 gap-4">
                    <div v-for="f in facs" :key="f.key" class="border border-slate-100 rounded-xl p-4">
                        <div class="flex justify-between items-baseline mb-2">
                            <span class="font-medium text-sm">{{ f.label }}</span>
                            <span class="text-xs" :style="{ color: niveauColor[f.niveau] }">T={{ f.T }} · {{ niveauLabel[f.niveau] }}</span>
                        </div>
                        <div class="pt-progress-track">
                            <div class="h-full rounded-full transition-all duration-700" :style="{ width: f.pct + '%', backgroundColor: metaDim[dimKey]?.color }"></div>
                        </div>
                    </div>
                </div>
            </section>

            <div class="text-center mt-12">
                <!-- Pistes métiers (issues de ce test + profil) -->
                <TestPistesSection
                    v-if="pistes_test?.length"
                    :pistes="pistes_test"
                    :ptp-eligible="ptp_eligible"
                />

                <a :href="route('results.pdf', attempt.id)" class="pt-btn-ghost">Télécharger en PDF</a>
            </div>
        </div>
    </CandidateLayout>
</template>
