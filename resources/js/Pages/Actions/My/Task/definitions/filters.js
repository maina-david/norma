import { usePage } from '@inertiajs/vue3';
import TableFilter from '@/vue/components/data-table/classes/TableFilter';
import { TaskStatus } from '@/enums/actions/tasks/task-statuses';
import ControlCategorySelector from '@/vue/components/my/categories/ControlCategorySelector.vue';
import SubjectCategorySelector from '@/vue/components/my/categories/SubjectCategorySelector.vue';
import UserSelector from '@/vue/components/my/users/UserSelector.vue';
import { TaskPriority } from '@/enums/actions/tasks/task-priorities';
import { TaskTypes } from '@/enums/actions/tasks/task-types';
import NormaSelector from '@/vue/components/my/normas/NormaSelector.vue';
import ProjectSelector from '@/vue/components/my/projects/ProjectSelector.vue';
import impactRange from '@/vue/components/my/tasks/impact-range';

export default [
  new TableFilter({
    name: 'streams',
    label: 'customer.norma.norma_stream',
    component: () => NormaSelector,
    multiple: true,
    shouldShow: () => {
      const page = usePage();

      return !page.props.stream.single;
    },
  }),
  new TableFilter({
    name: 'assignee',
    label: 'tasks.assigned_to',
    component: () => UserSelector,
  }),
  new TableFilter({
    name: 'project',
    label: 'tasks.project',
    component: () => ProjectSelector,
  }),
  new TableFilter({
    name: 'statuses',
    label: 'tasks.status',
    multiple: true,
    options: TaskStatus.forSelector(),
  }),
  new TableFilter({
    name: 'control_types',
    label: 'tasks.control_type',
    multiple: true,
    component: () => ControlCategorySelector,
  }),
  new TableFilter({
    name: 'topics',
    label: 'ontology.category.topics',
    multiple: true,
    component: () => SubjectCategorySelector,
  }),
  new TableFilter({
    name: 'type',
    label: 'tasks.type',
    options: TaskTypes.forSelector(),
  }),
  new TableFilter({
    name: 'priority',
    label: 'tasks.priority',
    options: TaskPriority.forSelector(),
  }),
  new TableFilter({
    name: 'min_impact',
    label: 'tasks.min_impact',
    options: impactRange,
  }),
  new TableFilter({
    name: 'max_impact',
    label: 'tasks.max_impact',
    options: impactRange,
  }),
  new TableFilter({
    name: 'start_date',
    label: 'tasks.due_date_start',
    type: 'date',
  }),
  new TableFilter({
    name: 'end_date',
    label: 'tasks.due_date_end',
    type: 'date',
  }),
];
