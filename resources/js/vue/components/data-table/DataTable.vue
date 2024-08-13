<script setup>
import { computed, ref } from 'vue';
import DataTableActions from '@/vue/components/data-table/DataTableActions.vue';
import { useModelFilters } from '@/vue/composables/useModelFilters';
import DataTableSearch from '@/vue/components/data-table/DataTableSearch.vue';
import DataTableFilters from '@/vue/components/data-table/DataTableFilters.vue';
import { useAxios } from '@/vue/composables/useAxios';
import DataTableRows from '@/vue/components/data-table/DataTableRows.vue';
import { useSort } from '@/vue/composables/useSort';
import { useTableSelection } from '@/vue/composables/useTableSelection';
import DataTableGroup from '@/vue/components/data-table/DataTableGroup.vue';
import ExportButton from '@/vue/components/my/ExportButton.vue';

const props = defineProps({
  actions: { type: Array, default: () => [] },
  columns: { type: Array, required: true },
  defaultSort: { type: String, default: 'id' },
  downloads: { type: Array, default: () => [] },
  emptyIcon: { type: String, required: true },
  endpoint: { type: String, required: true },
  filterable: { type: Boolean, default: false },
  filters: { type: Array, default: () => [] },
  fixedQueryFilters: { type: Object, default: () => ({}) },
  groupUsing: { type: Object, default: null },
  hasSubRow: { type: Boolean, default: false },
  registerRefreshHandler: { type: Function, default: null },
  rowKey: { type: String, default: 'id' },
  searchable: { type: Boolean, default: false },
  sortable: { type: Boolean, default: false },
  title: { type: String, required: true },
  rowLinkElement: { type: [String, Object], default: 'a' },
});

const hasActions = computed(()=> props.actions.length > 0);
const { changeSort, sortedColumn, sortedDirection, getSortQueryParams } = useSort({ defaultSort: props.defaultSort, onSorted: fetchRows });
const { selectedRows, hasSelectedRows, toggleSelected } = useTableSelection();
const { getAppliedFilters, applyFilters, availableFilters, search } = useModelFilters({
  available: props.filterable ? props.filters : [],
  preset: props.groupUsing?.setFilters ?? {},
  fixedFilters: props.fixedQueryFilters,
});

const axios = useAxios();
const loading = ref(false);
const fetchHandler = ref([]);

function fetchRows() {
  if (fetchHandler.value.length > 0) {
    fetchHandler.value.forEach((handler) => handler());
  }
}

function registerFetchHandler(fetch) {
  fetchHandler.value.push(fetch);
}

if (props.registerRefreshHandler) {
  props.registerRefreshHandler(fetchRows);
}

function handleAction(params) {
  loading.value = true;
  const selected = Object.keys(selectedRows.value).filter((key) => selectedRows.value[key]);
  axios.post(`${props.endpoint}/actions/${params.action}`, { ...params.payload, selected })
    .then(() => fetchRows())
    .finally(() => {
      loading.value = false;
    });
}
</script>
<template>
  <div v-loading="loading">
    <div class="flex items-center mb-4 justify-between">
      <div class="flex items-center">
        <div>
          <DataTableSearch v-model="search" @search="fetchRows" />
        </div>

        <DataTableActions
          v-if="hasActions"
          v-show="hasSelectedRows"
          class="ml-4"
          :actions="actions"
          @apply="handleAction"
        />
      </div>

      <div>
        <ExportButton
          v-for="item in downloads"
          :key="item.type"
          :endpoint="`${endpoint}/export/${item.type}`"
          :icon="item.icon"
          :get-applied-filters="getAppliedFilters"
          :apply-filters="applyFilters"
        />
      </div>
    </div>

    <div class="flex w-full overflow-x-hidden">
      <div v-if="filterable" class="flex-shrink-0">
        <DataTableFilters :class="{ 'mt-10': !groupUsing }" :filters="availableFilters" @apply="() => applyFilters(fetchRows)" />
      </div>

      <div class="flex-grow overflow-x-auto custom-scroll">
        <div v-if="groupUsing" class="space-y-2 grid">
          <DataTableGroup v-for="group in groupUsing.options" :key="group.value">
            <template #label>
              {{ group.label }}
            </template>

            <DataTableRows
              v-model:selectedRows="selectedRows"
              :columns="columns"
              :empty-icon="emptyIcon"
              :row-key="rowKey"
              :sortable="sortable"
              :sorted-direction="sortedDirection"
              :sorted-column="sortedColumn"
              :change-sort="changeSort"
              :toggle-selected="toggleSelected"
              :has-actions="hasActions"
              :has-sub-row="hasSubRow"
              :title="title"
              :row-link-element="rowLinkElement"
              :get-sort-query-params="getSortQueryParams"
              :get-applied-filters="getAppliedFilters"
              :register-fetch-handler="registerFetchHandler"
              :endpoint="endpoint"
              :filter-by="group.value"
            >
              <template #dataRow="{ row, rowIndex }">
                <slot name="dataRow" :row="row" :row-index="rowIndex" />
              </template>

              <template #subRow="{ row, rowIndex }">
                <slot name="subRow" :row="row" :row-index="rowIndex" />
              </template>
            </DataTableRows>
          </DataTableGroup>
        </div>

        <DataTableRows
          v-else
          v-model:selectedRows="selectedRows"
          :columns="columns"
          :empty-icon="emptyIcon"
          :row-key="rowKey"
          :sortable="sortable"
          :sorted-direction="sortedDirection"
          :sorted-column="sortedColumn"
          :change-sort="changeSort"
          :toggle-selected="toggleSelected"
          :has-actions="hasActions"
          :has-sub-row="hasSubRow"
          :title="title"
          :row-link-element="rowLinkElement"
          :get-sort-query-params="getSortQueryParams"
          :get-applied-filters="getAppliedFilters"
          :register-fetch-handler="registerFetchHandler"
          :endpoint="endpoint"
        >
          <template #dataRow="{ row, rowIndex }">
            <slot name="dataRow" :row="row" :row-index="rowIndex" />
          </template>

          <template #subRow="{ row, rowIndex }">
            <slot name="subRow" :row="row" :row-index="rowIndex" />
          </template>
        </DataTableRows>
      </div>
    </div>
  </div>
</template>
