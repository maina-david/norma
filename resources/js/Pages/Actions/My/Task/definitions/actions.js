import { TaskStatus } from '@/enums/actions/tasks/task-statuses';
import UserSelector from '@/vue/components/my/users/UserSelector.vue';
import { TaskPriority } from '@/enums/actions/tasks/task-priorities';
import TableAction from '@/vue/components/data-table/classes/TableAction';
import impactRange from '@/vue/components/my/tasks/impact-range';

export default [
  new TableAction({
    name: 'change_assignee',
    label: 'workflows.task.change_assignee',
    component: () => UserSelector,
  }),
  new TableAction({
    name: 'change_impact',
    label: 'tasks.change_impact',
    options: impactRange,
  }),
  new TableAction({
    name: 'change_priority',
    label: 'workflows.task.change_priority',
    options: TaskPriority.forSelector(),
  }),
  new TableAction({
    name: 'change_status',
    label: 'workflows.task.change_status',
    options: TaskStatus.forSelector(),
  }),
];
