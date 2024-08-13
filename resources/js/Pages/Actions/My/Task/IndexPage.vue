<script setup>
import { usePage } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import { ref } from 'vue';
import PageHeader from '@/vue/components/my/PageHeader.vue';
import LibryoIcon from '@/collaborate/components/LibryoIcon.vue';
import AppTabs from '@/vue/components/AppTabs.vue';
import NoGroupList from '@/Pages/Actions/My/Task/NoGroupList.vue';
import GroupedList from '@/Pages/Actions/My/Task/GroupedList.vue';
import CreateTaskButton from '@/vue/components/my/tasks/CreateTaskButton.vue';
import { TaskTypes } from '@/enums/actions/tasks/task-types';
import AppIcon from '@/vue/components/AppIcon.vue';

const page = usePage();
const { t } = useI18n({ useScope: 'global' });

const typeGeneric = TaskTypes.generic;
const fetchHandler = ref(null);
function registerRefreshHandler(trigger) {
  fetchHandler.value = trigger;
}

function triggerFetches() {
  if (fetchHandler.value) {
    fetchHandler.value();
  }
}

const tabs = [
  {
    id: 'schedule',
    label: t('tasks.task.views.schedule'),
    tooltip: t('tasks.task.view_tooltips.schedule'),
    icon: 'user',
    target: '/actions/view/schedule',
    inertia: true,
  },
  {
    id: 'calendar',
    label: t('tasks.task.views.calendar'),
    tooltip: t('tasks.task.view_tooltips.calendar'),
    icon: 'calendar',
    target: '/actions/view/calendar',
  },
  {
    id: 'type',
    label: t('tasks.task.views.type'),
    tooltip: t('tasks.task.view_tooltips.type'),
    icon: 'font',
    target: '/actions/view/type',
    inertia: true,
  },
  {
    id: 'topic',
    label: t('tasks.task.views.topic'),
    tooltip: t('tasks.task.view_tooltips.topic'),
    icon: 'layer-group',
    target: '/actions/view/topic',
    inertia: true,
  },
  {
    id: 'due',
    label: t('tasks.task.views.due'),
    tooltip: t('tasks.task.view_tooltips.due'),
    icon: 'exclamation',
    target: '/actions/view/due',
    inertia: true,
  },
  {
    id: 'status',
    label: t('tasks.task.views.status'),
    tooltip: t('tasks.task.view_tooltips.status'),
    icon: 'signal-bars',
    target: '/actions/view/status',
    inertia: true,
  },
  {
    id: 'assignee',
    label: t('tasks.task.views.assignee'),
    tooltip: t('tasks.task.view_tooltips.assignee'),
    icon: 'users',
    target: '/actions/view/assignee',
    inertia: true,
  },
  {
    id: 'control',
    label: t('tasks.task.views.control'),
    tooltip: t('tasks.task.view_tooltips.control'),
    icon: 'rotate-left',
    target: '/actions/view/control?statuses[0]=0&statuses[1]=1',
    inertia: true,
  },
  {
    id: 'list',
    label: t('tasks.task.views.list'),
    tooltip: t('tasks.task.view_tooltips.list'),
    icon: 'file-alt',
    target: '/actions/view/list',
    inertia: true,
  },
];

</script>

<template>
  <div class="h-full flex flex-col">
    <PageHeader class="flex-shrink-0">
      <div class="flex items-center">
        <LibryoIcon name="clipboard-list" class="mr-3 ml-2  text-libryo-gray-400" />
        <span>
          {{ $t('my.nav.actions_manager') }}
        </span>

        <a target="_blank" class="ml-4" href="https://success.libryo.com/en/knowledge/actions-manager">
          <AppIcon name="question-circle" />
        </a>
      </div>

      <template #actions>
        <div class="flex">
          <button class="flex items-center space-x-3 mr-4">
            <a href="/actions/projects">{{ $t('tasks.manage_projects') }}</a>
          </button>
          <CreateTaskButton
            v-if="page.props.stream.single"
            :type-id="page.props.stream.id"
            :type="typeGeneric"
            @create="triggerFetches"
          />
        </div>
      </template>
    </PageHeader>

    <div class="flex-grow px-4 lg:px-6">
      <AppTabs :tabs="tabs" :active="page.props.active ?? tabs[0].id">
        <template #default="{ active }">
          <NoGroupList v-if="active === 'list'" :register-refresh-handler="registerRefreshHandler" />
          <GroupedList v-else :register-refresh-handler="registerRefreshHandler" :group-by="active" />
        </template>
      </AppTabs>
    </div>
  </div>
</template>
