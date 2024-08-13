<script setup>
import { computed, ref } from 'vue';
import RemoteSearch from '@/vue/pages/annotations/components/RemoteSearch.vue';

const props = defineProps({
  location: { type: Number, default: null },
  isActions: { type: Boolean, default: false },
  isAssess: { type: Boolean, default: false },
  isContext: { type: Boolean, default: false },
});

const target = computed(() => {
  const payload = [];

  if (props.location) {
    payload.push(`location_id=${props.location}`);
  }

  if (props.isActions) {
    payload.push('actions=1');
  }

  if (props.isContext) {
    payload.push('context=1');
  }

  if (props.isAssess) {
    payload.push('assess=1');
  }

  return `/categories/json?${payload.join('&')}`;
});

const selector = ref(null);
defineExpose({ reset: () => selector.value.tomselect.clear(), tomselect: () => selector.value.tomselect });
</script>

<template>
  <RemoteSearch ref="selector" :search-field="false" :target="target" placeholder="Search Topic..." />
</template>
