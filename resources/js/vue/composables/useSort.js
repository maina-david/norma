import { ref } from 'vue';

export const useSort = ({ defaultSort, onSorted }) => {
  const sortedColumn = ref(defaultSort ?? '');
  const sortedDirection = ref('asc');

  function changeSort(column) {
    if (sortedColumn.value === column) {
      sortedDirection.value = sortedDirection.value === 'asc'  ? 'desc' : 'asc';
      onSorted();
      return;
    }

    sortedDirection.value = 'asc';
    sortedColumn.value = column;
    onSorted();
  }

  function getSortQueryParams() {
    const params = [];

    if (sortedDirection.value === 'desc') {
      params.push('-');
    }

    params.push(sortedColumn.value);

    return { sort: params.join('') };
  }

  return {
    changeSort,
    sortedColumn,
    sortedDirection,
    getSortQueryParams,
  };
};
