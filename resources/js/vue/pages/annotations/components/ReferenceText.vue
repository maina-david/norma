<script setup>
import { ref } from 'vue';
import { useAxios } from '@/vue/composables/useAxios';

const axios = useAxios();
const props = defineProps({
  reference: { type: Object, required: true },
});

const content = ref('');

axios.get(`/references/${props.reference.id}/content`)
  .then(({ data }) => {
    content.value = data.live ?? data.draft?.html_content ?? '';
  });
</script>

<template>
  <div class="p-4 norma-legislation">
    <div v-if="content.length > 0" class="relative" v-html="content" />
  </div>
</template>
