<script setup>
import AppIcon from '@/vue/components/AppIcon.vue';
import CreateTaskButton from '@/vue/components/my/tasks/CreateTaskButton.vue';
import { TaskTypes } from '@/enums/actions/tasks/task-types';
import ReferenceContentExtractsPreview from '@/vue/components/my/references/ReferenceContentExtractsPreview.vue';

const emit = defineEmits(['create']);
defineProps({
  actionArea: { type: Object, default: null },
  actionAreas: { type: Array, default: () => [] },
  referenceId: { type: Number, required: true },
});

const typeRequirement = TaskTypes.requirements;
</script>

<template>
  <div>
    <div class="px-2">
      <div class="text-primary flex justify-between items-center border-b border-norma-gray-200 py-6 px-4">
        <div class="flex items-center">
          <AppIcon name="radar" />
          <div class="ml-2">
            {{ $t('tasks.suggested_tasks') }}
          </div>
        </div>

        <CreateTaskButton
          :action-areas="actionArea ? [actionArea] : actionAreas"
          :type-id="referenceId"
          :type="typeRequirement"
          reference
          @create="emit('create')"
        />
      </div>
    </div>

    <div>
      <ReferenceContentExtractsPreview
        :action-areas="actionArea ? [actionArea] : actionAreas"
        :reference-id="referenceId"
        @create="emit('create')"
      />
    </div>
  </div>
</template>
