<script setup>
import { inject, ref } from 'vue';
import { getComputedBackgroundColour, getLabel, TaskStatus } from '@/enums/actions/tasks/task-statuses';
import PieChart from '@/vue/components/PieChart.vue';
import { useAxios } from '@/vue/composables/useAxios';

const labels = [
  getLabel(TaskStatus.notStarted),
  getLabel(TaskStatus.inProgress),
  getLabel(TaskStatus.done),
  getLabel(TaskStatus.paused),
];

const background = [
  getComputedBackgroundColour(TaskStatus.notStarted),
  getComputedBackgroundColour(TaskStatus.inProgress),
  getComputedBackgroundColour(TaskStatus.done),
  getComputedBackgroundColour(TaskStatus.paused),
];

const registerRefreshHandler = inject('registerRefreshHandler');
const getAppliedFilters = inject('getAppliedFilters');
const loading = ref(true);
const datasets = ref([]);
const axios = useAxios();

function fetchItems() {
  loading.value = true;

  axios.get('/actions/metrics/statuses', { params: getAppliedFilters() })
    .then(({ data }) => data)
    .then(({ data }) => {
      datasets.value = [
        { data, backgroundColor: background, hoverOffset: 4 },
      ];
    })
    .finally(() => {
      loading.value = false;
    });
}

registerRefreshHandler(fetchItems, 'status_pie_chart');

fetchItems();

</script>

<template>
  <div v-loading="loading">
    <PieChart v-if="!loading" class="h-full" :labels="labels" :datasets="datasets" />
  </div>
</template>
