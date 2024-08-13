import { reactive } from 'vue';

export const usePagination = (perPage = 50) => {
  const pagination = reactive({ page: 1, perPage, lastPage: 1 });
  function changePage(page, callback = null) {
    pagination.page = page;
    if (callback) {
      callback();
    }
  }

  function getPaginationQueryParams() {
    return {
      page: pagination.page,
      perPage: pagination.perPage,
    };
  }

  function updatePagination(response) {
    pagination.page = response.current_page;
    pagination.perPage = response.per_page;
    pagination.lastPage = response.last_page;
  }

  return { pagination, changePage, getPaginationQueryParams, updatePagination };
};
