<script setup>
import { computed, ref } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { useAxios } from '@/vue/composables/useAxios';
import TableGroup from '@/vue/components/data-table/classes/TableGroup';
import DueDates from '@/enums/actions/tasks/due-dates';
import { TaskTypes } from '@/enums/actions/tasks/task-types';
import { TaskStatus } from '@/enums/actions/tasks/task-statuses';
import TaskDataTable from '@/vue/components/my/tasks/TaskDataTable.vue';

const page = usePage();
const user = computed(() => page.props.auth.user);
const group = ref(null);

const props = defineProps({
  groupBy: { type: String, required: true },
  registerRefreshHandler: { type: Function, default: null },
});

const fetchable = {
  assignee: 'assigned_to_id',
  control: 'has:actionArea|control_category_id',
  topic: 'has:actionArea|subject_category_id',
};

const whenNull = {
  control: 'action_area_id',
  topic: 'action_area_id',
};

const options = {
  due:() => DueDates.forGrouping('due_on'),
  schedule:() => DueDates.forGrouping('due_on'),
  status:() => TaskStatus.forGrouping('due_on'),
  type:() => TaskTypes.forGrouping('due_on'),
};

const extras = {
  assignee:() => ({ statuses: [TaskStatus.notStarted, TaskStatus.inProgress] }),
  control:() => ({ statuses: [TaskStatus.notStarted, TaskStatus.inProgress] }),
  schedule:() => ({ assignee: user.value.id, statuses: [TaskStatus.notStarted, TaskStatus.inProgress] }),
  topic:() => ({ statuses: [TaskStatus.notStarted, TaskStatus.inProgress] }),
  type:() => ({ statuses: [TaskStatus.notStarted, TaskStatus.inProgress] }),
};

if (Object.keys(fetchable).includes(props.groupBy)) {
  const axios = useAxios();

  axios.get(`/actions/tasks/groups/${props.groupBy}`)
    .then(({ data }) => data)
    .then(({ data }) => {
      group.value = new TableGroup({
        field: props.groupBy,
        setFilters: extras[props.groupBy] ? extras[props.groupBy]() : [],
        options: [...data].map((item) => {
          const col = item.value === null && whenNull[props.groupBy] ? whenNull[props.groupBy] : fetchable[props.groupBy];

          return {
            ...item,
            value: [`${col},eq,${item.value}`],
          };
        }),
      });
    });
} else {
  group.value = new TableGroup({
    field: props.groupBy,
    options: options[props.groupBy] ? options[props.groupBy]() : [],
    setFilters: extras[props.groupBy] ? extras[props.groupBy]() : [],
  });
}

</script>

<template>
  <TaskDataTable
    v-if="group"
    :group-using="group"
    :register-refresh-handler="registerRefreshHandler"
    filterable
    sortable
  />
</template>
