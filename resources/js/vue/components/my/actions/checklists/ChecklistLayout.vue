<script setup>
import { usePage } from '@inertiajs/vue3';
import { provide, ref } from 'vue';
import PageHeader from '@/vue/components/my/PageHeader.vue';
import LibryoIcon from '@/collaborate/components/LibryoIcon.vue';
import DataTableFilters from '@/vue/components/data-table/DataTableFilters.vue';
import { useModelFilters } from '@/vue/composables/useModelFilters';
import filters from '@/Pages/Actions/My/Checklist/filters';
import DataTableSearch from '@/vue/components/data-table/DataTableSearch.vue';
import ExportButton from '@/vue/components/my/ExportButton.vue';
import ActionPlannerExportModal from '@/vue/components/my/actions/ActionPlannerExportModal.vue';

const page = usePage();

const { getAppliedFilters, applyFilters, availableFilters, search } = useModelFilters({ available: filters, preset: {}, fixedFilters: {} });

const fetchHandler = ref(null);

function registerRefreshHandler(trigger) {
  fetchHandler.value = trigger;
}

function fetchRows() {
  if (fetchHandler.value) {
    fetchHandler.value();
  }
}

provide('registerRefreshHandler', registerRefreshHandler);
provide('getAppliedFilters', getAppliedFilters);
</script>

<template>
  <div class="h-full flex flex-col">
    <PageHeader class="flex-shrink-0">
      <div class="flex items-center">
        <LibryoIcon name="clipboard-list" class="mr-3 ml-2  text-libryo-gray-400" />
        <div>
          <span>{{ $t('actions.checklist.checklists') }}</span>
          <span class="text-xs text-libryo-gray-500 italic ml-3">{{ page.props.stream.title }}</span>
        </div>
      </div>

      <template #actions>
        <ExportButton
          v-if="page.props.active !== 'requirements'"
          :endpoint="`/actions/planner/${page.props.active}/export/excel`"
          icon="file-excel"
          :get-applied-filters="getAppliedFilters"
          :apply-filters="applyFilters"
          has-body
        >
          <template #default="{ updateBodyPayload, cancelExport, triggerExport }">
            <ActionPlannerExportModal
              :update-body-payload="updateBodyPayload"
              :cancel-export="cancelExport"
              :trigger-export="triggerExport"
            />
          </template>
        </ExportButton>
      </template>
    </PageHeader>

    <div class="flex-grow px-2">
      <div class="flex w-full">
        <div class="flex-shrink-0">
          <div class="mb-4">
            <DataTableSearch v-model="search" @search="fetchRows" />
          </div>
          <DataTableFilters
            :filters="availableFilters"
            @apply="() => applyFilters(fetchRows)"
          />
        </div>
        <div class="flex-grow">
          <slot />
        </div>
      </div>
    </div>
  </div>
</template>
