import { ref } from 'vue';
import { useAxios } from '@/vue/composables/useAxios';

export const useTaskModel = () => {
  const axios = useAxios();
  const loading = ref(false);

  function updateField(id, field, value) {
    loading.value = true;

    return axios.put(`/actions/tasks/${id}`, { [field]: value })
      .then(({ data }) => data.data)
      .finally(() => {
        loading.value = false;
      });
  }
  return {
    updateField,
    loading,
  };
};
