import { computed, ref } from 'vue';

export function useTableSelection() {
  const selectedRows = ref({});
  const hasSelectedRows = computed(() => Object.values(selectedRows.value).filter((i) => i).length > 0);

  function toggleSelected(rows, rowKey, unchecked) {
    rows.forEach((row) => {
      selectedRows.value[row[rowKey]] = !unchecked;
    });
  }

  return { selectedRows, hasSelectedRows, toggleSelected };
}
