import { ref } from 'vue';
import { debounce } from 'lodash';
import { useAxios } from '@/vue/composables/useAxios';
import { usePagination } from '@/vue/pages/annotations/composables/usePagination';

export function useFetchModels({ endpoint, getAppliedFilters, getSortQueryParams, perPage = 50 }) {
  const { pagination, changePage, getPaginationQueryParams, updatePagination } = usePagination(perPage);
  const axios = useAxios();
  const rows = ref([]);
  const loadingRows = ref(true);

  const fetchRows = debounce(() =>  {
    const params = { filters: [] , has: [], ...getSortQueryParams(), ...getPaginationQueryParams(), ...getAppliedFilters() };

    (params.filters ?? []).forEach((item, index) => {
      if (item.startsWith('has:')) {
        const has = item.replace('has:', '').replace('eq', '=');
        params.has.push(has);
        delete params.filters[index];
      }
    });

    loadingRows.value = true;

    return axios.get(endpoint, { params })
      .then(({ data }) => {
        updatePagination(data.meta);

        rows.value = [...data.data];
      })
      .finally(() => {
        loadingRows.value = false;
      });
  }, 100);

  function updateRow(index, newRow) {
    const cloned = [...rows.value];
    cloned[index] = { ...cloned[index], ...newRow };
    rows.value = [...cloned];
  }

  function handlePageChange(page) {
    return changePage(page, () => fetchRows());
  }

  return {
    pagination,
    changePage: handlePageChange,
    fetchRows,
    updateRow,
    loadingRows,
    rows,
  };
}
