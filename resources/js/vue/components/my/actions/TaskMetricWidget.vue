<script setup>
import { computed, inject, ref } from 'vue';
import MetricWidget from '@/vue/components/MetricWidget.vue';
import { useAxios } from '@/vue/composables/useAxios';

const props = defineProps({
  colour: { type: String, default: '' },
  icon: { type: String, default: null },
  title: { type: String, required: true },
  percentage: { type: Boolean, default: false },
  type: { type: String, required: true },
  appliedFilters: { type: String, default: null },
});

const value = ref('...');
const loading = ref(true);
const axios = useAxios();
const registerRefreshHandler = inject('registerRefreshHandler');
const getAppliedFilters = inject('getAppliedFilters');
const target = computed(() => {
  if (props.appliedFilters) {
    return `/actions/view/list?${props.appliedFilters}`;
  }

  return null;
});

function fetchMetrics() {
  loading.value = true;
  axios.get(`/actions/metrics/single/${props.type}`, { params: getAppliedFilters() })
    .then(({ data }) => data)
    .then(({ data }) => {
      const suffix = props.percentage ? '%' : '';
      value.value = `${data.value}${suffix}`;
    })
    .finally(() => {
      loading.value = false;
    });
}

registerRefreshHandler(fetchMetrics, `metric_${props.type}`);
fetchMetrics();
</script>

<template>
  <MetricWidget
    v-loading="loading"
    :colour="colour"
    :icon="icon"
    :title="title"
    :value="value"
    :target="target"
  />
</template>
