<script setup>
import { ref } from 'vue';
import { useAxios } from '@/vue/composables/useAxios';

const props = defineProps({
  expression: { type: Object, required: true },
  plainText: { type: String, default: null },
});

const text = ref('');

const axios = useAxios();

function fetchContent() {
  if (props.plainText) {
    axios.get(`${props.plainText}/`, { baseURL: '/' })
      .then((response) => {
        if ((response.headers['content-type'] ?? '').includes('text/plain')) {
          text.value = response.data.split('\n').map((item) => `<div>${item}</div>`).join('');
        }
      });
  }
}

fetchContent();
</script>
<template>
  <div class="flex-grow w-full overflow-hidden libryo-legislation bg-white">
    <div v-if="plainText" class="w-full h-full py-4 px-6 overflow-y-auto" v-html="text" />
    <embed v-else class="w-full h-full" :src="`/work-expressions/${expression.id}/source/content`">
  </div>
</template>
