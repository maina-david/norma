<script setup>
import { ref } from 'vue';
import FolderSelectorItem from '@/vue/components/my/files/FolderSelectorItem.vue';
import DropDown from '@/vue/components/DropDown.vue';
import AppIcon from '@/vue/components/AppIcon.vue';

const value = defineModel();
const emit = defineEmits(['select']);
const selected = ref('');

function handleSelect(folder, toggle) {
  selected.value = folder.title;
  emit('select', folder);
  value.value = folder.id;
  toggle();
}

function clearSelected() {
  selected.value = '';
  emit('select', null);
  value.value = null;
}
</script>

<template>
  <div>
    <DropDown>
      <template #trigger="{ toggle }">
        <div class="relative flex items-center h-11 px-3 py-2 cursor-pointer p-3 border rounded-md border-norma-gray-200 flex-row justify-between bg-white text-ellipsis" @click="toggle">
          <span class="flex-grow pr-8">{{ selected }}</span>

          <button v-if="selected" class="flex-shrink-0" @click="clearSelected">
            <AppIcon name="times" />
          </button>
        </div>
      </template>

      <template #default="{ toggle }">
        <div class="bg-white pl-1 pr-4 py-4">
          <FolderSelectorItem @select="(e) => handleSelect(e, toggle)" />
        </div>
      </template>
    </DropDown>
  </div>
</template>
