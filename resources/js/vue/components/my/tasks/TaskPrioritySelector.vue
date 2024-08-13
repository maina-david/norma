<script setup>

import TaskOptionsSelector from '@/vue/components/my/tasks/TaskOptionsSelector.vue';
import { getLabel, TaskPriority } from '@/enums/actions/tasks/task-priorities';

const emit = defineEmits(['change']);

defineProps({
  row: { type: Object, required: true },
  rowIndex: { type: Number, default: null },
});

const options = TaskPriority.forSelector();
</script>

<template>
  <TaskOptionsSelector
    :options="options"
    :row="row"
    :row-index="rowIndex"
    :get-label="getLabel"
    field="priority"
    @change="(e) => emit('change', e)"
  />
</template>
