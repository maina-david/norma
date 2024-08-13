import { ref, watch } from 'vue';
import { useAxios } from '@/vue/composables/useAxios';
import { usePagination } from './usePagination';
import { debounce } from 'lodash';
import { ReferenceVisibility } from '@/vue/pages/annotations/composables/useReferenceVisibility';
import { useState } from '@/vue/composables/useState';

export const useReferences = (workId, taskId, referenceVisibility) => {
  const [loading, setLoading] = useState(false);
  const { pagination, changePage, getPaginationQueryParams, updatePagination } = usePagination();
  const axios = useAxios();

  const items = ref([]);
  function getIds() {
    return items.value.map((ref) => ref.id);
  }

  const fetchAll = debounce(() => {
    setLoading(true);
    const params = { ...getPaginationQueryParams() };

    Object.keys(referenceVisibility).forEach((key) => {
      if (referenceVisibility[key] !== ReferenceVisibility.ALL) {
        params[key] = referenceVisibility[key];
      }
    });

    axios.get(`/work/${workId}/references/task/${taskId}`, { params })
      .then(({ data }) => {
        updatePagination(data.meta);

        items.value = [...data.data];
      })
      .finally(() => setLoading(false));
  }, 1000);

  function fetchOne(referenceId, params = {}) {
    setLoading(true);

    Object.keys(referenceVisibility).forEach((key) => {
      if (referenceVisibility[key] !== ReferenceVisibility.ALL) {
        params[key] = referenceVisibility[key];
      }
    });

    return axios.get(`/work/${workId}/references/${referenceId}/task/${taskId}`, { params })
      .then(({ data }) => {
        const index = items.value.findIndex((ref) => ref.id === data.data.id);

        if (index !== -1) {
          items.value.splice(index, 1, { ...items.value[index], ...data });
        }

        return data;
      })
      .finally(() => setLoading(false));
  }

  // when the visibility is changed, fetch the references.
  watch(referenceVisibility, () => fetchAll());

  return {
    items,
    fetchAll,
    fetchOne,
    getIds,
    pagination,
    loading,
    changePage: async (page) => {
      changePage(page);
      await fetchAll();
    },
  };
};
