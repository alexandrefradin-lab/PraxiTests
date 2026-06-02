<script setup>
import { computed, onMounted, ref } from 'vue'
import { Link } from '@inertiajs/vue3'
import CandidateLayout from '@/Layouts/CandidateLayout.vue'
import { Chart, ScatterController, LinearScale, PointElement, Tooltip } from 'chart.js'
Chart.register(ScatterController, LinearScale, PointElement, Tooltip)

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

const quadrantRef = ref(null)

// Seuil réel du moteur Karasek : hautes demandes ≥ 22/36, haute latitude > 21/36
// → pivot à 61 % (et non 50 %) pour que les zones correspondent exactement
// au profil calculé par KarasekMbiScoringEngine.
const KARASEK_PIVOT = Math.round((22 / 36) * 100)   // 61 %

const userX = computed(() => karasek.value.latitude_max
    ? Math.round((karasek.value.latitude / karasek.value.latitude_max) * 100)
    : KARASEK_PIVOT)
const userY = computed(() => karasek.value.demandes_max
    ? Math.round((karasek.value.demandes / karasek.value.demandes_max) * 100)
    : KARASEK_PIVOT)

onMounted(() => {
    if (!quadrantRef.value) return
    const ctx = quadrantRef.value.getContext('2d')
    const pivot = KARASEK_PIVOT

    const quadrantPlugin = {
        id: 'quadrantBg',

        // beforeDraw : remplissages colorés AVANT les éléments du graphique
        // → le point candidat apparaîtra PAR-DESSUS les zones.
        beforeDraw(chart) {
            const { ctx: c, chartArea: { left, right, top, bottom }, scales: { x, y } } = chart
            const midX = x.getPixelForValue(pivot)
            const midY = y.getPixelForValue(pivot)
            c.save()
            c.fillStyle = 'rgba(220, 38, 38, 0.07)';  c.fillRect(left,  top,  midX - left,  midY - top)   // Job strain
            c.fillStyle = 'rgba(22,  163,  74, 0.07)'; c.fillRect(midX,  top,  right - midX, midY - top)   // Job actif
            c.fillStyle = 'rgba(100, 116, 139, 0.06)'; c.fillRect(left,  midY, midX - left,  bottom - midY) // Job passif
            c.fillStyle = 'rgba(37,   99, 235, 0.07)'; c.fillRect(midX,  midY, right - midX, bottom - midY) // Job détendu
            c.strokeStyle = 'rgba(0,0,0,0.10)'; c.lineWidth = 1; c.setLineDash([4, 4])
            c.beginPath(); c.moveTo(midX, top);  c.lineTo(midX, bottom); c.stroke()
            c.beginPath(); c.moveTo(left, midY); c.lineTo(right, midY);  c.stroke()
            c.setLineDash([])
            c.restore()
        },

        // afterDraw : labels APRÈS le rendu → par-dessus gridlines mais sous rien
        afterDraw(chart) {
            const { ctx: c, chartArea: { left, right, top, bottom } } = chart
            c.save()
            c.font = '500 10px sans-serif'
            c.fillStyle = 'rgba(220, 38, 38, 0.70)';  c.textAlign = 'left';  c.textBaseline = 'top';    c.fillText('Job strain',   left + 8, top + 6)
            c.fillStyle = 'rgba(22, 163, 74, 0.70)';  c.textAlign = 'right'; c.textBaseline = 'top';    c.fillText('Job actif',    right - 8, top + 6)
            c.fillStyle = 'rgba(100,116,139, 0.70)';  c.textAlign = 'left';  c.textBaseline = 'bottom'; c.fillText('Job passif',   left + 8, bottom - 6)
            c.fillStyle = 'rgba(37, 99, 235, 0.70)';  c.textAlign = 'right'; c.textBaseline = 'bottom'; c.fillText('Job détendu', right - 8, bottom - 6)
            c.restore()
        }
    }

    new Chart(ctx, {
        type: 'scatter',
        plugins: [quadrantPlugin],
        data: {
            datasets: [{
                data: [{ x: userX.value, y: userY.value }],
                backgroundColor: '#4F46E5',
                borderColor: '#fff',
                borderWidth: 2.5,
                pointRadius: 11,
                pointHoverRadius: 13,
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: () => `Autonomie : ${userX.value}%  ·  Demandes : ${userY.value}%`
                    }
                }
            },
            scales: {
                x: {
                    min: 0, max: 100,
                    title: { display: true, text: 'Autonomie (latitude décisionnelle) →', font: { size: 11 } },
                    grid: { color: 'rgba(0,0,0,0.04)' },
                    ticks: { stepSize: 25, callback: v => v + '%' }
                },
                y: {
                    min: 0, max: 100,
                    title: { display: true, text: '↑ Demandes psychologiques', font: { size: 11 } },
                    grid: { color: 'rgba(0,0,0,0.04)' },
                    tick