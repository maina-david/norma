<script setup>
import { usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import PageHeader from '@/vue/components/my/PageHeader.vue';
import BackButton from '@/vue/components/BackButton.vue';
import AppIcon from '@/vue/components/AppIcon.vue';
import TaskDataTable from '@/vue/components/my/tasks/TaskDataTable.vue';
import TaskDetailsSubRow from '@/vue/components/my/tasks/TaskDetailsSubRow.vue';
import ActionAreaReferenceDataTable from '@/vue/components/my/references/ActionAreaReferenceDataTable.vue';
import ReferenceSuggestedTasks from '@/vue/components/my/references/ReferenceSuggestedTasks.vue';
import ReferenceDetailTabs from '@/vue/components/my/references/ReferenceDetailTabs.vue';
import TaskAttachables from '@/vue/components/my/tasks/TaskAttachables.vue';
import ConfirmAction from '@/vue/components/ConfirmAction.vue';

const page = usePage();
const taskQueryFilters = computed(() => ({ actionArea: page.props.action.id }));

const fetchHandler = ref(null);
function registerRefreshHandler(trigger) {
  fetchHandler.value = trigger;
}

function fetchTasks() {
  if (fetchHandler.value) {
    fetchHandler.value();
  }
}

</script>

<template>
  <div class="h-full flex flex-col">
    <PageHeader class="flex-shrink-0">
      <div class="flex items-center">
        <BackButton :target="page.props.backButton" />

        <div class="flex items-center ml-4">
          <AppIcon name="clipboard-list" class="mr-3 ml-2  text-norma-gray-400" size="8" />

          <span class="space-x-2">
            <span v-tooltip="page.props.action.control.description ?? ''" :class="{ 'underline-offset-8 underline decoration-dotted decoration-2 cursor-pointer': page.props.action.control.description }">
              {{ page.props.action.control.display_label }}
            </span>
            <span>relating to</span>
            <span v-tooltip="page.props.action.subject.description ?? ''" :class="{ 'underline-offset-8 underline decoration-dotted decoration-2 cursor-pointer': page.props.action.subject.description }">
              {{ page.props.action.subject.display_label }}
            </span>
          </span>
        </div>
      </div>
    </PageHeader>

    <div class="flex-grow px-4 lg:px-6">
      <div class="px-2">
        <h2 class="font-semibold mb-8">
          {{ $t('actions.action_area.associated_tasks') }}
        </h2>

        <div>
          <TaskDataTable
            :register-refresh-handler="registerRefreshHandler"
            :fixed-query-filters="taskQueryFilters"
            has-sub-row
            sortable
          >
            <template #subRow="{ row, rowIndex }">
              <div class="md:grid grid-cols-5 gap-4 pt-8">
                <div class="col-span-3 border-r border-norma-gray-100 text-norma-gray-600">
                  <TaskDetailsSubRow :row="row" :row-index="rowIndex" />
                </div>

                <div class="pr-4 pb-4 pt-2 col-span-2 pl-4">
                  <TaskAttachables :row="row" :row-index="rowIndex" />
                </div>
              </div>
            </template>
          </TaskDataTable>
        </div>
      </div>

      <div class="px-2 mt-10 mb-20">
        <h2 class="font-semibold mb-8">
          {{ $t('actions.action_area.associated_requirements', { label: page.props.action.title }) }}
        </h2>

        <div>
          <ActionAreaReferenceDataTable
            :action-area-id="page.props.action.id"
            :fixed-query-filters="taskQueryFilters"
            has-sub-row
            sortable
          >
            <template #subRow="{ row }">
              <div class="md:grid grid-cols-5 gap-4">
                <div class="col-span-5 lg:col-span-2 border-r border-norma-gray-100 text-norma-gray-600">
                  <ReferenceSuggestedTasks :reference-id="row.id" :action-area="page.props.action" @create="fetchTasks" />
                </div>

                <div class="pr-4 pb-4 pt-8 col-span-5 lg:col-span-3 pl-4">
                  <ReferenceDetailTabs :reference-id="row.id" :work-id="row.work_id" />
                </div>
              </div>
            </template>
          </ActionAreaReferenceDataTable>
        </div>
      </div>
    </div>

    <ConfirmAction />
  </div>
</template>
