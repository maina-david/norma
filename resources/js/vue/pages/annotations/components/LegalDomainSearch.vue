<script setup>
import { computed, ref } from 'vue';
import RemoteSearch from '@/vue/pages/annotations/components/RemoteSearch.vue';

const props = defineProps({
  location: { type: Number, default: null },
});

const target = computed(() => {
  const payload = [];

  if (props.location) {
    payload.push(`location_id=${props.location}`);
  }

  return `/legal-domains/json?${payload.join('&')}`;
});

const selector = ref(null);
defineExpose({ reset: () => selector.value.tomselect.clear(), tomselect: () => selector.value.tomselect });
</script>

<template>
  <RemoteSearch ref="selector" fetch-on-load :target="target" placeholder="Search Legal Domain..." />
</template>
