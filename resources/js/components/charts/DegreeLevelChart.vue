<script setup lang="ts">
import { Bar } from 'vue-chartjs'
import {
    Chart as ChartJS,
    CategoryScale,
    LinearScale,
    BarElement,
    Title,
    Tooltip,
    Legend,
} from 'chart.js'
import { computed } from 'vue'

ChartJS.register(CategoryScale, LinearScale, BarElement, Title, Tooltip, Legend)

const props = defineProps<{
    data: Record<string, number>
}>()

const chartData = computed(() => ({
    labels: Object.keys(props.data),
    datasets: [
        {
            label: 'Graduates',
            data: Object.values(props.data),
            backgroundColor: 'rgba(168, 85, 247, 0.8)',
            borderColor: 'rgb(168, 85, 247)',
            borderWidth: 1,
            borderRadius: 8,
        },
    ],
}))

const chartOptions = computed(() => ({
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            display: false,
        },
        title: {
            display: false,
        },
        tooltip: {
            backgroundColor: 'rgba(0, 0, 0, 0.8)',
            padding: 12,
            titleFont: {
                size: 14,
            },
            bodyFont: {
                size: 13,
            },
            borderColor: 'rgba(255, 255, 255, 0.1)',
            borderWidth: 1,
        },
    },
    scales: {
        x: {
            grid: {
                display: false,
            },
            ticks: {
                font: {
                    size: 11,
                },
            },
        },
        y: {
            beginAtZero: true,
            grid: {
                color: 'rgba(0, 0, 0, 0.05)',
            },
            ticks: {
                font: {
                    size: 11,
                },
            },
        },
    },
}))
</script>

<template>
    <div class="h-[300px] w-full">
        <Bar :data="chartData" :options="chartOptions" />
    </div>
</template>
