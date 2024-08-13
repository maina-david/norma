<script setup>
import { useAxios } from '@/vue/composables/useAxios';
import SelectElement from '@/vue/components/SelectElement.vue';
import useRootPersist from '@/vue/composables/useRootPersist';

const value = defineModel();
const axios = useAxios();

const { stored, loading } = useRootPersist({
  key: 'subject_category_filters',
  defaultValue: [],
  fetchData() {
    return axios.get('/categories/subjects')
      .then(({ data }) => data)
      .then(({ data }) => data);
  },
});

</script>

<template>
  <SelectElement v-if="!loading" v-model="value" :options="stored" />
</template>
