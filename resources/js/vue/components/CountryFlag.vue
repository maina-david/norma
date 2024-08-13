<script setup>
import { computed } from 'vue';

const props = defineProps({ countryCode: { type: String, required: true } });

const target = computed(() => {
  const code = props.countryCode.toLowerCase();
  const suffix = code.includes('.') ? '' : '.svg';

  return `/img/flags/${code}${suffix}`;
});
</script>

<template>
  <img :src="target">
</template>
