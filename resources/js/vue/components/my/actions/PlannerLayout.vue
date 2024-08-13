<script setup>
import { usePage } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import { provide, ref } from 'vue';
import PageHeader from '@/vue/components/my/PageHeader.vue';
import LibryoIcon from '@/collaborate/components/LibryoIcon.vue';
import AppTabs from '@/vue/components/AppTabs.vue';
import DataTableFilters from '@/vue/components/data-table/DataTableFilters.vue';
import { useModelFilters } from '@/vue/composables/useModelFilters';
import filters from '@/Pages/Actions/My/Task/definitions/filters';
import DataTableSearch from '@/vue/components/data-table/DataTableSearch.vue';
import ExportButton from '@/vue/components/my/ExportButton.vue';
import ActionPlannerExportModal from '@/vue/components/my/actions/ActionPlannerExportModal.vue';
import AppIcon from '@/vue/components/AppIcon.vue';

const page = usePage();
const { t } = useI18n({ useScope: 'global' });

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

const tabs = [
  {
    id: 'topics',
    label: t('nav.topics'),
    icon: 'ballot-check',
    target: '/actions/topics',
    inertia: true,
  },
  {
    id: 'controls',
    label: t('actions.action_area.control_types'),
    icon: 'file-alt',
    target: '/actions/controls',
    inertia: true,
  },
  {
    id: 'requirements',
    label: t('requirements.requirements'),
    icon: 'gavel',
    target: '/actions/requirements',
    inertia: true,
  },
];

provide('registerRefreshHandler', registerRefreshHandler);
provide('getAppliedFilters', getAppliedFilters);
</script>

<template>
  <div class="h-full flex flex-col">
    <PageHeader class="flex-shrink-0">
      <div class="flex items-center">
        <LibryoIcon name="clipboard-list" class="mr-3 ml-2  text-libryo-gray-400" />
        <span>
          {{ $t('actions.action_area.actions_planner') }}
        </span>

        <a target="_blank" class="ml-4" href="https://success.libryo.com/en/knowledge/actions-planner">
          <AppIcon name="question-circle" />
        </a>
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

    <div class="flex-grow px-4 lg:px-6">
      <AppTabs :tabs="tabs" :active="page.props.active ?? tabs[0].id" no-overflow>
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
      </AppTabs>
    </div>
  </div>
</template>
