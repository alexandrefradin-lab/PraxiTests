<script setup>
/**
 * Page résultats — PraxiBiais (biais cognitifs professionnels)
 *
 * RÈGLE : n'utiliser que les classes pt-* et les variables var(--pt-*).
 * Ne jamais hardcoder de couleurs Tailwind (bg-indigo-600, etc.).
 */
import { computed, ref } from 'vue'
import { Link } from '@inertiajs/vue3'
import CandidateLayout from '@/Layouts/CandidateLayout.vue'
import SynthesisCard from '@/Components/SynthesisCard.vue'
import MarkdownText from '@/Components/MarkdownText.vue'

const props = defineProps({
    attempt: Object,
    result:  Object,
})

const scoring  = computed(() => props.result?.scoring ?? {})
const scores   = computed(() => scoring.value.scores ?? {})
const top3     = computed(() => scoring.value.top3 ?? [])
const profile  = computed(() => scoring.value.profile ?? {})

// Biais triés par score décroissant pour le tableau complet
const allBiais = computed(() =>
    Object.values(scores.value).sort((a, b) => b.score - a.score)
)

// Carte dépliée
const expanded = ref(null)
const toggle   = (slug) => { expanded.value = expanded.value === slug ? null : slug }

// Couleur selon niveau
const levelColor = (slug) => ({
    critical: '#dc2626',
    high:     '#ea580c',
    moderate: 'var(--pt-gold)',
    low:      '#64748b',
}[slug] ?? '#64748b')

const levelBg = (slug) => ({
    critical: 'rgba(220,38,38,.1)',
    high:     'rgba(234,88,12,.1)',
    moderate: 'rgba(var(--pt-gold-rgb, 212,160,23),.12)',
    low:      'rgba(100,116,139,.1)',
}[slug] ?? 'rgba(100,116,139,.1)')

// Couleur de la barre de score (0–100 → rouge→or→vert)
const barColor = (score) => {
    if (score >= 80) return '#dc2626'
    if (score >= 65) return '#ea580c'
    if (score >= 35) return 'var(--pt-gold)'
    return '#64748b'
}

// Emoji profil
const profileEmoji = computed(() => ({
    prisonnier_passe:      '🏰',
    suradaptation_sociale: '🪞',
    faux_rationnel:        '🔬',
    oscillant:             '⚖️',
    mixte:                 '🧩',
}[profile.value.slug] ?? '🧠'))
</script>

<template>
    <CandidateLayout>
        <Head title="Tes résultats — Le Cartographe Mental" />

        <div style="max-width:800px;margin:0 auto">

            <!-- En-tête ───────────────────────────────────────────────────── -->
            <div style="text-align:center;margin-bottom:2rem">
                <span class="pt-badge" style="margin-bottom:.75rem">Le Cartographe Mental</span>
                <h1 style="font-size:26px;font-weight:700;margin-top:6px;color:var(--pt-text)">
                    Vos biais cognitifs professionnels
                </h1>
                <p style="font-size:14px;color:var(--pt-text-muted);margin-top:6px;line-height:1.5">
                    10 biais analysés · 40 questions · Scoring direct / inverse / scénario
                </p>
            </div>

            <!-- Profil composite ──────────────────────────────────────────── -->
            <div class="pt-card" style="padding:1.75rem;margin-bottom:1.5rem;border-left:4px solid var(--pt-gold)">
                <div style="display:flex;align-items:flex-start;gap:1rem">
                    <span style="font-size:2.5rem;line-height:1">{{ profileEmoji }}</span>
                    <div style="flex:1">
                        <p style="font-size:11px;text-transform:uppercase;letter-spacing:.08em;color:var(--pt-text-light);margin-bottom:4px">
                            Votre profil cognitif dominant
                        </p>
                        <h2 style="font-size:20px;font-weight:700;color:var(--pt-text);margin-bottom:.75rem">
                            {{ profile.label }}
                        </h2>
                        <p style="font-size:14px;color:var(--pt-text-muted);line-height:1.65">
                            {{ profile.desc }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Top 3 biais ───────────────────────────────────────────────── -->
            <h2 style="font-size:16px;font-weight:600;color:var(--pt-text);margin-bottom:1rem">
                Vos 3 freins décisionnels principaux
            </h2>

            <div style="display:flex;flex-direction:column;gap:.75rem;margin-bottom:2rem">
                <div
                    v-for="(biais, i) in top3"
                    :key="biais.slug"
                    class="pt-card"
                    style="padding:1.25rem 1.5rem;cursor:pointer;transition:box-shadow .15s"
                    @click="toggle(biais.slug)"
                >
                    <div style="display:flex;align-items:center;gap:1rem">
                        <!-- Rang -->
                        <div style="width:32px;height:32px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:700;flex-shrink:0"
                             :style="{ background: i === 0 ? 'var(--pt-gold)' : 'var(--pt-surface-2)', color: i === 0 ? '#fff' : 'var(--pt-text)' }">
                            {{ i + 1 }}
                        </div>

                        <!-- Nom + teaser -->
                        <div style="flex:1;min-width:0">
                            <div style="display:flex;align-items:center;gap:.5rem;flex-wrap:wrap;margin-bottom:4px">
                                <span style="font-size:15px;font-weight:600;color:var(--pt-text)">{{ biais.label }}</span>
                                <span style="font-size:11px;padding:2px 8px;border-radius:999px;font-weight:600"
                                      :style="{ background: levelBg(biais.level.slug), color: levelColor(biais.level.slug) }">
                                    {{ biais.level.label }}
                                </span>
                            </div>
                            <p style="font-size:13px;color:var(--pt-text-muted);line-height:1.4;margin:0">
                                {{ biais.teaser }}
                            </p>
                        </div>

                        <!-- Score + barre -->
                        <div style="text-align:right;min-width:60px">
                            <span style="font-size:20px;font-weight:700" :style="{ color: levelColor(biais.level.slug) }">
                                {{ biais.score }}<small style="font-size:12px;font-weight:400;color:var(--pt-text-light)">/100</small>
                            </span>
                        </div>
                    </div>

                    <!-- Barre de score -->
                    <div style="height:4px;background:var(--pt-surface-2);border-radius:2px;margin-top:.875rem;overflow:hidden">
                        <div style="height:100%;border-radius:2px;transition:width .6s ease"
                             :style="{ width: biais.score + '%', background: barColor(biais.score) }"></div>
                    </div>

                    <!-- Détails dépliables -->
                    <div v-if="expanded === biais.slug && biais.details"
                         style="margin-top:1.25rem;padding-top:1.25rem;border-top:1px solid var(--pt-border)">
                        <div style="display:grid;gap:1rem">
                            <div>
                                <p style="font-size:11px;text-transform:uppercase;letter-spacing:.06em;color:var(--pt-text-light);margin-bottom:4px">Ce qui se passe dans votre cerveau</p>
                                <p style="font-size:13px;color:var(--pt-text-muted);line-height:1.6">{{ biais.details.definition }}</p>
                            </div>
                            <div>
                                <p style="font-size:11px;text-transform:uppercase;letter-spacing:.06em;color:var(--pt-text-light);margin-bottom:4px">Comment ça se manifeste</p>
                                <p style="font-size:13px;color:var(--pt-text-muted);line-height:1.6">{{ biais.details.manifestation }}</p>
                            </div>
                            <div>
                                <p style="font-size:11px;text-transform:uppercase;letter-spacing:.06em;color:var(--pt-text-light);margin-bottom:4px">Le coût invisible</p>
                                <p style="font-size:13px;color:var(--pt-text-muted);line-height:1.6">{{ biais.details.cout }}</p>
                            </div>
                            <div style="background:var(--pt-surface-2);border-radius:8px;padding:.875rem 1rem">
                                <p style="font-size:11px;text-transform:uppercase;letter-spacing:.06em;color:var(--pt-gold);margin-bottom:4px">✦ Piste d'action</p>
                                <p style="font-size:13px;color:var(--pt-text);line-height:1.6;font-style:italic">{{ biais.details.piste }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Indicateur déplier -->
                    <p style="font-size:12px;color:var(--pt-text-light);margin-top:.5rem;text-align:right">
                        {{ expanded === biais.slug ? '▲ Réduire' : '▼ Voir le détail' }}
                    </p>
                </div>
            </div>

            <!-- Carte complète des 10 biais ──────────────────────────────── -->
            <h2 style="font-size:16px;font-weight:600;color:var(--pt-text);margin-bottom:1rem">
                Cartographie de vos 10 biais
            </h2>

            <div class="pt-card" style="padding:1.5rem;margin-bottom:1.5rem">
                <div style="display:flex;flex-direction:column;gap:.875rem">
                    <div v-for="biais in allBiais" :key="biais.slug"
                         style="display:flex;align-items:center;gap:.75rem">

                        <!-- Nom -->
                        <div style="min-width:180px;flex:1">
                            <span style="font-size:13px;font-weight:500;color:var(--pt-text)">{{ biais.label }}</span>
                        </div>

                        <!-- Barre -->
                        <div style="flex:2;height:8px;background:var(--pt-surface-2);border-radius:4px;overflow:hidden">
                            <div style="height:100%;border-radius:4px;transition:width .6s ease"
                                 :style="{ width: biais.score + '%', background: barColor(biais.score) }"></div>
                        </div>

                        <!-- Score + niveau -->
                        <div style="min-width:120px;display:flex;align-items:center;gap:.5rem">
                            <span style="font-size:13px;font-weight:600;min-width:36px;text-align:right"
                                  :style="{ color: levelColor(biais.level.slug) }">
                                {{ biais.score }}
                            </span>
                            <span style="font-size:11px;padding:2px 6px;border-radius:999px;white-space:nowrap"
                                  :style="{ background: levelBg(biais.level.slug), color: levelColor(biais.level.slug) }">
                                {{ biais.level.label }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Légende -->
                <div style="margin-top:1.25rem;padding-top:1rem;border-top:1px solid var(--pt-border);display:flex;flex-wrap:wrap;gap:.75rem">
                    <span v-for="(label, slug) in { critical: 'Blocage majeur ≥80', high: 'Frein significatif ≥65', moderate: 'Tendance active ≥35', low: 'Angle mort discret <35' }"
                          :key="slug"
                          style="font-size:11px;padding:3px 8px;border-radius:999px"
                          :style="{ background: levelBg(slug), color: levelColor(slug) }">
                        {{ label }}
                    </span>
                </div>
            </div>

            <!-- Tous les biais (détails dépliables) ─────────────────────── -->
            <h2 style="font-size:16px;font-weight:600;color:var(--pt-text);margin-bottom:1rem">
                Comprendre chaque biais
            </h2>

            <div style="display:flex;flex-direction:column;gap:.5rem;margin-bottom:2rem">
                <div
                    v-for="biais in allBiais"
                    :key="'detail-' + biais.slug"
                    class="pt-card"
                    style="padding:1rem 1.25rem;cursor:pointer"
                    @click="toggle('detail-' + biais.slug)"
                >
                    <div style="display:flex;align-items:center;justify-content:space-between;gap:1rem">
                        <div style="display:flex;align-items:center;gap:.75rem">
                            <span style="font-size:14px;font-weight:600;color:var(--pt-text)">{{ biais.label }}</span>
                            <span style="font-size:11px;padding:2px 7px;border-radius:999px"
                                  :style="{ background: levelBg(biais.level.slug), color: levelColor(biais.level.slug) }">
                                {{ biais.score }}/100
                            </span>
                        </div>
                        <span style="font-size:12px;color:var(--pt-text-light);flex-shrink:0">
                            {{ expanded === 'detail-' + biais.slug ? '▲' : '▼' }}
                        </span>
                    </div>

                    <div v-if="expanded === 'detail-' + biais.slug && biais.details"
                         style="margin-top:1rem;padding-top:1rem;border-top:1px solid var(--pt-border)">
                        <div style="display:grid;gap:.875rem">
                            <div>
                                <p style="font-size:11px;text-transform:uppercase;letter-spacing:.06em;color:var(--pt-text-light);margin-bottom:3px">Mécanisme</p>
                                <p style="font-size:13px;color:var(--pt-text-muted);line-height:1.6">{{ biais.details.definition }}</p>
                            </div>
                            <div>
                                <p style="font-size:11px;text-transform:uppercase;letter-spacing:.06em;color:var(--pt-text-light);margin-bottom:3px">Manifestation</p>
                                <p style="font-size:13px;color:var(--pt-text-muted);line-height:1.6">{{ biais.details.manifestation }}</p>
                            </div>
                            <div>
                                <p style="font-size:11px;text-transform:uppercase;letter-spacing:.06em;color:var(--pt-text-light);margin-bottom:3px">Coût invisible</p>
                                <p style="font-size:13px;color:var(--pt-text-muted);line-height:1.6">{{ biais.details.cout }}</p>
                            </div>
                            <div style="background:var(--pt-surface-2);border-radius:8px;padding:.75rem 1rem">
                                <p style="font-size:11px;text-transform:uppercase;letter-spacing:.06em;color:var(--pt-gold);margin-bottom:3px">✦ Piste</p>
                                <p style="font-size:13px;color:var(--pt-text);line-height:1.6;font-style:italic">{{ biais.details.piste }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Synthèse IA ───────────────────────────────────────────────── -->
            <SynthesisCard :attempt="attempt" :result="result" />

        </div>
    </CandidateLayout>
</template>
