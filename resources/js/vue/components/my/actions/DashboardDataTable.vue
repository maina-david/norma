<script setup>
import { useI18n } from 'vue-i18n';
import DataTable from '@/vue/components/data-table/DataTable.vue';
import filters from '@/Pages/Actions/My/Task/definitions/filters';
import TableColumn from '@/vue/components/data-table/classes/TableColumn';
import TableDownload from '@/vue/components/data-table/classes/TableDownload';

const { t } = useI18n({ useScope: 'global' });
defineProps({
  filterable: { type: Boolean, default: false },
  fixedQueryFilters: { type: Object, default: () => ({}) },
  groupUsing: { type: Object, default: null },
  hasSubRow: { type: Boolean, default: false },
  registerRefreshHandler: { type: Function, default: null },
  searchable: { type: Boolean, default: false },
  sortable: { type: Boolean, default: false },
});

const mappedFilters = filters.map((item) => ({ ...item, name: `task_${item.name}` }));
const downloads = [
  new TableDownload({
    type: 'excel',
    icon: 'file-excel',
  }),
];

const columns = [
  new TableColumn({
    name: 'title',
    label: t('actions.dashboard.columns.title'),
    align: 'left',
    minWidth: '40rem',
    sortable: true,
  }),
  new TableColumn({
    name: 'total_tasks',
    label: t('actions.dashboard.columns.total_in_progress_tasks'),
  }),
  new TableColumn({
    name: 'total_in_progress_tasks',
    label: t('actions.dashboard.columns.total_in_progress_tasks'),
  }),
  new TableColumn({
    name: 'total_not_started_tasks',
    label: t('actions.dashboard.columns.total_not_started_tasks'),
  }),
  new TableColumn({
    name: 'overdue_tasks',
    label: t('actions.dashboard.columns.overdue_tasks'),
  }),
  new TableColumn({
    name: 'completed_total_impact',
    label: t('actions.dashboard.columns.completed_total_impact'),
  }),
  new TableColumn({
    name: 'incomplete_total_impact',
    label: t('actions.dashboard.columns.incomplete_total_impact'),
  }),
];
</script>

<template>
  <DataTable
    :actions="[]"
    :columns="columns"
    :downloads="downloads"
    :filterable="filterable"
    :filters="mappedFilters"
    :group-using="groupUsing"
    :has-sub-row="hasSubRow"
    :register-refresh-handler="registerRefreshHandler"
    :searchable="searchable"
    :sortable="sortable"
    :fixed-query-filters="fixedQueryFilters"
    default-sort="title"
    empty-icon="tasks"
    endpoint="/actions/tasks/dashboard"
    title="tasks"
  >
    <template #dataRow="{ row, rowIndex }">
      <slot name="dataRow" :row="row" :row-index="rowIndex" />
    </template>

    <template #subRow="{ row, rowIndex }">
      <slot name="subRow" :row="row" :row-index="rowIndex" />
    </template>
  </DataTable>
</template>
