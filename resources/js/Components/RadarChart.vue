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
})

const DEFAULT_COLOR = '#A67520'

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
            ctx.strokeStyle    = 'rgba(234,226,210,0.9)'
            ctx.lineWidth      = 3
            ctx.strokeText(val, nx, ny)
            ctx.fillStyle      = ax.color || DEFAULT_COLOR
            ctx.fillText(val, nx, ny)
            ctx.restore()
        })
    },
}

const chartData = computed(() => ({
    labels: props.axes.map(a => a.label),
    datasets: [{
        data:                 props.axes.map(a => a.value ?? 0),
        backgroundColor:      'rgba(166,117,32,0.11)',
        borderColor:          props.accent,
        borderWidth:          2.5,
        borderJoinStyle:      'round',
        pointBackgroundColor: props.axes.map(a => a.color || DEFAULT_COLOR),
        pointBorderColor:     'rgba(234,226,210,0.95)',
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
                color:         'rgba(90,70,20,0.50)',
                backdropColor: 'rgba(234,226,210,0.70)',
            },
            pointLabels: {
                font:    { size: 13, weight: '600', family: "'Space Grotesk','Inter',system-ui,sans-serif" },
                color:   props.axes.map(a => a.color || DEFAULT_COLOR),
                padding: 8,
            },
            grid:       { color: 'rgba(80,60,20,0.09)' },
            angleLines: { color: 'rgba(80,60,20,0.11)' },
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
    <div style="position:relative;width:100%;max-width:480px;margin:0 auto">
        <Radar :data="chartData" :options="chartOptions" :plugins="plugins" />
    </div>
</template>
