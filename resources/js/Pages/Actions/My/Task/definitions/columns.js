import TableColumn from '@/vue/components/data-table/classes/TableColumn';
import TaskPrioritySelector from '@/vue/components/my/tasks/TaskPrioritySelector.vue';
import TaskImpactSelector from '@/vue/components/my/tasks/TaskImpactSelector.vue';
import TitleColumn from '@/vue/components/my/tasks/TitleColumn.vue';
import TaskTypeColumn from '@/vue/components/my/tasks/TaskTypeColumn.vue';
import TaskAssigneeSelector from '@/vue/components/my/tasks/TaskAssigneeSelector.vue';
import TaskStatusSelector from '@/vue/components/my/tasks/TaskStatusSelector.vue';

export default [
  new TableColumn({
    name: 'title',
    label: 'Title',
    align: 'left',
    href: (item) => `/actions/${item.hash_id}`,
    component: () => TitleColumn,
    minWidth: '40rem',
  }),
  new TableColumn({
    name: 'status',
    label: 'Status',
    field: 'task_status',
    component: () => TaskStatusSelector,
  }),
  new TableColumn({
    name: 'assignee',
    label: 'Assignee',
    sortable: false,
    component: () => TaskAssigneeSelector,
  }),
  new TableColumn({
    name: 'impact',
    label: 'Impact',
    component: () => TaskImpactSelector,
  }),
  new TableColumn({
    name: 'due_on',
    label: 'Date Due',
    date: true,
  }),
  new TableColumn({
    name: 'priority',
    label: 'Priority',
    component: () => TaskPrioritySelector,
  }),
  new TableColumn({
    name: 'type',
    label: 'Type',
    component: () => TaskTypeColumn,
  }),
  new TableColumn({
    name: 'created_at',
    label: 'Date Created',
    date: true,
  }),
];
