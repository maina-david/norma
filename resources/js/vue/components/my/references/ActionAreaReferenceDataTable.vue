<script setup>
import { computed } from 'vue';
import DataTable from '@/vue/components/data-table/DataTable.vue';
import TableColumn from '@/vue/components/data-table/classes/TableColumn';
import TitleColumn from '@/vue/components/my/references/TitleColumn.vue';

const props = defineProps({
  actionAreaId: { type: [Number, String], required: true },
  referenceId: { type: [Number, String], default: '' },
  filterable: { type: Boolean, default: false },
  fixedQueryFilters: { type: Object, default: () => ({}) },
  groupUsing: { type: Object, default: null },
  hasSubRow: { type: Boolean, default: false },
  registerRefreshHandler: { type: Function, default: null },
  searchable: { type: Boolean, default: false },
  sortable: { type: Boolean, default: false },
});

const columns = [
  new TableColumn({
    name: 'title',
    label: 'Title',
    align: 'left',
    component: () => TitleColumn,
  }),
];

const updatedColumns = computed(() => {
  if (!props.hasSubRow) {
    return columns;
  }

  return columns.map((item) => {
    if (item.name === 'title') {
      item.href = null;
    }

    return item;
  });
});
</script>

<template>
  <DataTable
    :columns="updatedColumns"
    :filterable="filterable"
    :group-using="groupUsing"
    :has-sub-row="hasSubRow"
    :register-refresh-handler="registerRefreshHandler"
    :searchable="searchable"
    :sortable="sortable"
    :fixed-query-filters="fixedQueryFilters"
    default-sort="title"
    empty-icon="gavel"
    :endpoint="`/actions/${actionAreaId}/references/${referenceId}`"
    title="requirements"
  >
    <template #dataRow="{ row, rowIndex }">
      <slot name="dataRow" :row="row" :row-index="rowIndex" />
    </template>

    <template #subRow="{ row, rowIndex }">
      <slot name="subRow" :row="row" :row-index="rowIndex" />
    </template>
  </DataTable>
</template>
