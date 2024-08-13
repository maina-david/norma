<script setup>
import { onMounted, onUnmounted, ref } from 'vue';
import { Chart } from 'chart.js';

const props = defineProps({
  datasets: { type: Array, default: null },
  labels: { type: Array, default: null },
});

const chartEl = ref(null);
const chart = ref(null);

function getChatConfig() {
  return {
    type: 'bar',
    options: {
      maintainAspectRatio: false,
      scales: {
        y: {
          beginAtZero: true,
        },
      },
    },
    data: {
      labels: props.labels,
      datasets: props.datasets,
    },
  };
}
onMounted(() => {
  window.setTimeout(() => {
    chart.value = new Chart(chartEl.value.getContext('2d'), getChatConfig());
  }, 300);
});

onUnmounted(() => {
  chart.value?.destroy();
});
</script>

<template>
  <canvas ref="chartEl" />
</template>
