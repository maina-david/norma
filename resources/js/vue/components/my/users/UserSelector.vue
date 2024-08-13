<script setup>
import { useAxios } from '@/vue/composables/useAxios';
import SelectElement from '@/vue/components/SelectElement.vue';
import useRootPersist from '@/vue/composables/useRootPersist';

const value = defineModel();
const axios = useAxios();

const { stored, loading } = useRootPersist({
  key: 'user_filter',
  defaultValue: [],
  fetchData() {
    return axios.get('/organisation/users')
      .then(({ data }) => data)
      .then(({ data }) => data.map((item) => ({ value: item.id, label: item.name })));
  },
});

</script>

<template>
  <SelectElement v-if="!loading" v-model="value" :options="stored" />
</template>
