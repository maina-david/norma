<script setup>
import TaskOptionsSelector from '@/vue/components/my/tasks/TaskOptionsSelector.vue';

const emit = defineEmits(['change']);
defineProps({
  row: { type: Object, required: true },
  rowIndex: { type: Number, default: null },
});

const options = Array(10).fill('').map((val, index) => ({ value: index + 1, label: index + 1 }));
</script>

<template>
  <TaskOptionsSelector
    :options="options"
    :row="row"
    :row-index="rowIndex"
    field="impact"
    @change="(e) => emit('change', e)"
  />
</template>
