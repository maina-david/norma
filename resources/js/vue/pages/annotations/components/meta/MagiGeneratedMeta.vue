<script setup>
import {onMounted, ref} from 'vue';
import {useAxios} from '@/vue/composables/useAxios';

const emit = defineEmits(['select']);
const props = defineProps({
  meta: { type: String, required: true },
  reference: { type: Object, required: true },
});
const axios = useAxios();
const suggested = ref([]);
const loading = ref(true);

onMounted(() => {
  const routes = {
    assessmentItems: `/reference/${props.reference.id}/suggest/assessment-items`,
    categories: `/reference/${props.reference.id}/suggest/categories`,
    contextQuestions: `/reference/${props.reference.id}/suggest/context-questions`,
    legalDomains: `/reference/${props.reference.id}/suggest/legal-domains`,
    actionAreas: `/reference/${props.reference.id}/suggest/action-areas`,
  };

  if (!routes[props.meta]) {
    loading.value = false;
    return;
  }

  axios.get(routes[props.meta])
    .then(({ data }) => data)
    .then(({ data }) => {
      suggested.value = [...data];
    })
    .finally(() => {
      loading.value = false;
    });
});

</script>

<template>
  <div v-loading="loading" class="text-xs space-y-1 flex flex-col items-start pb-8">
    <div v-for="item in suggested" :key="item.id" class="rounded-full bg-primary-lighter text-white font-semibold px-2 py-0.5 cursor-pointer hover:bg-primary-darker" @click="() => emit('select', item)">
      {{ item.title }}
    </div>
  </div>
</template>
