<script setup>
import { inject, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { useAxios } from '@/vue/composables/useAxios';
import BarChart from '@/vue/components/BarChart.vue';

const { t } = useI18n({ useScope: 'global' });
const registerRefreshHandler = inject('registerRefreshHandler');
const getAppliedFilters = inject('getAppliedFilters');
const loading = ref(true);
const datasets = ref([]);
const axios = useAxios();
const labels = ref([]);

function fetchItems() {
  loading.value = true;

  axios.get('/actions/metrics/creation-completion', { params: getAppliedFilters() })
    .then(({ data }) => data)
    .then(({ data }) => {
      labels.value = data.labels;
      datasets.value = [
        {
          label: t('actions.dashboard.tasks_created'),
          data: data.created,
          backgroundColor: window.getComputedStyle(document.body).getPropertyValue('--norma-gray-400'),
          borderColor: '',
        },
        {
          label: t('actions.dashboard.tasks_done'),
          data: data.completed,
          backgroundColor: window.getComputedStyle(document.body).getPropertyValue('--positive'),
          borderColor: '',
        },
      ];
    })
    .finally(() => {
      loading.value = false;
    });
}

registerRefreshHandler(fetchItems, 'completion_bar_chart');

fetchItems();

</script>

<template>
  <div v-loading="loading">
    <BarChart v-if="!loading" class="h-full" :labels="labels" :datasets="datasets" />
  </div>
</template>
