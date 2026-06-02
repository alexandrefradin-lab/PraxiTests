<script setup>
import { computed, onMounted, onUnmounted, ref } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import CandidateLayout from '@/Layouts/CandidateLayout.vue'

const props = defineProps({
    attempt:    Object,
    result:     Object,
    ai_pending: Boolean,
})

// ── Polling — rafraîchit la page quand l'IA a terminé ────────────────────
// Appelle /results/{id}/status toutes les 5s. Dès que ai_ready = true,
// recharge les props Inertia (sans rechargement complet de page).
let pollTimer = null

// FE-06 — timeout polling après 5 min (60 × 5s)
const pollCount = ref(0)
const pollTimeout = ref(false)

const startPolling = () => {
    if (!props.ai_pending) return

    pollTimer = setInterval(async () => {
        // FE-06 — guard timeout
        pollCount.value++
        if (pollCount.value >= 60) {
            clearInterval(pollTimer)
            pollTimer = null
            pollTimeout.value = true
            return
        }

        try {
            const res  = await fetch(route('results.status', props.attempt.id))
            const data = await res.json()

            if (data.ai_ready) {
                clearInterval(pollTimer)
                // Recharge uniquement result + ai_pending via Inertia (pas de full reload)
                router.reload({ only: ['result', 'ai_pending'] })
            }
        } catch {
            // Réseau indisponible — on réessaiera au prochain tick
        }
    }, 5000) // toutes les 5 secondes
}

onMounted(startPolling)
onUnmounted(() => clearInterval(pollTimer))

const scoring = computed(() => props.result?.scoring ?? {})

/**
 * Construit la liste des dimensions à afficher, en privilégiant
 * les données étalonnées (norm_scores) quand elles existent.
 * Format unifié : [{ key, label, norm, pct, dots, color, rawPct }]
 */
const dimensions = computed(() => {
    const s = scoring.value
    if (!s) return null

    const normScores = s.norm_scores ?? null

    // ── Helper : normalise une entrée norm_scores ─────────────────
    const fromNorm = (key, normEntry, label) => ({
        key,
        label:   label ?? key,
        norm:    normEntry?.label    ?? null,
        pct:     normEntry?.percentile ? Math.round((normEntry.percentile / 99) * 100) : null,
        dots:    normEntry?.dots     ?? null,
        color:   normEntry?.color    ?? 'slate',
        rawPct:  null,
    })

    // ── RIASEC / PraxiValeurs : dimensions = { R: number, ... } ──
    if (s.dimensions && typeof Object.values(s.dimensions)[0] === 'number') {
        const meta = s.types_meta ?? {}    // RIASEC labels
        const dimsMeta = s.meta ?? {}      // Schwartz labels
        return Object.entries(s.dimensions).map(([k, raw]) => {
            const label = meta[k]?.label ?? dimsMeta[k]?.label ?? k
            const norm  = normScores?.[k] ?? null
            return {
                key: k, label,
                norm:   norm?.label   ?? null,
                pct:    norm?.percentile ? Math.round((norm.percentile / 99) * 100) : null,
                dots:   norm?.dots    ?? null,
                color:  norm?.color   ?? 'slate',
                rawPct: Math.round(raw),
            }
        })
    }

    // ── BigFive : scores_dim = { O: { T, pct, niveau, label }, ... } ──
    if (s.scores_dim) {
        return Object.entries(s.scores_dim).map(([k, v]) => {
            const norm = normScores?.[k] ?? null
            return {
                key: k,
                label:  v.label ?? k,
                norm:   norm?.label   ?? null,
                pct:    norm?.percentile ? Math.round((norm.percentile / 99) * 100) : Math.round(v.pct ?? 50),
                dots:   norm?.dots    ?? null,
                color:  norm?.color   ?? 'slate',
                rawPct: Math.round(v.pct ?? 50),
            }
        })
    }

    // ── EQi : dim_scores = { dimId: rawScore (5-20) } ────────────
    if (s.dim_scores) {
        const meta = s.meta_dimensions ?? {}
        return Object.entries(s.dim_scores).map(([k, raw]) => {
            const norm = normScores?.[k] ?? null
            return {
                key: k,
                label:  meta[k]?.label ?? k,
                norm:   norm?.label   ?? null,
                pct:    norm?.percentile ? Math.round((norm.percentile / 99) * 100) : Math.round(((raw - 5) / 15) * 100),
                dots:   norm?.dots    ?? null,
                color:  norm?.color   ?? 'slate',
                rawPct: Math.round(((raw - 5) / 15) * 100),
            }
        })
    }

    return null
})

// Couleurs CSS par niveau d'étalonnage
const dotColor = (color) => ({
    gold:  'var(--pt-gold)',
    navy:  'var(--pt-navy)',
    slate: '#94A3B8',
    amber: '#F59E0B',
    muted: '#D1D5DB',
}[color] ?? '#94A3B8')

const hasNorms = computed(() =>
    dimensions.value?.some(d => d.norm !== null) ?? false
)
</script>

<template>
    <CandidateLayout>
        <Head title="Vos résultats" />

        <div style="max-width:780px;margin:0 auto">
            <!-- En-tête -->
            <div class="text-center mb-10">
                <span class="pt-badge mb-3">Profil cartographié</span>
                <h1 class="mt-2" style="font-size:32px">Voilà ce qui vous ressemble.</h1>
                <p style="font-size:14px;color:var(--pt-text-muted);margin-top:8px">
                    Synthèse personnalisée par notre IA, à partir de vos réponses et de votre CV.
                </p>
            </div>

            <!-- FE-06 — message si polling timeout (> 5 min) -->
            <div v-if="pollTimeout" class="pt-card p-6 border-l-4 border-amber-400 bg-amber-50 mb-6">
                <p class="text-sm text-amber-900 font-medium">La synthèse IA prend plus de temps que prévu.</p>
                <p class="text-sm text-amber-700 mt-1">Reviens dans quelques minutes, elle sera disponible dans ton historique.</p>
            </div>

            <!-- En attente IA — polling actif toutes les 5s -->
            <div v-if="ai_pending && !pollTimeout" class="pt-card" style="padding:4rem 2rem;text-align:center">
                <!-- Spinner doré -->
                <div style="width:48px;height:48px;border-radius:50%;border:3px solid var(--pt-cream-dark);border-top-color:var(--pt-gold);animation:spin 1.2s linear infinite;margin:0 auto"></div>

                <!-- Titre -->
                <h2 style="font-family:'Playfair Display',serif;font-size:20px;font-weight:500;margin-top:1.5rem;color:var(--pt-text)">
                    Votre analyse est en cours de génération
                </h2>

                <!-- Étapes -->
                <div style="display:flex;flex-direction:column;gap:.5rem;max-width:320px;margin:1.25rem auto 0;text-align:left">
                    <div style="display:flex;align-items:center;gap:10px;font-size:13px;color:var(--pt-text-muted)">
                        <span style="color:var(--pt-gold)">✓</span> Scoring calculé
                    </div>
                    <div style="display:flex;align-items:center;gap:10px;font-size:13px;color:var(--pt-text-muted)">
                        <div style="width:14px;height:14px;border-radius:50%;border:2px solid var(--pt-cream-dark);border-top-color:var(--pt-gold);animation:spin 1s linear infinite;flex-shrink:0"></div>
                        Synthèse personnalisée en cours…
                    </div>
                    <div style="display:flex;align-items:center;gap:10px;font-size:13px;color:var(--pt-text-light)">
                        <span style="width:14px;height:14px;border-radius:50%;background:var(--pt-cream-dark);display:inline-block;flex-shrink:0"></span>
                        Sélection des 15 métiers
                    </div>
                </div>

                <p style="font-size:12px;color:var(--pt-text-light);margin-top:1.5rem">
                    Cette page se met à jour automatiquement. Pas besoin de la recharger.
                </p>
            </div>

            <template v-else>

                <!-- Synthèse IA -->
                <section class="pt-card p-8 mb-6">
                    <h2 style="font-size:16px;font-weight:500;margin-bottom:1rem">Votre synthèse</h2>
                    <div style="font-size:15px;line-height:1.75;color:var(--pt-text);white-space:pre-line">{{ result.ai_synthesis }}</div>
                </section>

                <!-- Dimensions étalonnées -->
                <section v-if="dimensions?.length" class="pt-card p-8 mb-6">
                    <div style="display:flex;align-items:baseline;justify-content:space-between;margin-bottom:1.25rem">
                        <h2 style="font-size:16px;font-weight:500">Vos dimensions</h2>
                        <span v-if="hasNorms" style="font-size:11px;color:var(--pt-text-light);font-style:italic">
                            Comparé à une population de référence française
                        </span>
                    </div>

                    <div style="display:flex;flex-direction:column;gap:1rem">
                        <div v-for="dim in dimensions" :key="dim.key">
                            <div style="display:flex;align-items:center;gap:12px;margin-bottom:6px">
                                <!-- Nom de la dimension -->
                                <span style="font-size:13px;font-weight:500;min-width:140px;text-transform:capitalize">
                                    {{ dim.label }}
                                </span>

                                <!-- Barre de progression (sur le percentile) -->
                                <div class="pt-progress-track" style="flex:1">
                                    <div class="pt-progress-fill" :style="{ width: (dim.pct ?? dim.rawPct ?? 50) + '%' }"></div>
                                </div>

                                <!-- Étalonnage : dots + label OU chiffre brut en fallback -->
                                <div v-if="dim.norm" style="display:flex;align-items:center;gap:8px;flex-shrink:0;min-width:220px">
                                    <!-- 5 points -->
                                    <div style="display:flex;gap:3px">
                                        <div v-for="n in 5" :key="n"
                                            style="width:9px;height:9px;border-radius:50%;transition:background .2s"
                                            :style="{ background: n <= dim.dots ? d