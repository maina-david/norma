<script setup>
import ConfirmButton from '@/vue/components/ConfirmButton.vue';

defineProps({
  baseUrl: { type: String, default: null },
  target: { type: String, default: null },
  message: { type: String, default: 'Are you sure you want to delete? This action is not reversible!' },
  noBorder: { type: Boolean, default: false },
});

const emit = defineEmits(['delete']);

</script>

<template>
  <ConfirmButton
    theme="danger"
    title="Delete"
    method="delete"
    :message="message"
    :target="target"
    :base-url="baseUrl"
    :no-border="noBorder"
    @confirm="() => emit('delete')"
  >
    <slot />
  </ConfirmButton>
</template>
