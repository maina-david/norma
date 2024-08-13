<script setup>
import AppIcon from '@/vue/components/AppIcon.vue';
import { getIcon } from '@/enums/actions/tasks/task-types';

defineProps({
  row: { type: Object, required: true },
});

</script>

<template>
  <div class="pr-4">
    <AppIcon :name="getIcon(row.taskable_type)" />
  </div>
</template>
