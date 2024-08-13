<script setup>
import { computed } from 'vue';
import AppIcon from '@/vue/components/AppIcon.vue';

const props = defineProps({
  mimeType: { type: String, default: '' },
});

const icon = computed(() => {
  switch (props.mimeType) {
  case 'image/jpeg':
  case 'image/png':
    return 'image';
  case 'application/pdf':
    return 'file-pdf';
  case 'application/vnd.ms-excel':
  case 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet':
    return 'file-excel';
  case 'application/vnd.openxmlformats-officedocument.wordprocessingml.document':
  case 'application/msword':
    return 'file-word';
  case 'application/zip':
  case 'application/octet-stream':
  case 'application/x-rar-compressed':
    return 'file-archive';
  case 'text/plain':
    return 'file-alt';
  default:
    return 'file';
  }
});

</script>

<template>
  <AppIcon :name="icon" />
</template>
