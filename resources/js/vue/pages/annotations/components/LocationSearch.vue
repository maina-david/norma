<script setup>
import { ref } from 'vue';
import RemoteSearch from '@/vue/pages/annotations/components/RemoteSearch.vue';

const selector = ref(null);
defineExpose({ reset: () => selector.value.tomselect.clear(), tomselect: () => selector.value.tomselect });
</script>

<template>
  <RemoteSearch ref="selector" :search-field="false" target="/locations/json" placeholder="Search Jurisdiction..." />
</template>
