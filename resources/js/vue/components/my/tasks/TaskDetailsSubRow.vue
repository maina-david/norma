<script setup>
import TaskInlineInput from '@/vue/components/my/tasks/TaskInlineInput.vue';
import TaskStatusSelector from '@/vue/components/my/tasks/TaskStatusSelector.vue';
import TaskImpactSelector from '@/vue/components/my/tasks/TaskImpactSelector.vue';
import TaskAssigneeSelector from '@/vue/components/my/tasks/TaskAssigneeSelector.vue';
import TaskPrioritySelector from '@/vue/components/my/tasks/TaskPrioritySelector.vue';
import TaskFollowersSelector from '@/vue/components/my/tasks/TaskFollowersSelector.vue';
import UploadedFiles from '@/vue/components/my/files/UploadedFiles.vue';

defineProps({
  row: { type: Object, required: true },
  rowIndex: { type: Number, required: true },
});

</script>

<template>
  <div class="text-left">
    <div>
      <div class="px-6 py-4">
        <div class="mb-1 text-libryo-gray-700 font-semibold">
          {{ $t('workflows.task.title') }}
        </div>
        <div>
          <TaskInlineInput field="title" :row="row" :row-index="rowIndex" />
        </div>
      </div>

      <div class="grid grid-cols-6 gap-8 border-b border-libryo-gray-100 px-6 py-4">
        <div>
          <div class="mb-4 text-libryo-gray-700 font-semibold">
            {{ $t('tasks.status') }}
          </div>

          <TaskStatusSelector :row="row" :row-index="rowIndex" />
        </div>

        <div>
          <div class="mb-4 text-libryo-gray-700 font-semibold text-center">
            {{ $t('tasks.impact') }}
          </div>

          <TaskImpactSelector :row="row" :row-index="rowIndex" />
        </div>

        <div>
          <div class="mb-1 text-libryo-gray-700 font-semibold text-center">
            {{ $t('tasks.assigned_to') }}
          </div>

          <TaskAssigneeSelector :row="row" :row-index="rowIndex" />
        </div>

        <div>
          <div class="mb-4 text-libryo-gray-700 font-semibold text-center">
            {{ $t('tasks.due_on') }}
          </div>
          <div class="text-center">
            <TaskInlineInput
              field="due_on"
              :row="row"
              :row-index="rowIndex"
              type="date"
              :format-value="(val) => $format.date(row.due_on)"
            />
          </div>
        </div>

        <div>
          <div class="mb-4 text-libryo-gray-700 font-semibold text-center">
            {{ $t('workflows.task.priority') }}
          </div>
          <div>
            <TaskPrioritySelector :row="row" :row-index="rowIndex" />
          </div>
        </div>

        <div>
          <div class="mb-1 text-libryo-gray-700 font-semibold text-center">
            {{ $t('tasks.followers') }}
          </div>
          <div>
            <TaskFollowersSelector :row="row" :row-index="rowIndex" />
          </div>
        </div>
      </div>

      <div v-if="row.description" class="px-6 pb-4 pt-8">
        <div class="mb-4 text-libryo-gray-800 font-semibold">
          {{ $t('workflows.task.description') }}
        </div>
        <div>
          <div class="libryo-legislation wysiwyg-content" v-html="row.description" />
        </div>
      </div>
    </div>

    <div class="px-6 pb-4 pt-8">
      <div class="mb-4 text-libryo-gray-800 font-semibold">
        {{ $t('storage.attachment.index_title') }}
      </div>

      <UploadedFiles
        requires-folder
        multiple
        can-upload
        relation="task"
        :related-id="row.id"
        :libryo-id="row.place_id"
      />
    </div>
  </div>
</template>
