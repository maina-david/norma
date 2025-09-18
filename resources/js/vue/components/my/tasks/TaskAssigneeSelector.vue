<script setup>
import { ref } from 'vue';
import UserListingSelector from '@/vue/components/my/users/UserListingSelector.vue';
import TaskInlineDropDownInput from '@/vue/components/my/tasks/TaskInlineDropDownInput.vue';
import UserAvatar from '@/vue/components/my/users/UserAvatar.vue';
import AppIcon from '@/vue/components/AppIcon.vue';

const props = defineProps({
  row: { type: Object, required: true },
  rowIndex: { type: Number, default: null },
});

const selected = ref(props.row.assignee);

function triggerSave(value, callback) {
  callback(value);
}
</script>

<template>
  <div class="flex justify-center">
    <TaskInlineDropDownInput field="assigned_to_id" :row="row" :row-index="rowIndex">
      <template #display="{ toggle, changeValue }">
        <div v-if="row.assignee" class="relative group">
          <button class="group-hover:flex hidden font-semibold absolute -top-1 -left-1 bg-white h-4 w-4 rounded-full border border-norma-gray-200 items-center justify-center" @click="() => triggerSave(null, changeValue)">
            <span class="pb-0.5">x</span>
          </button>

          <button v-if="row.assignee" @click.prevent="toggle">
            <UserAvatar :user="row.assignee" />
          </button>
        </div>

        <button v-else data-tippy-content="Unassigned" class="tippy rounded-full w-9 h-9 bg-norma-gray-300 flex items-center justify-center" @click.prevent="toggle">
          <AppIcon name="user" size="4" class="text-norma-gray-800" />
        </button>
      </template>

      <template #default="{ changeValue, toggle }">
        <UserListingSelector :value="selected" @update:model-value="(e) => { triggerSave(e, changeValue); toggle(); }" />
      </template>
    </TaskInlineDropDownInput>
  </div>
</template>
