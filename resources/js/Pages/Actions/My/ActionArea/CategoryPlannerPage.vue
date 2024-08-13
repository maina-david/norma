<script setup>
import { usePage } from '@inertiajs/vue3';
import { computed, inject, ref } from 'vue';
import PlannerLayout from '@/vue/components/my/actions/PlannerLayout.vue';
import { useAxios } from '@/vue/composables/useAxios';
import ActionAreaSummaryItem from '@/vue/components/my/actions/ActionAreaSummaryItem.vue';
import { TaskStatus } from '@/enums/actions/tasks/task-statuses';

defineOptions({ layout: [PlannerLayout] });

const registerRefreshHandler = inject('registerRefreshHandler');
const getAppliedFilters = inject('getAppliedFilters');

const page = usePage();
const assignees = ref({});
const items = ref([]);
const loading = ref(false);
const axios = useAxios();

function calculateStatus(current, addition) {
  // if we are adding the same item then return the current state.
  if (current === addition) {
    return current;
  }

  if (!current) {
    return addition;
  }

  return TaskStatus.inProgress;
}

function sortByLabel(a, b) {
  if (a.label > b.label) { return 1; }
  if (a.label < b.label) { return -1; }
  return 0;
}

function mapForType(fetched) {
  const grouped = {};
  const field = page.props.active === 'topics' ? 'subject' : 'control';
  const childField = page.props.active === 'topics' ? 'control' : 'subject';

  fetched.forEach((item) => {
    const groupKey = item[`${field}_label`];
    grouped[groupKey] = grouped[groupKey] ?? {
      id: groupKey,
      icon: item[`${field}_icon`],
      label: groupKey,
      children: [],
      assignees: {},
      references_count: 0,
      tasks_count: 0,
      status: null,
    };

    const updatedItem = {
      id: item.id,
      label: item.title,
      sub_label: item[`${childField}_label`],
      children: [],
      assignees: [],
      references_count: item.references_count,
      tasks_count: item.tasks.length,
      status: null,
    };

    item.tasks.forEach((task) => {
      if (task.assignee) {
        assignees[task.assignee.id] = assignees[task.assignee.id] ?? { ...task.assignee };
        grouped[groupKey].assignees[task.assignee.id] = assignees[task.assignee.id];
        updatedItem.assignees.push(assignees[task.assignee.id]);
      }

      grouped[groupKey].tasks_count += 1;
      updatedItem.status = calculateStatus(updatedItem.status, task.task_status);
      grouped[groupKey].status = calculateStatus(grouped[groupKey].status, task.task_status);
    });

    grouped[groupKey].children.push(updatedItem);
    grouped[groupKey].references_count += item.references_count;
  });

  const sorted = [];

  Object.keys(grouped).forEach((key) => {
    const children = grouped[key].children.sort(sortByLabel);

    sorted.push({ ...grouped[key], assignees: Object.values(grouped[key].assignees), children });
  });

  items.value = [...sorted.sort(sortByLabel)];
}

function fetchItems() {
  loading.value = true;

  const params = getAppliedFilters();

  axios.get('/actions/planner/areas', { params })
    .then(({ data }) => data)
    .then(({ data }) => mapForType(data))
    .finally(() => {
      loading.value = false;
    });
}

registerRefreshHandler(fetchItems);

fetchItems();

const completionPercent = computed(() => {
  return page.props.completion.completed > 0
    ? Math.round((page.props.completion.completed/page.props.completion.total) * 100)
    : 0;
});
</script>

<template>
  <div v-loading="loading" class="space-y-3 pl-2">
    <div v-if="page.props.completion && page.props.completion.total > 0" class="mb-4">
      <div class="mb-1 font-medium text-sm">
        {{ $t('actions.action_area.item_with_tasks', { item: page.props.title ?? $t('actions.action_area.index_title') }) }}
      </div>
      <div class="flex items-center">
        <div class="h-6 flex-grow border border-libryo-gray-200 rounded-full overflow-hidden bg-white">
          <div class="bg-primary h-full rounded-full" :style="`width:${completionPercent}%`" />
        </div>
        <div class="flex-shrink-0 pl-2">
          {{ page.props.completion.completed }} / {{ page.props.completion.total }}
        </div>
      </div>
    </div>

    <div class="flex items-center w-full font-semibold px-6">
      <div class="flex-grow">
        {{ page.props.title ?? $t('actions.action_area.index_title') }}
      </div>
      <div class="flex-shrink-0 w-40">
        {{ $t('tasks.task.assigned') }}
      </div>
      <div class="flex-shrink-0 w-40 text-center">
        {{ $t('requirements.requirements') }}
      </div>
      <div class="flex-shrink-0 w-40 text-center">
        {{ $t('nav.tasks') }}
      </div>
      <div class="flex-shrink-0 w-40 text-center">
        {{ $t('tasks.status') }}
      </div>
      <div class="w-10" />
    </div>

    <ActionAreaSummaryItem
      v-for="item in items"
      :key="item.id"
      :category="item"
      collapsable
      parent
    />
  </div>
</template>
