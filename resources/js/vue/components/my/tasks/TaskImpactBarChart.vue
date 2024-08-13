<script setup>
import { inject, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { useAxios } from '@/vue/composables/useAxios';
import BarChart from '@/vue/components/BarChart.vue';
import { getComputedBackgroundColour, getLabel, TaskStatus } from '@/enums/actions/tasks/task-statuses';

const { t } = useI18n({ useScope: 'global' });
const registerRefreshHandler = inject('registerRefreshHandler');
const getAppliedFilters = inject('getAppliedFilters');
const loading = ref(true);
const datasets = ref([]);
const axios = useAxios();

const labels = [
  getLabel(TaskStatus.notStarted),
  getLabel(TaskStatus.inProgress),
  getLabel(TaskStatus.done),
  getLabel(TaskStatus.paused),
  t('actions.dashboard.overdue'),
];

const background = [
  getComputedBackgroundColour(TaskStatus.notStarted),
  getComputedBackgroundColour(TaskStatus.inProgress),
  getComputedBackgroundColour(TaskStatus.done),
  getComputedBackgroundColour(TaskStatus.paused),
  getComputedBackgroundColour(TaskStatus.notStarted),
];

function fetchItems() {
  loading.value = true;

  axios.get('/actions/metrics/impact', { params: getAppliedFilters() })
    .then(({ data }) => data)
    .then(({ data }) => {
      datasets.value = [
        { data, backgroundColor: background, label: t('actions.dashboard.columns.total_task_impact_score') },
      ];
    })
    .finally(() => {
      loading.value = false;
    });
}

registerRefreshHandler(fetchItems, 'impact_bar_chart');

fetchItems();

</script>

<template>
  <div v-loading="loading">
    <BarChart v-if="!loading" class="h-full" :labels="labels" :datasets="datasets" />
  </div>
</template>
