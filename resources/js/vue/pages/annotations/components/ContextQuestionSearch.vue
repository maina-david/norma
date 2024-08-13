<script setup>
import { computed, ref } from 'vue';
import RemoteSearch from '@/vue/pages/annotations/components/RemoteSearch.vue';

const props = defineProps({
  categories: { type: Array, default: null },
  location: { type: Number, default: null },
});

const target = computed(() => {
  const payload = [];

  if (props.categories && props.categories.length > 0) {
    payload.push(`categories=${props.categories.join(',')}`);
  }

  if (props.location) {
    payload.push(`location_id=${props.location}`);
  }

  return `/context-questions/json?${payload.join('&')}`;
});

const selector = ref(null);
defineExpose({ reset: () => selector.value.tomselect.clear(), tomselect: () => selector.value.tomselect });
</script>

<template>
  <RemoteSearch
    ref="selector"
    fetch-on-target-change
    :search-field="false"
    :target="target"
    placeholder="Search Context Question..."
  />
</template>
