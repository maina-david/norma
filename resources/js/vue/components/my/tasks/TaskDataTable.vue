<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import DataTable from '@/vue/components/data-table/DataTable.vue';
import columns from '@/Pages/Actions/My/Task/definitions/columns';
import filters from '@/Pages/Actions/My/Task/definitions/filters';
import actions from '@/Pages/Actions/My/Task/definitions/actions';
import downloads from '@/Pages/Actions/My/Task/definitions/downloads';

const props = defineProps({
  filterable: { type: Boolean, default: false },
  fixedQueryFilters: { type: Object, default: () => ({}) },
  groupUsing: { type: Object, default: null },
  hasSubRow: { type: Boolean, default: false },
  registerRefreshHandler: { type: Function, default: null },
  searchable: { type: Boolean, default: false },
  sortable: { type: Boolean, default: false },
});

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
    :actions="actions"
    :columns="updatedColumns"
    :downloads="downloads"
    :filterable="filterable"
    :filters="filters"
    :group-using="groupUsing"
    :has-sub-row="hasSubRow"
    :register-refresh-handler="registerRefreshHandler"
    :searchable="searchable"
    :sortable="sortable"
    :fixed-query-filters="fixedQueryFilters"
    default-sort="title"
    empty-icon="tasks"
    endpoint="/actions/tasks"
    title="tasks"
    :row-link-element="Link"
  >
    <template #dataRow="{ row, rowIndex }">
      <slot name="dataRow" :row="row" :row-index="rowIndex" />
    </template>

    <template #subRow="{ row, rowIndex }">
      <slot name="subRow" :row="row" :row-index="rowIndex" />
    </template>
  </DataTable>
</template>
