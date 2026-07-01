<script setup>
import { computed } from 'vue'
import CandidateLayout from '@/Layouts/CandidateLayout.vue'
import SynthesisCard from '@/Components/SynthesisCard.vue'
import RestitutionHeader from '@/Components/RestitutionHeader.vue'
import ResultPanel from '@/Components/ResultPanel.vue'
import ResultPdfButton from '@/Components/ResultPdfButton.vue'

const props = defineProps({
    attempt:      Object,
    result:       Object,
})
const scoring = computed(() => props.result?.scoring ?? {})
const karasek = computed(() => scoring.value.karasek ?? {})
const mbi = computed(() => scoring.value.mbi ?? {})
const profile = computed(() => scoring.value.profile)
const meta = computed(() => scoring.value.meta_profiles?.[profile.value] ?? {})

const severityLabel = { faible: 'Faible', modere: 'Modéré', eleve: 'Élevé' }
const sevHex = { faible: '#16a34a', modere: '#d97706', eleve: '#dc2626' }
// Variantes éclaircies pour fond sombre (panneaux constellation)
const sevHexDark = { faible: '#4ade80', modere: '#fbbf24', eleve: '#f87171' }
// Pastille de sévérité lisible sur fond sombre
const severityChipStyle = {
    faible: { color: '#4ade80', background: 'rgba(74,222,128,0.14)', border: '1px solid rgba(74,222,128,0.35)' },
    modere: { color: '#fbbf24', background: 'rgba(251,191,36,0.14)', border: '1px solid rgba(251,191,36,0.35)' },
    eleve:  { color: '#f87171', background: 'rgba(248,113,113,0.14)', border: '1px solid rgba(248,113,113,0.35)' },
}

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
    // Sur fond sombre : zones plus opaques, libellés en ton clair.
    const FILL = 0.22
    return [
        { key: 'tendu',   label: 'Tendu',    x: QX0,        y: QY0,        w: thX.value - QX0,        h: thY.value - QY0,        fill: tint(c('tendu'), FILL),   text: '#F0E8D4' },
        { key: 'actif',   label: 'Actif',    x: thX.value,  y: QY0,        w: QX0 + QW - thX.value,   h: thY.value - QY0,        fill: tint(c('actif'), FILL),   text: '#F0E8D4' },
        { key: 'passif',  label: 'Passif',   x: QX0,        y: thY.value,  w: thX.value - QX0,        h: QY0 + QH - thY.value,   fill: tint(c('passif'), FILL),  text: '#F0E8D4' },
        { key: 'detendu', label: 'Détendu',  x: thX.value,  y: thY.value,  w: QX0 + QW - thX.value,   h: QY0 + QH - thY.value,   fill: tint(c('detendu'), FILL), text: '#F0E8D4' },
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
        markX: sx(val), color: sevHexDark[sev] }
})))

/* ─── Définitions neutres des 3 facteurs Karasek ── */
const karasekDef = {
    demandes: "Charge mentale et pression ressenties au travail.",
    latitude: "Marge d'autonomie et de décision au travail.",
    soutien:  "Appui reçu des collègues et de la hiérarchie.",
}

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
            <RestitutionHeader
                kicker="PraxiCare · Karasek + MBI"
                title="Ce que ton travail te coûte aujourd'hui."
                subtitle="Outil d'aide à la prise de conscience. Ne remplace pas un accompagnement humain ni un diagnostic médical."
            />

            <!-- Profil Karasek -->
            <section class="pt-card ac-card-ornate ac-card-dark p-8 mb-8 border-l-4" :style="{ borderColor: meta.color }">
                <p class="text-xs uppercase tracking-wide text-slate-400">Profil Karasek</p>
                <h2 class="text-2xl font-semibold mt-1" :style="{ color: meta.color }">{{ scoring.profile_label }}</h2>
                <p class="text-sm text-slate-700 mt-2">{{ meta.desc }}</p>
            </section>

            <!-- Quadrant Karasek -->
            <ResultPanel label="Ton positionnement (Karasek)" class="mb-8">
                <h2 class="ac-panel-title mb-1">Karasek — Le modèle tension / contrôle</h2>
                <p class="ac-dark-muted text-sm mb-6">Ta position selon la pression subie (demandes) et ta marge de manœuvre (latitude).</p>

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
                                  :fill="q.text" :opacity="profile === q.key ? 1 : 0.7">{{ q.label }}</text>
                        </g>

                        <!-- Seuils (filets dorés) -->
                        <line :x1="thX" :y1="QY0" :x2="thX" :y2="QY0 + QH" stroke="var(--color-primary)" stroke-width="1" stroke-dasharray="4 4" opacity="0.55" />
                        <line :x1="QX0" :y1="thY" :x2="QX0 + QW" :y2="thY" stroke="var(--color-primary)" stroke-width="1" stroke-dasharray="4 4" opacity="0.55" />

                        <!-- Cadre + axes -->
                        <rect :x="QX0" :y="QY0" :width="QW" :height="QH" fill="none" stroke="rgba(230,190,90,0.30)" stroke-width="1" />

                        <!-- Point candidat (halo doux + cœur fin) -->
                        <circle :cx="ptX" :cy="ptY" r="12" :fill="meta.color" opacity="0.18" />
                        <circle :cx="ptX" :cy="ptY" r="6.5" :fill="meta.color" opacity="0.32" />
                        <circle :cx="ptX" :cy="ptY" r="4" :fill="meta.color" stroke="#F0E8D4" stroke-width="1.5" />
                        <circle :cx="ptX" :cy="ptY" r="1.4" fill="#F0E8D4" />

                        <!-- Labels d'axes -->
                        <text :x="QX0 + QW / 2" y="384" text-anchor="middle"
                              font-family="'Space Mono', monospace" font-size="11" fill="#C9B589" letter-spacing="1">
                            LATITUDE DÉCISIONNELLE →
                        </text>
                        <text :x="20" :y="QY0 + QH / 2" text-anchor="middle"
                              font-family="'Space Mono', monospace" font-size="11" fill="#C9B589" letter-spacing="1"
                              :transform="`rotate(-90 20 ${QY0 + QH / 2})`">
                            DEMANDES PSYCHO →
                        </text>
                    </svg>
                </div>

                <!-- Détail chiffré : 3 scores -->
                <div class="grid md:grid-cols-3 gap-5 mt-6 pt-6" style="border-top:1px solid rgba(230,190,90,0.20)">
                    <div v-for="(label, key) in { demandes: 'Demandes psychologiques', latitude: 'Latitude décisionnelle', soutien: 'Soutien social' }" :key="key" class="ac-dark-item">
                        <p class="ac-dark-name">{{ label }}</p>
                        <div class="flex items-baseline gap-1 mt-1">
                            <span class="text-3xl font-semibold" style="font-family: var(--font-data); color:#F4ECD8">{{ karasek[key] }}</span>
                            <span class="ac-dark-muted text-sm">/ {{ karasek[key + '_max'] }}</span>
                        </div>
                        <div class="ac-dark-track mt-2">
                            <div :style="{ width: ((karasek[key] / karasek[key + '_max']) * 100) + '%', background: 'var(--color-primary)' }"></div>
                        </div>
                        <p class="ac-dark-def">{{ karasekDef[key] }}</p>
                    </div>
                </div>
                <p class="ac-dark-muted text-xs mt-4">
                    Soutien social : <span class="font-medium" style="color:#F0E8D4">{{ soutienPct }} %</span> du maximum.
                    Un faible soutien combiné à une forte tension peut faire basculer vers l'<span class="font-medium" style="color:#f87171">iso-strain</span>.
                </p>
            </ResultPanel>

            <!-- MBI — jauges zonées -->
            <ResultPanel class="mb-8">
                <h2 class="ac-panel-title mb-1">MBI — Signes d'épuisement professionnel</h2>
                <p class="ac-dark-muted text-sm mb-6">Position de ton score sur l'échelle, des zones <span class="font-medium" style="color:#4ade80">faible</span> · <span class="font-medium" style="color:#fbbf24">modérée</span> · <span class="font-medium" style="color:#f87171">élevée</span>.</p>

                <div class="space-y-6">
                    <div v-for="item in mbiItems" :key="item.key" class="ac-dark-item">
                        <div class="flex justify-between items-start gap-3 mb-2">
                            <div>
                                <p class="ac-dark-name">{{ item.label }}</p>
                                <p class="ac-dark-def" style="margin-top:0.35rem">{{ item.desc }}</p>
                            </div>
                            <span class="text-xs px-2 py-1 rounded-full font-medium whitespace-nowrap" :style="severityChipStyle[item.sev]">{{ severityLabel[item.sev] }}</span>
                        </div>

                        <svg viewBox="0 0 300 26" class="w-full" role="img" :aria-label="`${item.label} : ${item.val} sur ${item.max}`">
                            <defs>
                                <clipPath :id="'mbi-' + item.key">
                                    <rect x="0" y="12" width="300" height="5" rx="2.5" />
                                </clipPath>
                            </defs>
                            <!-- Zones douces (extrémités arrondies via clip) -->
                            <g :clip-path="`url(#mbi-${item.key})`">
                                <rect x="0"        y="12" :width="item.z1"            height="5" fill="rgba(74,222,128,0.32)" />
                                <rect :x="item.z1" y="12" :width="item.z2 - item.z1"  height="5" fill="rgba(251,191,36,0.34)" />
                                <rect :x="item.z2" y="12" :width="item.full - item.z2" height="5" fill="rgba(248,113,113,0.34)" />
                            </g>
                            <!-- séparateurs de seuils (fins) -->
                            <line :x1="item.z1" y1="10.5" :x2="item.z1" y2="18.5" stroke="rgba(16,11,4,0.5)" stroke-width="1" />
                            <line :x1="item.z2" y1="10.5" :x2="item.z2" y2="18.5" stroke="rgba(16,11,4,0.5)" stroke-width="1" />

                            <!-- Marqueur du score (fin + halo discret) -->
                            <circle :cx="item.markX" cy="14.5" r="7" :fill="item.color" opacity="0.14" />
                            <line :x1="item.markX" y1="6" :x2="item.markX" y2="23" :stroke="item.color" stroke-width="1.5" stroke-linecap="round" />
                            <circle :cx="item.markX" cy="6" r="3.1" :fill="item.color" stroke="#1b1206" stroke-width="1.25" />
                        </svg>

                        <div class="flex justify-between items-baseline mt-1">
                            <span class="ac-dark-muted text-xs" style="font-family: var(--font-data)">0</span>
                            <span class="text-sm">
                                <span class="text-xl font-semibold" :style="{ color: item.color, fontFamily: 'var(--font-data)' }">{{ item.val }}</span>
                                <span class="ac-dark-muted"> / {{ item.max }}</span>
                            </span>
                            <span class="ac-dark-muted text-xs" style="font-family: var(--font-data)">{{ item.max }}</span>
                        </div>
                    </div>
                </div>
            </ResultPanel>

            <!-- Synthèse IA -->
            <SynthesisCard :source="attempt.result?.ai_synthesis" title="Ta synthèse" />

            <ResultPdfButton :attempt-id="attempt.id" />
        </div>
    </CandidateLayout>
</template>
