<script setup>

import TaskOptionsSelector from '@/vue/components/my/tasks/TaskOptionsSelector.vue';
import { getBackgroundColour, getColour, getLabel, TaskStatus } from '@/enums/actions/tasks/task-statuses';

const emit = defineEmits(['change']);

defineProps({
  row: { type: Object, required: true },
  rowIndex: { type: Number, default: null },
});

const options = TaskStatus.forSelector();
</script>

<template>
  <TaskOptionsSelector
    :options="options"
    :row="row"
    :row-index="rowIndex"
    :get-label="getLabel"
    :get-colour="getColour"
    :get-background-colour="getBackgroundColour"
    field="task_status"
    @change="(e) => emit('change', e)"
  />
</template>
