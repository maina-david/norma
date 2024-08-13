<script setup>
import { ref } from 'vue';
import UserListingSelector from '@/vue/components/my/users/UserListingSelector.vue';
import TaskInlineDropDownInput from '@/vue/components/my/tasks/TaskInlineDropDownInput.vue';
import AppIcon from '@/vue/components/AppIcon.vue';
import GroupedAvatars from '@/vue/components/my/users/GroupedAvatars.vue';
import AppButton from '@/vue/components/AppButton.vue';

const props = defineProps({
  row: { type: Object, required: true },
  rowIndex: { type: Number, default: null },
});

const selected = ref((props.row.followers ?? []).map((item) => item.id));

function triggerSave(callback) {
  callback(selected.value);
}
</script>

<template>
  <div class="flex justify-center">
    <TaskInlineDropDownInput field="followers" :row="row" :row-index="rowIndex">
      <template #display="{ toggle, changeValue }">
        <div v-if="row.followers.length > 0" class="relative group">
          <button class="group-hover:flex hidden font-semibold absolute -top-1 -left-1 bg-white h-4 w-4 rounded-full border border-libryo-gray-200 items-center justify-center" @click="() => triggerSave({ id: null }, changeValue)">
            <span class="pb-0.5">x</span>
          </button>

          <button @click.prevent="toggle">
            <GroupedAvatars :users="row.followers" />
          </button>
        </div>

        <button v-else data-tippy-content="Unassigned" class="tippy rounded-full w-9 h-9 bg-libryo-gray-300 flex items-center justify-center" @click.prevent="toggle">
          <AppIcon name="user" size="4" class="text-libryo-gray-800" />
        </button>
      </template>

      <template #default="{ changeValue, toggle }">
        <div>
          <div class="flex justify-end pr-2 pt-1 pb-1">
            <AppButton @click="() => { triggerSave(changeValue); toggle();}">
              {{ $t('actions.save') }}
            </AppButton>
          </div>

          <UserListingSelector v-model="selected" multiple />
        </div>
      </template>
    </TaskInlineDropDownInput>
  </div>
</template>
