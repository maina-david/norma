<script setup>
import { usePage } from '@inertiajs/vue3';
import { provide, ref } from 'vue';
import PageHeader from '@/vue/components/my/PageHeader.vue';
import AppIcon from '@/vue/components/AppIcon.vue';
import { useModelFilters } from '@/vue/composables/useModelFilters';
import filters from '@/Pages/Actions/My/Task/definitions/filters';
import DataTableFilters from '@/vue/components/data-table/DataTableFilters.vue';
import StatusesPieChart from '@/vue/components/my/tasks/StatusesPieChart.vue';
import AppCard from '@/vue/components/AppCard.vue';
import TaskMetricWidget from '@/vue/components/my/actions/TaskMetricWidget.vue';
import CompletionCreationBarChart from '@/vue/components/my/tasks/CompletionCreationBarChart.vue';
import TaskImpactBarChart from '@/vue/components/my/tasks/TaskImpactBarChart.vue';

const fetchHandlers = ref({});
const page = usePage();
const { getAppliedFilters, applyFilters, availableFilters } = useModelFilters({ available: filters, preset: {}, fixedFilters: {} });

function registerRefreshHandler(trigger, key) {
  fetchHandlers.value[key] = trigger;
}

function fetchRows() {
  Object.keys(fetchHandlers.value).forEach((key) => fetchHandlers.value[key]());
}

provide('registerRefreshHandler', registerRefreshHandler);
provide('getAppliedFilters', getAppliedFilters);
</script>

<template>
  <div class="h-full flex flex-col w-full">
    <PageHeader class="flex-shrink-0">
      <div class="flex items-center">
        <div class="flex items-center">
          <AppIcon name="clipboard-list" class="mr-3 ml-2  text-libryo-gray-400" size="8" />

          <div>
            <span>{{ $t('actions.dashboard.actions_dashboard') }}</span>
            <span class="text-xs text-libryo-gray-500 italic ml-3">{{ page.props.stream.title }}</span>
          </div>
        </div>
      </div>
    </PageHeader>

    <div class="flex-grow px-4 lg:px-6 w-full">
      <div class="flex w-full">
        <div class="flex-shrink-0">
          <DataTableFilters :filters="availableFilters" @apply="() => applyFilters(fetchRows)" />
        </div>

        <div class="flex-grow">
          <div class="grid sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 mb-12">
            <TaskMetricWidget
              :title="$t('actions.dashboard.total_tasks')"
              type="total"
              :applied-filters="`end_date=${$format.sysDate()}&statuses%5B0%5D=0&statuses%5B1%5D=1`"
            />
            <TaskMetricWidget
              :title="$t('actions.dashboard.columns.overdue_tasks')"
              icon="exclamation-triangle"
              colour="text-negative"
              type="overdue"
              :applied-filters="`end_date=${$format.sysDate()}&statuses%5B0%5D=0&statuses%5B1%5D=1&statuses%5B2%5D=3`"
            />
            <TaskMetricWidget
              :title="$t('actions.dashboard.task_completion_rate')"
              icon="hands-clapping"
              colour="text-positive"
              type="complete-rate"
              applied-filters="statuses%5B0%5D=2"
            />
            <TaskMetricWidget
              :title="$t('actions.dashboard.task_incomplete_rate')"
              icon="times-circle"
              colour="text-negative"
              type="incomplete-rate"
              applied-filters="statuses%5B0%5D=1&statuses%5B1%5D=0&statuses%5B2%5D=3"
            />
          </div>

          <div class="grid xl:grid-cols-2 gap-8 mb-12">
            <AppCard class="shadow flex flex-col items-center justify-center">
              <CompletionCreationBarChart class="h-80 w-full p-4 lg:px-8 overflow-hidden" />
            </AppCard>

            <AppCard class="shadow flex flex-col items-center justify-center">
              <TaskImpactBarChart class="h-80 w-full p-4 lg:px-8 overflow-hidden" />
            </AppCard>

            <AppCard class="shadow flex flex-col items-center justify-center">
              <StatusesPieChart class="h-80" />
            </AppCard>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
