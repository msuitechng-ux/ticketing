<script setup lang="ts">
import { Doughnut } from 'vue-chartjs'
import {
    Chart as ChartJS,
    ArcElement,
    Tooltip,
    Legend,
} from 'chart.js'
import { computed } from 'vue'

ChartJS.register(ArcElement, Tooltip, Legend)

const props = defineProps<{
    data: Record<string, number>
}>()

const chartData = computed(() => ({
    labels: Object.keys(props.data),
    datasets: [
        {
            data: Object.values(props.data),
            backgroundColor: [
                'rgba(251, 146, 60, 0.8)', // Pending - Orange
                'rgba(34, 197, 94, 0.8)',  // Approved - Green
                'rgba(239, 68, 68, 0.8)',  // Denied - Red
                'rgba(59, 130, 246, 0.8)', // Waitlisted - Blue
                'rgba(253, 186, 116, 0.8)', // Partially Approved - Light Orange
            ],
            borderColor: [
                'rgb(251, 146, 60)',
                'rgb(34, 197, 94)',
                'rgb(239, 68, 68)',
                'rgb(59, 130, 246)',
                'rgb(253, 186, 116)',
            ],
            borderWidth: 2,
        },
    ],
}))

const chartOptions = computed(() => ({
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            position: 'right' as const,
            labels: {
                font: {
                    size: 12,
                    family: 'Inter, system-ui, sans-serif',
                },
                padding: 15,
                usePointStyle: true,
            },
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
            callbacks: {
                label: function(context: any) {
                    const label = context.label || '';
                    const value = context.parsed || 0;
                    const total = context.dataset.data.reduce((a: number, b: number) => a + b, 0);
                    const percentage = ((value / total) * 100).toFixed(1);
                    return `${label}: ${value} (${percentage}%)`;
                },
            },
        },
    },
}))
</script>

<template>
    <div class="h-[300px] w-full">
        <Doughnut :data="chartData" :options="chartOptions" />
    </div>
</template>
