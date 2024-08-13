<script setup>
import { ref } from 'vue';
import { useAxios } from '@/vue/composables/useAxios';

const axios = useAxios();
const props = defineProps({
  reference: { type: Object, required: true },
});

const extracts = ref([]);

axios.get(`/references/${props.reference.id}/content/extracts`)
  .then(({ data }) => {
    extracts.value = data.data.map((item) => {
      item.content = item.content.replace(/\n/g, '<br>');

      return item;
    });
  });
</script>

<template>
  <div class="p-4 libryo-legislation pl-8">
    <ul class="list-disc">
      <li v-for="extract in extracts" :key="extract.id" v-html="extract.content" />
    </ul>
  </div>
</template>
