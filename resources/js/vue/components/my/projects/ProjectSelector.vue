<script setup>
import { useAxios } from '@/vue/composables/useAxios';
import SelectElement from '@/vue/components/SelectElement.vue';
import useRootPersist from '@/vue/composables/useRootPersist';

const value = defineModel();
const axios = useAxios();

const { stored, loading } = useRootPersist({
  key: 'project_filter',
  defaultValue: [],
  fetchData() {
    return axios.get('/actions/projects')
      .then(({ data }) => data)
      .then(({ data }) => data.map((item) => ({ value: item.id, label: item.title })));
  },
});

</script>

<template>
  <SelectElement v-if="!loading" v-model="value" :options="stored" />
</template>
