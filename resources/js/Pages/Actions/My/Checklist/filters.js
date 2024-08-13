import TableFilter from '@/vue/components/data-table/classes/TableFilter';
import ControlCategorySelector from '@/vue/components/my/categories/ControlCategorySelector.vue';
import SubjectCategorySelector from '@/vue/components/my/categories/SubjectCategorySelector.vue';
import { RiskOfNonCompliance } from '@/enums/actions/checklists/risk-of-non-compliance';

export default [
  new TableFilter({
    name: 'topics',
    label: 'ontology.category.topics',
    multiple: true,
    component: () => SubjectCategorySelector,
  }),
  new TableFilter({
    name: 'control_types',
    label: 'tasks.control_type',
    multiple: true,
    component: () => ControlCategorySelector,
  }),
  new TableFilter({
    name: 'non_compliance',
    label: 'actions.checklist.risk_of_non_compliance',
    multiple: true,
    options: RiskOfNonCompliance.forSelector(),
  }),
  new TableFilter({
    name: 'next_review_before',
    label: 'actions.checklist.next_review_before',
    type: 'date',
  }),
  new TableFilter({
    name: 'next_review_after',
    label: 'actions.checklist.next_review_after',
    type: 'date',
  }),
  new TableFilter({
    name: 'last_answered_before',
    label: 'actions.checklist.last_answered_before',
    type: 'date',
  }),
  new TableFilter({
    name: 'last_answered_after',
    label: 'actions.checklist.last_answered_after',
    type: 'date',
  }),
];
