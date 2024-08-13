<script setup>
import TaskDataTable from '@/vue/components/my/tasks/TaskDataTable.vue';

defineProps({
  registerRefreshHandler: { type: Function, default: null },
});
</script>

<template>
  <TaskDataTable
    :register-refresh-handler="registerRefreshHandler"
    filterable
    sortable
  />
</template>
