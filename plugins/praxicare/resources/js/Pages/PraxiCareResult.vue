<script setup>
import { computed } from 'vue'
import CandidateLayout from '@/Layouts/CandidateLayout.vue'
import SynthesisCard from '@/Components/SynthesisCard.vue'

const props = defineProps({ attempt: Object, result: Object })
const scoring = computed(() => props.result?.scoring ?? {})
const karasek = computed(() => scoring.value.karasek ?? {})
const mbi = computed(() => scoring.value.mbi ?? {})
const profile = computed(() => scoring.value.profile)
const meta = computed(() => scoring.value.meta_profiles?.[profile.value] ?? {})

const severityColor = {
    faible: 'text-emerald-700 bg-emerald-50',
    modere: 'text-amber-700 bg-amber-50',
    eleve:  'text-rose-700 bg-rose-50',
}
const severityLabel = { faible: 'Faible', modere: 'Modéré', eleve: 'Élevé' }
const sevHex = { faible: '#16a34a', modere: '#d97706', eleve: '#dc2626' }

/* ─── Quadrant Karasek (demandes × latitude) ─────────────────────────
 * X = latitude décisionnelle (marge de manœuvre), Y = demandes psycho.
 * Seuils du moteur de scoring : demandes ≥ 22 = élevé · latitude > 21 = élevé.
 * Repère tracé sur une grille 0..36. */
const QX0 = 64, QY0 = 36, QW = 312, QH = 300   // zone de tracé
const SCALE = 36
const TH_DEM = 22, TH_LAT = 21

const qx = v => QX0 + (Math.max(0, Math.min(SCALE, v)) / SCALE) * QW
const qy = v => QY0 + QH - (Math.max(0, Math.min(SCALE, v)) / SCALE) * QH

const thX = computed(() => qx(TH_LAT))
const thY = computed(() => qy(TH_DEM))
const ptX = computed(() => qx(karasek.value.latitude ?? 0))
const ptY = computed(() => qy(karasek.value.demandes ?? 0))

const quadrants = computed(() => {
    const m = scoring.value.meta_profiles ?? {}
    const tint = (hex, a) => {
        const h = (hex || '#999').replace('#', '')
        const r = parseInt(h.slice(0, 2), 16), g = parseInt(h.slice(2, 4), 16), b = parseInt(h.slice(4, 6), 16)
        return `rgba(${r},${g},${b},${a})`
    }
    const c = k => m[k]?.color ?? '#999'
    return [
        { key: 'tendu',   label: 'Tendu',    x: QX0,        y: QY0,        w: thX.value - QX0,        h: thY.value - QY0,        fill: tint(c('tendu'), 0.13),   text: c('tendu') },
        { key: 'actif',   label: 'Actif',    x: thX.value,  y: QY0,        w: QX0 + QW - thX.value,   h: thY.value - QY0,        fill: tint(c('actif'), 0.13),   text: c('actif') },
        { key: 'passif',  label: 'Passif',   x: QX0,        y: thY.value,  w: thX.value - QX0,        h: QY0 + QH - thY.value,   fill: tint(c('passif'), 0.13),  text: c('passif') },
        { key: 'detendu', label: 'Détendu',  x: thX.value,  y: thY.value,  w: QX0 + QW - thX.value,   h: QY0 + QH - thY.value,   fill: tint(c('detendu'), 0.13), text: c('detendu') },
    ]
})

/* ─── Jauges zonées MBI ───────────────────────────────────────────────
 * Thresholds alignés sur KarasekMbiScoringEngine::severite(). */
const BW = 300, BH = 22   // largeur/hauteur barre (viewBox)
const mbiItems = computed(() => ([
    { key: 'ee', label: 'Épuisement émotionnel',  desc: 'Plus le score est élevé, plus tu es vidé·e émotionnellement par ton travail.',       t1: 8, t2: 13 },
    { key: 'dp', label: 'Dépersonnalisation',      desc: 'Plus le score est élevé, plus tu prends de la distance émotionnelle avec les autres.', t1: 2, t2: 4 },
    { key: 'ap', label: 'Accomplissement personnel', desc: 'Plus le score est élevé, moins tu te sens accompli·e dans ton travail.',            t1: 4, t2: 8 },
].map(it => {
    const max = mbi.value[it.key + '_max'] || 1
    const val = mbi.value[it.key] ?? 0
    const sev = mbi.value[it.key + '_severite'] ?? 'faible'
    const sx = v => (Math.max(0, Math.min(max, v)) / max) * BW
    return { ...it, max, val, sev,
        z1: sx(it.t1), z2: sx(it.t2), full: BW,
        markX: sx(val), color: sevHex[sev] }
})))

/* ─── Soutien social ── */
const soutienPct = computed(() => {
    const m = karasek.value.soutien_max || 1
    return Math.round(((karasek.value.soutien ?? 0) / m) * 100)
})
</script>

<template>
    <CandidateLayout>
        <Head title="Tes résultats — PraxiCare" />

        <div class="max-w-3xl mx-auto">
            <div class="text-center mb-12">
                <span class="pt-badge">PraxiCare · Karasek + MBI</span>
                <h1 class="text-4xl font-semibold tracking-tight mt-4">Ce que ton travail te coûte aujourd'hui.</h1>
                <p class="text-slate-600 mt-2 max-w-xl mx-auto text-sm">Outil d'aide à la prise de conscience. Ne remplace pas un accompagnement humain ni un diagnostic médical.</p>
            </div>

            <!-- Profil Karasek -->
            <section class="pt-card p-8 mb-8 border-l-4" :style="{ borderColor: meta.color }">
                <p class="text-xs uppercase tracking-wide text-slate-400">Profil Karasek</p>
                <h2 class="text-2xl font-semibold mt-1" :style="{ color: meta.color }">{{ scoring.profile_label }}</h2>
                <p class="text-sm text-slate-700 mt-2">{{ meta.desc }}</p>
            </section>

            <!-- Quadrant Karasek -->
            <section class="pt-card p-8 mb-8">
                <h2 class="text-xl font-semibold mb-1">Karasek — Le modèle tension / contrôle</h2>
                <p class="text-sm text-slate-500 mb-6">Ta position selon la pression subie (demandes) et ta marge de manœuvre (latitude).</p>

                <div class="flex justify-center">
                    <svg viewBox="0 0 412 392" class="w-full max-w-md" role="img" aria-label="Quadrant de Karasek">
                        <!-- Quadrants colorés -->
                        <g>
                            <rect v-for="q in quadrants" :key="q.key"
                                  :x="q.x" :y="q.y" :width="q.w" :height="q.h" :fill="q.fill" />
                            <text v-for="q in quadrants" :key="q.key + '-t'"
                                  :x="q.x + q.w / 2" :y="q.y + q.h / 2"
                                  text-anchor="middle" dominant-baseline="middle"
                                  font-family="'Space Grotesk', sans-serif" font-size="13" font-weight="600"
                                  :fill="q.text" :opacity="profile === q.key ? 0.25 : 0.55">{{ q.label }}</text>
                        </g>

                        <!-- Seuils -->
                        <line :x1="thX" :y1="QY0" :x2="thX" :y2="QY0 + QH" stroke="#A67520" stroke-width="1" stroke-dasharray="4 4" opacity="0.5" />
                        <line :x1="QX0" :y1="thY" :x2="QX0 + QW" :y2="thY" stroke="#A67520" stroke-width="1" stroke-dasharray="4 4" opacity="0.5" />

                        <!-- Cadre + axes -->
                        <rect :x="QX0" :y="QY0" :width="QW" :height="QH" fill="none" stroke="rgba(42,30,8,0.18)" stroke-width="1" />

                        <!-- Point candidat (halo + cœur) -->
                        <circle :cx="ptX" :cy="ptY" r="13" :fill="meta.color" opacity="0.18" />
                        <circle :cx="ptX" :cy="ptY" r="7" :fill="meta.color" stroke="#F0E8D4" stroke-width="2" />

                        <!-- Labels d'axes -->
                        <text :x="QX0 + QW / 2" y="384" text-anchor="middle"
                              font-family="'Space Mono', monospace" font-size="11" fill="#6B5A3E" letter-spacing="1">
                            LATITUDE DÉCISIONNELLE →
                        </text>
                        <text :x="20" :y="QY0 + QH / 2" text-anchor="middle"
                              font-family="'Space Mono', monospace" font-size="11" fill="#6B5A3E" letter-spacing="1"
                              :transform="`rotate(-90 20 ${QY0 + QH / 2})`">
                            DEMANDES PSYCHO →
                        </text>
                    </svg>
                </div>

                <!-- Détail chiffré -->
                <div class="grid md:grid-cols-3 gap-5 mt-6 pt-6 border-t border-slate-100">
                    <div v-for="(label, key) in { demandes: 'Demandes psychologiques', latitude: 'Latitude décisionnelle', soutien: 'Soutien social' }" :key="key">
                        <p class="text-sm font-medium text-slate-700">{{ label }}</p>
                        <div class="flex items-baseline gap-1 mt-1">
                            <span class="text-3xl font-semibold" style="font-family: var(--font-data)">{{ karasek[key] }}</span>
                            <span class="text-sm text-slate-400">/ {{ karasek[key + '_max'] }}</span>
                        </div>
                        <div class="pt-progress-track mt-2">
                            <div class="pt-progress-fill" :style="{ width: ((karasek[key] / karasek[key + '_max']) * 100) + '%' }"></div>
                        </div>
                    </div>
                </div>
                <p class="text-xs text-slate-500 mt-4">
                    Soutien social : <span class="font-medium">{{ soutienPct }} %</span> du maximum.
                    Un faible soutien combiné à une forte tension peut faire basculer vers l'<span class="font-medium text-rose-700">iso-strain</span>.
                </p>
            </section>

            <!-- MBI — jauges zonées -->
            <section class="pt-card p-8 mb-8">
                <h2 class="text-xl font-semibold mb-1">MBI — Signes d'épuisement professionnel</h2>
                <p class="text-sm text-slate-500 mb-6">Position de ton score sur l'échelle, des zones <span class="text-emerald-700 font-medium">faible</span> · <span class="text-amber-700 font-medium">modérée</span> · <span class="text-rose-700 font-medium">élevée</span>.</p>

                <div class="space-y-8">
                    <div v-for="item in mbiItems" :key="item.key">
                        <div class="flex justify-between items-start gap-3 mb-2">
                            <div>
                                <p class="font-medium">{{ item.label }}</p>
                                <p class="text-xs text-slate-500 mt-0.5">{{ item.desc }}</p>
                            </div>
                            <span :class="severityColor[item.sev]" class="text-xs px-2 py-1 rounded-full font-medium whitespace-nowrap">{{ severityLabel[item.sev] }}</span>
                        </div>

                        <svg viewBox="0 0 300 40" class="w-full" role="img" :aria-label="`${item.label} : ${item.val} sur ${item.max}`">
                            <defs>
                                <clipPath :id="'mbi-' + item.key">
                                    <rect x="0" y="14" width="300" height="12" rx="6" />
                                </clipPath>
                            </defs>
                            <!-- Zones (extrémités arrondies via clip) -->
                            <g :clip-path="`url(#mbi-${item.key})`">
                                <rect x="0"        y="14" :width="item.z1"            height="12" fill="rgba(22,163,74,0.18)" />
                                <rect :x="item.z1" y="14" :width="item.z2 - item.z1"  height="12" fill="rgba(217,119,6,0.20)" />
                                <rect :x="item.z2" y="14" :width="item.full - item.z2" height="12" fill="rgba(220,38,38,0.20)" />
                            </g>
                            <!-- séparateurs de seuils -->
                            <line :x1="item.z1" y1="11" :x2="item.z1" y2="29" stroke="#F0E8D4" stroke-width="1.5" />
                            <line :x1="item.z2" y1="11" :x2="item.z2" y2="29" stroke="#F0E8D4" stroke-width="1.5" />

                            <!-- Marqueur du score -->
                            <line :x1="item.markX" y1="8" :x2="item.markX" y2="32" :stroke="item.color" stroke-width="2.5" stroke-linecap="round" />
                            <circle :cx="item.markX" cy="8" r="4.5" :fill="item.color" stroke="#F0E8D4" stroke-width="1.5" />
                        </svg>

                        <div class="flex justify-between items-baseline mt-1">
                            <span class="text-xs text-slate-400" style="font-family: var(--font-data)">0</span>
                            <span class="text-sm">
                                <span class="text-xl font-semibold" :style="{ color: item.color, fontFamily: 'var(--font-data)' }">{{ item.val }}</span>
                                <span class="text-slate-400"> / {{ item.max }}</span>
                            </span>
                            <span class="text-xs text-slate-400" style="font-family: var(--font-data)">{{ item.max }}</span>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Synthèse IA -->
            <SynthesisCard :source="attempt.result?.ai_synthesis" title="Ta synthèse" />

            <div class="text-center mt-12">
                <a :href="route('results.pdf', attempt.id)" class="pt-btn-ghost">Télécharger en PDF</a>
            </div>
        </div>
    </CandidateLayout>
</template>
