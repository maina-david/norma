import { ref, watch } from 'vue';
import { debounce } from 'lodash';
import { useState } from '@/vue/composables/useState';
import { usePagination } from '@/vue/pages/annotations/composables/usePagination';
import { useAxios } from '@/vue/composables/useAxios';

export default function (baseUrl, filters = {}, perPage = 50, unwatchedFilters = {}, debounceSeconds = 500) {
  const [loading, setLoading] = useState(false);
  const { pagination, changePage, getPaginationQueryParams, updatePagination } = usePagination(perPage);
  const axios = useAxios();

  const items = ref([]);
  function getIds() {
    return items.value.map((ref) => ref.id);
  }

  function getBase() {
    return typeof baseUrl === 'function' ? baseUrl() : baseUrl;
  }

  function handleFetch() {
    setLoading(true);
    const params = { ...getPaginationQueryParams(), ...(ref(filters).value), ...(ref(unwatchedFilters).value) };

    return axios.get(getBase(), { params })
      .then(({ data }) => {
        updatePagination(data.meta);

        items.value = [...data.data];
      })
      .finally(() => setLoading(false));
  }

  const fetchAll = debounce((postFetch = null) => {
    return handleFetch().then(() => {
      if (postFetch) {
        postFetch(items.value);
      }
    });
  }, debounceSeconds);

  function fetchOne(itemId, params = {}, target = null) {
    return axios.get(target || `${getBase()}/${itemId}`, { params: ref(params).value })
      .then(({ data }) => {
        const index = items.value.findIndex((one) => one.id === data.data.id);

        if (index !== -1) {
          items.value.splice(index, 1, { ...items.value[index], ...data.data });
        }

        return data.data;
      });
  }

  function update(body, target = null) {
    setLoading(true);

    const payload = ref(body).value;

    return axios.put(target || `${getBase()}/${payload.id}`, payload)
      .then(({ data }) => {
        const index = items.value.findIndex((one) => one.id === data?.data?.id ?? 0);

        if (index !== -1) {
          items.value.splice(index, 1, { ...items.value[index], ...data });
        }

        return data;
      })
      .finally(() => setLoading(false));
  }

  function store(body = {}, target = null) {
    setLoading(true);

    return axios.post(target || getBase(), ref(body).value)
      .then(({ data }) => {
        const index = items.value.findIndex((one) => one.id === data?.data?.id ?? 0);

        if (index !== -1) {
          items.value.splice(index, 1, { ...items.value[index], ...data });
        }

        return data;
      })
      .finally(() => setLoading(false));
  }

  function destroy(body, target = null) {
    setLoading(true);

    const payload = ref(body).value;

    return axios.delete(target || `${getBase()}/${payload.id}`)
      .then(({ data }) => {
        const index = items.value.findIndex((one) => one.id === data?.data?.id ?? 0);

        if (index !== -1) {
          items.value.splice(index, 1);
        }

        return data;
      })
      .finally(() => setLoading(false));
  }

  // when the filters change, fetch the items.
  if (filters.effect) {
    watch(filters, () => fetchAll());
  }

  return {
    items,
    fetchAll,
    fetchOne,
    store,
    update,
    destroy,
    getIds,
    pagination,
    loading,
    changePage: async (page, postFetch = null) => {
      changePage(page);
      await handleFetch(postFetch);
    },
  };
}
