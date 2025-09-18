<script setup>
import { ref } from 'vue';
import { useAxios } from '@/vue/composables/useAxios';

const props = defineProps({
  referenceId: { type: [String, Number], required: true },
});

const axios = useAxios();
const loading = ref(false);
const actionAreas = ref([]);

function fetchContent() {
  loading.value = true;

  axios.get(`/references/${props.referenceId}/action-areas`)
    .then(({ data }) => data)
    .then(({ data }) => {
      actionAreas.value = data;
    })
    .finally(() => {
      loading.value = false;
    });
}

fetchContent();
</script>

<template>
  <div v-loading="loading">
    <div class="wysiwyg-content norma-legislation">
      <div v-for="item in actionAreas" :key="item.id">
        <p class="text-primary">
          {{ item.title }}
        </p>
      </div>
    </div>
  </div>
</template>
