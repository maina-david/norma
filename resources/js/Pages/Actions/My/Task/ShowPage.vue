<script setup>
import { usePage } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import { provide, ref } from 'vue';
import BackButton from '@/vue/components/BackButton.vue';
import PageHeader from '@/vue/components/my/PageHeader.vue';
import DeleteButton from '@/vue/components/DeleteButton.vue';
import TaskStatusSelector from '@/vue/components/my/tasks/TaskStatusSelector.vue';
import TaskPrioritySelector from '@/vue/components/my/tasks/TaskPrioritySelector.vue';
import TaskAssigneeSelector from '@/vue/components/my/tasks/TaskAssigneeSelector.vue';
import TaskFollowersSelector from '@/vue/components/my/tasks/TaskFollowersSelector.vue';
import AppTabs from '@/vue/components/AppTabs.vue';
import CommentListing from '@/vue/components/my/comments/CommentListing.vue';
import ActivityListing from '@/vue/components/my/activities/ActivityListing.vue';
import UploadedFiles from '@/vue/components/my/files/UploadedFiles.vue';
import TaskInlineDropDownInput from '@/vue/components/my/tasks/TaskInlineDropDownInput.vue';
import ConfirmAction from '@/vue/components/ConfirmAction.vue';
import ProjectSelector from '@/vue/components/my/projects/ProjectSelector.vue';
import { useAxios } from '@/vue/composables/useAxios';
import RemindersListing from '@/vue/components/my/reminders/RemindersListing.vue';
import RecurringTaskDropDownInput from '@/vue/components/my/tasks/RecurringTaskDropDownInput.vue';

const { t } = useI18n({ useScope: 'global' });
const axios = useAxios();
const page = usePage();

function deleteTask() {
  axios.delete(`/actions/${page.props.task.id}`);
}

const leftTabs = [
  {
    id: 'files',
    label: t('storage.file.files'),
    icon: 'folder-open',
  },
  {
    id: 'activities',
    label: t('tasks.activity'),
    icon: 'analytics',
  },
];

const rightTabs = [
  {
    id: 'comments',
    label: t('comments.comments'),
    icon: 'comments',
  },
  {
    id: 'reminders',
    label: t('reminders.reminders'),
    icon: 'alarm-clock',
  },
];

const task = ref({});
const loading = ref(false);

function fetchTask() {
  loading.value = true;
  axios.get(`/actions/tasks/${page.props.task}`)
    .then(({ data }) => data)
    .then(({ data }) => {
      task.value = data;
    })
    .finally(() => {
      loading.value = false;
    });
}

function setTask(item) {
  task.value = {
    ...task.value,
    ...item,
  };
}

function updateDataTableRow(index, changes) {
  setTask(changes);
}

function createCopies() {
  loading.value = true;
  axios.post(`/actions/tasks/${page.props.task}/copy`)
    .then(({ data }) => data)
    .then(({ data }) => {
      task.value = data;
    })
    .finally(() => {
      loading.value = false;
    });
}

provide('updateDataTableRow', updateDataTableRow);
fetchTask();
</script>

<template>
  <div v-loading="loading" class="flex flex-col h-full">
    <PageHeader class="flex-shrink-0">
      <div class="flex items-center">
        <div class="mr-2">
          <BackButton :target="page.props.backButton" />
        </div>
        <div class="flex items-center">
          <div>
            <h3 class="pt-2">
              {{ $t('actions.action_area.tasks') }}
            </h3>
          </div>
        </div>
      </div>
    </PageHeader>

    <div v-if="task.id" class="px-4 pb-20">
      <div class="mb-6">
        <div class="flex justify-between">
          <div class="py-2">
            <TaskInlineDropDownInput
              maxlength="255"
              rows="5"
              type="textarea"
              field="title"
              :row="task"
              @change="(val) => task.title = val"
            >
              <template #display="{ toggle }">
                <div class="">
                  <div class="flex text-lg font-semibold item-center">
                    {{ task.title }}
                  </div>
                  <div>
                    <button class="font-semibold cursor-pointer text-primary" @click.prevent="toggle">
                      {{ $t('tasks.edit_title') }}
                    </button>
                  </div>
                </div>
              </template>
            </TaskInlineDropDownInput>
          </div>

          <div class="flex-shrink-0 mr-2">
            <DeleteButton :target="`/actions/${page.props.task.id}`" @click="deleteTask">
              {{ $t('actions.delete') }}
            </DeleteButton>
          </div>
        </div>
        <div class="mt-1 text-sm font-normal text-norma-gray-900">
          <div class="flex">
            <TaskInlineDropDownInput type="wysiwyg" field="description" :row="task" @change="(val) => task.description = val">
              <template #display="{ toggle }">
                <div class="">
                  <div class="wysiwyg-content" v-html="task.description" />
                  <div>
                    <button class="font-semibold cursor-pointer text-primary" @click.prevent="toggle">
                      {{ $t('tasks.edit_description') }}
                    </button>
                  </div>
                </div>
              </template>
            </TaskInlineDropDownInput>
          </div>
        </div>
      </div>

      <!--    Table-->
      <div class="px-4 bg-white shadow sm:rounded-lg">
        <table class="w-full ">
          <tr v-if="!page.props.stream.single && task.norma" class="border-b">
            <td class="px-6 py-4 text-sm font-medium whitespace-normal text-norma-gray-900">
              {{ $t('customer.norma.norma_stream') }}
            </td>
            <td class="px-6 py-4 text-sm font-medium whitespace-normal text-norma-gray-900">
              {{ task.title }}
            </td>
          </tr>
          <tr class="flex w-full border-b">
            <td class="w-1/4 px-6 py-4 text-sm font-medium whitespace-normal text-norma-gray-900">
              {{ $t('tasks.status') }}
            </td>
            <td class="px-3 py-4 text-sm font-medium whitespace-normal text-norma-gray-900">
              <TaskStatusSelector :row-index="1" :row="task" />
            </td>
          </tr>
          <tr class="flex w-full border-b">
            <td class="w-1/4 px-6 py-4 text-sm font-medium whitespace-normal text-norma-gray-900">
              {{ $t('timestamps.created_at') }}
            </td>
            <td class="py-4 pl-3 text-sm font-medium whitespace-normal text-norma-gray-900">
              {{ $format.date(task.created_at) }}
            </td>
          </tr>

          <tr class="flex w-full border-b">
            <td class="w-1/4 px-6 py-4 text-sm font-medium whitespace-normal text-norma-gray-900">
              {{ $t('tasks.due_on') }}
            </td>
            <td class="py-4 text-sm font-medium whitespace-normal text-norma-gray-900">
              <div class="flex justify-center text-center">
                <TaskInlineDropDownInput
                  field="due_on"
                  :row-index="1"
                  :row="task"
                  type="date"
                  :get-label="(val) => val ? $format.date(val) : '-'"
                />
              </div>
            </td>
          </tr>

          <tr class="flex w-full border-b">
            <td class="w-1/4 px-6 py-4 text-sm font-medium whitespace-normal text-norma-gray-900">
              {{ $t('tasks.project') }}
            </td>
            <td class="py-4 text-sm font-medium whitespace-normal text-norma-gray-900">
              <TaskInlineDropDownInput
                field="task_project_id"
                :row="task"
                :row-index="1"
                :get-label="() => task.project?.title ?? '-'"
              >
                <template #inputs="{ inputValue, update }">
                  <ProjectSelector :model-value="inputValue" @update:model-value="update" />
                </template>
              </TaskInlineDropDownInput>
            </td>
          </tr>

          <tr class="flex w-full border-b">
            <td class="w-1/4 px-6 py-4 text-sm font-medium whitespace-normal text-norma-gray-900">
              {{ $t('tasks.assigned_to') }}
            </td>
            <td class="px-3 py-4 text-sm font-medium whitespace-normal text-norma-gray-900">
              <TaskAssigneeSelector :row-index="1" :row="task" />
            </td>
          </tr>

          <tr class="flex w-full border-b">
            <td class="w-1/4 px-6 py-4 text-sm font-medium whitespace-normal text-norma-gray-900">
              {{ $t('tasks.followers') }}
            </td>
            <td class="px-3 py-4 text-sm font-medium whitespace-normal text-norma-gray-900">
              <TaskFollowersSelector :row-index="1" :row="task" />
            </td>
          </tr>

          <tr class="flex w-full border-b">
            <td class="w-1/4 px-6 py-4 text-sm font-medium whitespace-normal text-norma-gray-900">
              {{ $t('tasks.priority') }}
            </td>
            <td class="py-4 text-sm font-medium whitespace-normal text-norma-gray-900">
              <span class="text-norma-gray-500">
                <TaskPrioritySelector :row-index="1" :row="task" />
              </span>
            </td>
          </tr>

          <tr v-if="page.props.stream.single && task.taskable_type === 'register_item'" class="flex w-full border-b">
            <td class="w-1/4 px-6 py-4 text-sm font-medium whitespace-normal text-norma-gray-900">
              {{ $t('tasks.instance') }}
            </td>
            <td class="px-3 py-4 text-sm font-medium whitespace-normal text-norma-gray-900">
              <div
                v-if="task.source_task_id"
                v-html="$t('tasks.copied_task_details', { route: `/requirements/citations/${task.taskable_id}`, 'requirement': task.sub_title })"
              />
              <button v-else class="underline text-primary" @click="createCopies">
                {{ $t('tasks.create_copies') }}
              </button>
            </td>
          </tr>
          <tr class="flex w-full border-b">
            <td class="w-1/4 px-6 py-4 text-sm font-medium whitespace-normal text-norma-gray-900">
              {{ $t('tasks.recurring.tasks') }}
            </td>
            <td class="py-4 pl-3 text-sm font-medium whitespace-normal text-norma-gray-900">
              <RecurringTaskDropDownInput
                :task="task"
                @create="fetchTask"
              />
            </td>
          </tr>
        </table>
      </div>
      <!-- Footer content-->
      <div class="grid p-5 border-t lg:grid-cols-12 lg:gap-4 bg-norma-gray-50 border-norma-gray-100">
        <div class="lg:col-span-7">
          <AppTabs :tabs="leftTabs" :active="leftTabs[0].id">
            <template #default="{ active }">
              <KeepAlive>
                <ActivityListing v-if="active === 'activities'" relation="task" :related-id="task.id" />
              </KeepAlive>

              <KeepAlive>
                <UploadedFiles
                  v-if="active === 'files'"
                  requires-folder
                  multiple
                  can-upload
                  relation="task"
                  :related-id="task.id"
                  :norma-id="task.place_id"
                />
              </KeepAlive>
            </template>
          </AppTabs>
        </div>
        <div class="lg:col-span-5">
          <AppTabs :tabs="rightTabs" :active="rightTabs[0].id">
            <template #default="{ active }">
              <KeepAlive>
                <CommentListing v-if="active === 'comments'" relation="task" :related-id="task.id" :reply="true" />
              </KeepAlive>
              <KeepAlive>
                <div v-if="active === 'reminders'">
                  <RemindersListing relation="task" :related-id="task.id" />
                </div>
              </KeepAlive>
            </template>
          </AppTabs>
        </div>
      </div>
    </div>
    <ConfirmAction />
  </div>
</template>
<div>
</div>
