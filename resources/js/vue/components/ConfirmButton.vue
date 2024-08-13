<script setup>
import AppButton from '@/vue/components/AppButton.vue';
import { confirm } from '@/vue/plugins/bus';
import { useAxios } from '@/vue/composables/useAxios';

const axios = useAxios();

const props = defineProps({
  target: { type: String, default: null },
  title: { type: String, required: true },
  message: { type: String, required: true },
  method: { type: String, required: true },
  payload: { type: Object, default: () => ({}) },
  theme: { type: String, default: 'default' },
  baseUrl: { type: String, default: null },
  noBorder: { type: Boolean, default: false },
});

const emit = defineEmits(['confirm']);

function handleDelete() {
  const payload = {
    url: props.target,
    method: props.method,
    data: props.payload,
  };

  if (props.baseUrl) {
    payload.baseURL = props.baseUrl;
  }

  confirm({ title: props.title, message: props.message })
    .then(() => props.target ? axios.request(payload) : Promise.resolve())
    .then(() => emit('confirm'))
    .catch(() => {});
}
</script>

<template>
  <AppButton :no-border="noBorder" type="button" :theme="theme" @click="handleDelete">
    <slot />
  </AppButton>
</template>
