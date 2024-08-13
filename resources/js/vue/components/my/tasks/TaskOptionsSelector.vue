<script setup>
import TaskInlineDropDownInput from '@/vue/components/my/tasks/TaskInlineDropDownInput.vue';

defineProps({
  row: { type: Object, required: true },
  rowIndex: { type: Number, default: null },
  field: { type: String, required: true },
  getBackgroundColour: { type: Function, default: null },
  getLabel: { type: Function, default: null },
  options: { type: Array, required: true },
});
</script>

<template>
  <div class="flex justify-center">
    <TaskInlineDropDownInput
      :field="field"
      :row="row"
      :row-index="rowIndex"
      :get-background-colour="getBackgroundColour"
      :get-label="getLabel"
    >
      <template #default="{ toggle, changeValue }">
        <div>
          <div class="text-left bg-white py-2 flex flex-col">
            <a
              v-for="option in options"
              :key="option.value"
              href="#"
              class="whitespace-nowrap px-2 py-1 hover:text-primary hover:font-semibold"
              @click.prevent.stop="changeValue(option.value); toggle()"
            >
              {{ option.label }}
            </a>
          </div>
        </div>
      </template>
    </TaskInlineDropDownInput>
  </div>
</template>
