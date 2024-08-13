<script setup>
import { computed, provide, ref } from 'vue';
import InputElement from '@/vue/components/InputElement.vue';
import AppIcon from '@/vue/components/AppIcon.vue';
import { useFetchModels } from '@/vue/composables/useFetchModels';
import AppPagination from '@/vue/components/AppPagination.vue';
import EmptyState from '@/vue/components/EmptyState.vue';
import LoadingIndicator from '@/vue/components/LoadingIndicator.vue';

const props = defineProps({
  columns: { type: Array, required: true },
  emptyIcon: { type: String, required: true },
  rowKey: { type: String, default: 'id' },
  sortable: { type: Boolean, default: false },
  sortedDirection: { type: String, required: true },
  sortedColumn: { type: String, required: true },
  changeSort: { type: Function, required: true },
  toggleSelected: { type: Function, required: true },
  hasActions: { type: Boolean, required: true },
  hasSubRow: { type: Boolean, required: true },
  title: { type: String, required: true },
  rowLinkElement: { type: [String, Object], default: 'a' },

  getSortQueryParams: { type: Function, required: true },
  getAppliedFilters: { type: Function, required: true },
  registerFetchHandler: { type: Function, required: true },
  endpoint: { type: String, required: true },
  filterBy: { type: Array, default: null },
});

const { fetchRows, updateRow, loadingRows, rows, pagination, changePage } = useFetchModels({
  getSortQueryParams: props.getSortQueryParams,
  endpoint: props.endpoint,
  getAppliedFilters: () => ({ ...props.getAppliedFilters(), filters: props.filterBy }),
});

const selectedAll = ref(false);
const selectedRows = defineModel('selectedRows');

const openRow = ref(null);
const openedRows = ref({});

function toggleOpenRow(current) {
  openedRows.value[current] = true;

  if (props.hasSubRow) {
    openRow.value = openRow.value === current ? null : current;
  }
}

const columnLength = computed(() => props.columns.length);

function getActionedColumnClasses(index, row) {
  if(index === (columnLength.value - 1) && !props.hasSubRow) {
    return { 'rounded-tr-md': true, 'rounded-br-md': openRow.value !== row[props.rowKey] };
  }

  return {};
}
function getUnActionedColumnClasses(index, row) {
  const classes = {};

  if (index === 0) {
    classes['rounded-tl-md'] = true;
    classes['rounded-bl-md'] = true;
  }

  if (index === (columnLength.value - 1) && !props.hasSubRow) {
    classes['rounded-tr-md'] = true;
    classes['rounded-br-md'] = true;
  }

  if (openRow.value === row[props.rowKey]) {
    classes['rounded-bl-md'] = false;
    classes['rounded-br-md'] = false;
  }

  return classes;
}

function getColumnClasses(index, row) {
  if (props.hasActions) {
    return getActionedColumnClasses(index, row);
  }

  return getUnActionedColumnClasses(index, row);
}

provide('fetchRows', fetchRows);
provide('updateDataTableRow', updateRow);

fetchRows();

props.registerFetchHandler(fetchRows);
</script>

<template>
  <div class="relative">
    <LoadingIndicator v-if="loadingRows" />

    <EmptyState v-if="!loadingRows && rows.length < 1" class="bg-white py-10 rounded-lg shadow" :title="$t('interface.no_items_yet', { type: title })" :icon="emptyIcon" />

    <div v-else class="w-full pb-4">
      <table class="w-full h-full">
        <thead class="w-full">
          <tr>
            <th v-if="hasActions" class="w-4 pl-4">
              <span class="flex items-center">
                <InputElement v-model="selectedAll" type="checkbox" @input="() => toggleSelected(rows, rowKey, selectedAll)" />
              </span>
            </th>
            <th
              v-for="(column, index) in columns"
              :key="column.name"
              class="py-2 px-2 max-w-screen-90"
              :style="column.minWidth ? `min-width: ${column.minWidth};` : ''"
            >
              <span :class="`flex items-center justify-${column.align} mr-2`">
                <button
                  v-if="sortable && column.sortable"
                  class="flex items-center"
                  :class="sortedColumn === column.rowField() ? 'text-primary' : 'text-libryo-gray-400 hover:text-primary'"
                  @click.prevent="() => changeSort(column.rowField())"
                >
                  <span class="text-nowrap" :class="{ 'pl-4': index === 0 }">
                    {{ column.label }}
                  </span>

                  <AppIcon v-if="column.rowField() === sortedColumn && sortedDirection === 'asc'" class="ml-1" name="caret-up" />
                  <AppIcon v-else class="ml-1" :name="'caret-down'" />
                </button>

                <span v-else class="text-nowrap mr-2 text-libryo-gray-400" :class="{ 'pl-4': index === 0 }">
                  {{ column.label }}
                </span>
              </span>
            </th>

            <th v-if="hasSubRow" class="w-4 pl-4" />
          </tr>
        </thead>

        <tbody class="w-full">
          <template v-for="(row, rowIndex) in rows" :key="JSON.stringify(row)">
            <tr
              class="shadow text-libryo-gray-600"
              :class="{ 'cursor-pointer': hasSubRow, 'rounded-t-md bg-libryo-gray-200': openRow === row[rowKey], 'rounded-md bg-white': openRow !== row[rowKey] }"
              @click="toggleOpenRow(row[rowKey])"
            >
              <slot name="dataRow" :row="row" :row-index="rowIndex">
                <td v-if="hasActions" class="rounded-tl-md pl-4" :class="{ 'rounded-bl-md': openRow !== row[rowKey] }">
                  <div class="w-4 h-full flex items-center">
                    <InputElement v-model="selectedRows[row[rowKey]]" type="checkbox" />
                  </div>
                </td>

                <td
                  v-for="(column, colIndex) in columns"
                  :key="column.name"
                  class="py-4 px-4"
                  :class="{ [`text-${column.align}`]: true, ...getColumnClasses(colIndex, row) }"
                >
                  <component
                    :is="rowLinkElement"
                    v-if="column.href"
                    class="text-primary hover:underline"
                    :href="column.href(row)"
                  >
                    <component :is="column.component()" v-if="column.component" :row="row" :row-index="rowIndex" />

                    <template v-else>
                      <span v-if="column.date">{{ row[column.rowField()] ? $format.date(row[column.rowField()]) : '-' }}</span>
                      <span v-else>{{ row[column.rowField()] ?? '-' }}</span>
                    </template>
                  </component>

                  <span v-else>
                    <component :is="column.component()" v-if="column.component" :row="row" :row-index="rowIndex" />

                    <template v-else>
                      <span v-if="column.date">{{ row[column.rowField()] ? $format.date(row[column.rowField()]) : '-' }}</span>
                      <span v-else>{{ row[column.rowField()] ?? '-' }}</span>
                    </template>
                  </span>
                </td>

                <th v-if="hasSubRow" class="pr-6 rounded-tr-md rounded-br-md">
                  <AppIcon name="angle-down" />
                </th>
              </slot>
            </tr>

            <tr
              v-if="hasSubRow && openedRows[row[rowKey]]"
              v-show="openRow === row[rowKey]"
              class="bg-white shadow rounded-b-md"
              style="display: none;"
            >
              <td class="rounded-b-md" :colspan="columns.length + (hasActions ? 1 : 0) + (hasSubRow ? 1 : 0)">
                <slot name="subRow" :row="row" :row-index="rowIndex" />
              </td>
            </tr>

            <tr>
              <td :colspan="columns.length + (hasActions ? 1 : 0)">
                <div class="h-1 w-2" />
              </td>
            </tr>
          </template>
        </tbody>
      </table>

      <div v-if="pagination.lastPage > 1" class="pb-4 bg-white rounded-b">
        <AppPagination
          :current="pagination.page"
          :per-page="pagination.perPage"
          :last-page="pagination.lastPage"
          @page="changePage"
        />
      </div>
    </div>
  </div>
</template>
