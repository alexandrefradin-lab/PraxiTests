<script setup>
/**
 * RadarChart — toile d'araignée PraxiQuest via Chart.js / vue-chartjs.
 *
 * Usage :
 *   <RadarChart :axes="[
 *      { label: 'Écoute', value: 72, color: '#A67520' },
 *      { label: 'Assertivité', value: 54 },
 *   ]" :max="100" />
 *
 * - 3 à 12 axes supportés.
 * - `value` exprimée sur l'échelle 0..max (défaut 100).
 * - `color` par axe : optionnelle (teinte le point et le label).
 */
import { computed } from 'vue'
import { Radar } from 'vue-chartjs'
import {
    Chart as ChartJS,
    RadialLinearScale,
    PointElement,
    LineElement,
    Filler,
    Tooltip,
} from 'chart.js'

ChartJS.register(RadialLinearScale, PointElement, LineElement, Filler, Tooltip)

const props = defineProps({
    axes:       { type: Array,  default: () => [] },
    max:        { type: Number, default: 100 },
    accent:     { type: String, default: '#A67520' },
    showValues: { type: Boolean, default: true },
    dark:       { type: Boolean, default: false },   // rendu sur panneau sombre (constellation)
})

const DEFAULT_COLOR = '#A67520'
// Palette adaptée au fond (clair = parchemin / sombre = encre)
const pal = computed(() => props.dark ? {
    line:        '#E6BE5A',
    area:        'rgba(230,190,90,0.24)',
    defaultPt:   '#E6BE5A',
    pointBorder: 'rgba(18,12,4,0.85)',
    grid:        'rgba(225,185,95,0.42)',
    angle:       'rgba(225,185,95,0.50)',
    tick:        'rgba(240,232,212,0.70)',
    backdrop:    'transparent',
    labelStroke: 'rgba(18,12,4,0.85)',
} : {
    line:        props.accent,
    area:        'rgba(166,117,32,0.11)',
    defaultPt:   '#A67520',
    pointBorder: 'rgba(234,226,210,0.95)',
    grid:        'rgba(80,60,20,0.09)',
    angle:       'rgba(80,60,20,0.11)',
    tick:        'rgba(90,70,20,0.50)',
    backdrop:    'rgba(234,226,210,0.70)',
    labelStroke: 'rgba(234,226,210,0.9)',
})

// Plugin inline : affiche la valeur numérique au bout de chaque point
const valueLabelsPlugin = {
    id: 'praxiValueLabels',
    afterDatasetsDraw(chart) {
        if (!props.showValues) return
        const { ctx, scales: { r } } = chart
        const ds = chart.data.datasets[0]
        props.axes.forEach((ax, i) => {
            const val = ds.data[i]
            if (val < 4) return
            const pt  = r.getPointPosition(i, r.getDistanceFromCenterForValue(val))
            const cx  = r.xCenter, cy = r.yCenter
            const dx  = pt.x - cx,  dy = pt.y - cy
            const len = Math.sqrt(dx * dx + dy * dy) || 1
            const off = 18 / len
            const nx  = pt.x + dx * off
            const ny  = pt.y + dy * off
            ctx.save()
            ctx.font           = 'bold 10.5px monospace'
            ctx.textAlign      = 'center'
            ctx.textBaseline   = 'middle'
            ctx.strokeStyle    = pal.value.labelStroke
            ctx.lineWidth      = 3
            ctx.strokeText(val, nx, ny)
            ctx.fillStyle      = ax.color || pal.value.defaultPt
            ctx.fillText(val, nx, ny)
            ctx.restore()
        })
    },
}

const chartData = computed(() => ({
    labels: props.axes.map(a => a.label),
    datasets: [{
        data:                 props.axes.map(a => a.value ?? 0),
        backgroundColor:      pal.value.area,
        borderColor:          pal.value.line,
        borderWidth:          props.dark ? 3 : 2.5,
        borderJoinStyle:      'round',
        pointBackgroundColor: props.axes.map(a => a.color || pal.value.defaultPt),
        pointBorderColor:     pal.value.pointBorder,
        pointBorderWidth:     2,
        pointRadius:          5,
        pointHoverRadius:     7,
    }],
}))

const chartOptions = computed(() => ({
    responsive:          true,
    maintainAspectRatio: true,
    scales: {
        r: {
            min: 0,
            max: props.max,
            ticks: {
                stepSize:      props.max / 4,
                font:          { size: 9 },
                color:         pal.value.tick,
                backdropColor: pal.value.backdrop,
            },
            pointLabels: {
                font:    { size: 13, weight: '600', family: "'Space Grotesk','Inter',system-ui,sans-serif" },
                color:   props.axes.map(a => a.color || pal.value.defaultPt),
                padding: 8,
            },
            grid:       { color: pal.value.grid },
            angleLines: { color: pal.value.angle },
        },
    },
    plugins: {
        legend: { display: false },
        tooltip: {
            callbacks: {
                label: (ctx) => ` ${props.axes[ctx.dataIndex]?.label ?? ''} : ${ctx.raw}/${props.max}`,
            },
        },
    },
}))

const plugins = [valueLabelsPlugin]
</script>

<template>
    <div class="rc-wrap">
        <div class="rc-constellation" aria-hidden="true"></div>
        <Radar :data="chartData" :options="chartOptions" :plugins="plugins" />
    </div>
</template>

<style scoped>
.rc-wrap {
    position: relative;
    width: 100%;
    max-width: 480px;
    margin: 0 auto;
}
/* Fond « constellation » : halo or central + fines étoiles dorées */
.rc-constellation {
    position: absolute;
    inset: -4%;
    pointer-events: none;
    z-index: 0;
    background:
        radial-gradient(2px 2px at 20% 26%, rgba(201,144,48,0.55), transparent),
        radial-gradient(1.5px 1.5px at 78% 30%, rgba(201,144,48,0.45), transparent),
        radial-gradient(2px 2px at 66% 76%, rgba(201,144,48,0.45), transparent),
        radial-gradient(1.5px 1.5px at 30% 74%, rgba(201,144,48,0.40), transparent),
        radial-gradient(1.5px 1.5px at 50% 12%, rgba(201,144,48,0.40), transparent),
        radial-gradient(circle at 50% 46%, rgba(166,117,32,0.10), transparent 62%);
}
.rc-wrap :deep(canvas) {
    position: relative;
    z-index: 1;
}
</style>
