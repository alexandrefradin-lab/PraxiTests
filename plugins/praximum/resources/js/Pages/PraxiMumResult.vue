<script setup>
import { computed, reactive, onMounted } from 'vue'
import CandidateLayout from '@/Layouts/CandidateLayout.vue'
import RadarChart from '@/Components/RadarChart.vue'
import MarkdownText from '@/Components/MarkdownText.vue'
import RestitutionHeader from '@/Components/RestitutionHeader.vue'
import ResultPanel from '@/Components/ResultPanel.vue'

const props = defineProps({
    attempt:      Object,
    result:       Object,
})
const scoring = computed(() => props.result?.scoring ?? {})
const dims = computed(() => scoring.value.scores_dim ?? {})
const facettes = computed(() => scoring.value.scores_facette ?? {})
const metaDim = computed(() => scoring.value.meta_dimensions ?? {})
const metaFac = computed(() => scoring.value.meta_facettes ?? {})

const prefersReducedMotion = typeof window !== 'undefined'
    && window.matchMedia?.('(prefers-reduced-motion: reduce)').matches

const animatedDims = reactive({})
const animatedFacs = reactive({})

function countUp(target, durationMs, onTick) {
    if (prefersReducedMotion) { onTick(target); return }
    const start = Date.now()
    const tick = () => {
        const p = Math.min((Date.now() - start) / durationMs, 1)
        const ease = 1 - Math.pow(1 - p, 3)
        onTick(Math.round(ease * target))
        if (p < 1) requestAnimationFrame(tick)
        else onTick(target)
    }
    requestAnimationFrame(tick)
}

onMounted(() => {
    const dimEntries = Object.entries(dims.value)
    dimEntries.forEach(([key, d], i) => {
        animatedDims[key] = 0
        setTimeout(() => countUp(Number(d.pct ?? 0), 1000, v => { animatedDims[key] = v }), 300 + i * 120)
    })

    const facEntries = Object.entries(facettes.value)
    facEntries.forEach(([key, f], i) => {
        animatedFacs[key] = 0
        setTimeout(() => countUp(Number(f.pct ?? 0), 900, v => { animatedFacs[key] = v }), 800 + i * 40)
    })
})
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
        out[info.dim].push({ key: fk, label: info.label, court: info.court, ...(facettes.value[fk] ?? {}) })
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
            <RestitutionHeader
                kicker="PraxiMum · Big Five OCEAN"
                title="Ta personnalité en 5 dimensions"
                subtitle="Scores T normés (50 = moyenne). 30 facettes détaillées."
            />

            <!-- Archétype principal — blason « constellation » -->
            <section v-if="archetype" class="mum-arch pq-reveal mb-8" style="animation-delay:0.2s">
                <span class="mum-orn mum-tl"></span><span class="mum-orn mum-tr"></span><span class="mum-orn mum-bl"></span><span class="mum-orn mum-br"></span>
                <div class="mum-arch-grid">
                    <!-- Blason -->
                    <div class="mum-crest">
                        <svg viewBox="0 0 120 132" class="mum-crest-svg" aria-hidden="true">
                            <polygon points="60,4 112,32 112,90 60,128 8,90 8,32" fill="rgba(230,190,90,0.07)" stroke="var(--color-primary)" stroke-width="2"/>
                            <polygon points="60,16 100,38 100,84 60,116 20,84 20,38" fill="none" stroke="var(--color-primary)" stroke-width="0.6" opacity="0.5"/>
                        </svg>
                        <span class="mum-crest-emoji">{{ archetype.emoji }}</span>
                    </div>
                    <!-- Corps -->
                    <div class="mum-arch-body">
                        <div class="mum-krow">
                            <span class="mum-kicker">✦ Ton archétype</span>
                            <span v-if="archetype.rarete != null" class="mum-rare-badge">Rareté · {{ archetype.rarete }}%</span>
                        </div>
                        <h2 class="mum-name">{{ archetype.nom }}</h2>
                        <p class="mum-tagline">{{ archetype.tagline }}</p>
                        <p class="mum-desc">{{ archetype.description }}</p>
                        <div class="mum-chips">
                            <span v-for="trait in archetype.traits" :key="trait">{{ trait }}</span>
                        </div>
                        <p v-if="archetype.distance > 0" class="mum-near">Profil le plus proche · combinaison {{ archetype.matched_key }}</p>
                    </div>
                </div>
            </section>

            <!-- Alerte DS -->
            <section v-if="scoring.desirabilite?.alert" class="pt-card p-6 mb-8 border-l-4 border-amber-400 bg-amber-50 pq-reveal" style="animation-delay:0.35s">
                <p class="font-semibold text-amber-900">Désirabilité sociale élevée ({{ scoring.desirabilite.pct }}%)</p>
                <p class="text-sm text-amber-800 mt-1">Tes réponses pourraient refléter une image idéalisée de toi-même. Considère les résultats avec ce filtre.</p>
            </section>

            <!-- Toile d'araignée OCEAN -->
            <ResultPanel label="Ton profil en un coup d'œil" class="mb-8 pq-reveal" style="animation-delay:0.4s">
                <div class="flex justify-center">
                    <RadarChart :axes="radarAxes" dark />
                </div>
            </ResultPanel>

            <!-- 5 dimensions OCEAN -->
            <section class="pt-card p-8 mb-8 pq-reveal" style="animation-delay:0.55s">
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
                            <div class="h-full rounded-full" :style="{ width: (animatedDims[key] ?? 0) + '%', backgroundColor: metaDim[key]?.color }"></div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Facettes par dimension -->
            <section v-for="(facs, dimKey, di) in facettesByDim" :key="dimKey" class="pt-card p-8 mb-6 pq-reveal" :style="{ animationDelay: (0.65 + di * 0.12) + 's' }">
                <h2 class="text-xl font-semibold mb-1" :style="{ color: metaDim[dimKey]?.color }">{{ metaDim[dimKey]?.label }}</h2>
                <p class="text-sm text-slate-500 mb-6">{{ metaDim[dimKey]?.court }}</p>
                <div class="grid md:grid-cols-2 gap-4">
                    <div v-for="f in facs" :key="f.key" class="border border-slate-100 rounded-xl p-4">
                        <div class="flex justify-between items-baseline mb-2">
                            <span class="font-medium text-sm">{{ f.label }}</span>
                            <span class="text-xs" :style="{ color: niveauColor[f.niveau] }">T={{ f.T }} · {{ niveauLabel[f.niveau] }}</span>
                        </div>
                        <div class="pt-progress-track">
                            <div class="h-full rounded-full" :style="{ width: (animatedFacs[f.key] ?? 0) + '%', backgroundColor: metaDim[dimKey]?.color }"></div>
                        </div>
                        <p v-if="f.court" class="text-xs mt-2" style="color:var(--text-muted); line-height:1.45">{{ f.court }}</p>
                    </div>
                </div>
            </section>

            <div v-if="result?.ai_synthesis" class="mt-6 pt-4 border-t border-amber-200">
                <h3 class="font-semibold mb-2">Synthèse personnalisée</h3>
                <MarkdownText :source="result.ai_synthesis" />
                <p style="margin-top:0.85rem;padding-top:0.7rem;border-top:1px solid rgba(217,119,6,0.25);font-size:11.5px;line-height:1.55;color:#9a8866">
                    <strong style="font-weight:600;color:#57534e">Outil d'auto-évaluation et de développement personnel.</strong>
                    Cette synthèse est générée par IA, à titre informatif. Elle ne constitue pas un avis
                    professionnel et ne remplace pas un psychologue, un médecin ou un coach.
                </p>
            </div>

            <div class="text-center mt-12">

                <a :href="route('results.pdf', attempt.id)" class="pt-btn-ghost">Télécharger en PDF</a>
            </div>
        </div>
    </CandidateLayout>
</template>

<style scoped>
.pq-reveal {
    opacity: 0;
    animation: pqReveal 0.55s cubic-bezier(0.22, 1, 0.36, 1) both;
}

/* ── Archétype : blason constellation ── */
.mum-arch {
    position: relative;
    border-radius: 16px;
    overflow: hidden;
    padding: 1.9rem 2rem;
    color: #F0E8D4;
    background: radial-gradient(ellipse at 22% 12%, #241a0e, var(--color-accent) 62%, #120c04);
    border: 1px solid var(--color-primary-dark);
    box-shadow: 0 12px 30px rgba(42,30,8,0.30);
}
.mum-orn {
    position: absolute;
    width: 16px; height: 16px;
    border: 1.5px solid var(--color-primary);
    opacity: 0.7;
    pointer-events: none;
}
.mum-tl { top: 10px; left: 10px; border-right: 0; border-bottom: 0; border-radius: 3px 0 0 0; }
.mum-tr { top: 10px; right: 10px; border-left: 0; border-bottom: 0; border-radius: 0 3px 0 0; }
.mum-bl { bottom: 10px; left: 10px; border-right: 0; border-top: 0; border-radius: 0 0 0 3px; }
.mum-br { bottom: 10px; right: 10px; border-left: 0; border-top: 0; border-radius: 0 0 3px 0; }
.mum-arch-grid { display: flex; gap: 1.5rem; align-items: center; flex-wrap: wrap; }
.mum-crest { position: relative; width: 120px; flex-shrink: 0; display: flex; flex-direction: column; align-items: center; }
.mum-crest-svg { width: 120px; height: 132px; display: block; }
.mum-crest-emoji { position: absolute; top: 50%; left: 0; right: 0; transform: translateY(-50%); text-align: center; font-size: 44px; line-height: 1; }
.mum-arch-body { flex: 1; min-width: 240px; }
.mum-krow { display: flex; align-items: center; justify-content: space-between; gap: 0.75rem; flex-wrap: wrap; margin-bottom: 0.15rem; }
.mum-kicker { font-family: var(--font-data); font-size: 10px; letter-spacing: 0.18em; text-transform: uppercase; color: var(--color-primary-light); }
.mum-rare-badge {
    font-family: var(--font-data);
    font-size: 10.5px;
    font-weight: 700;
    letter-spacing: 0.04em;
    color: var(--color-accent);
    background: linear-gradient(180deg, var(--color-primary-light), var(--color-primary));
    border: 1px solid var(--color-primary-dark);
    border-radius: 999px;
    padding: 3px 12px;
    white-space: nowrap;
    box-shadow: 0 1px 4px rgba(0,0,0,0.3), inset 0 1px 0 rgba(255,255,255,0.3);
}
.mum-name { font-family: var(--font-display); font-size: 1.9rem; font-weight: 700; letter-spacing: -0.02em; color: #F4ECD8; margin: 0.2rem 0 0.25rem; text-shadow: 0 0 22px rgba(230,190,90,0.2); }
.mum-tagline { font-size: 14px; color: rgba(240,232,212,0.8); margin: 0 0 0.8rem; }
.mum-desc { font-size: 13.5px; line-height: 1.7; color: rgba(240,232,212,0.62); margin: 0 0 1rem; }
.mum-chips { display: flex; flex-wrap: wrap; gap: 0.5rem; }
.mum-chips span { font-family: var(--font-data); font-size: 10.5px; color: var(--color-primary-light); background: rgba(230,190,90,0.10); border: 1px solid rgba(230,190,90,0.35); padding: 3px 11px; border-radius: 20px; }
.mum-near { font-size: 11px; color: rgba(240,232,212,0.45); margin-top: 0.9rem; }
@keyframes pqReveal {
    from { opacity: 0; transform: translateY(20px); }
    to   { opacity: 1; transform: none; }
}
@media (prefers-reduced-motion: reduce) {
    .pq-reveal { animation: none; opacity: 1; transform: none; }
}
</style>
