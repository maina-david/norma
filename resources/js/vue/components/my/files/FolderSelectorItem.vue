<script setup>
import { computed, ref } from 'vue';
import { useAxios } from '@/vue/composables/useAxios';
import AppIcon from '@/vue/components/AppIcon.vue';

const emit = defineEmits(['select']);
const props = defineProps({
  folder: { type: Object, default: null },
});

const hasChildren = computed(() => !props.folder || props.folder.children_count > 0);
const open = ref(!props.folder);

const axios = useAxios();
const folders = ref([]);
const loading = ref(false);

function fetchChildren() {
  loading.value = true;

  axios.get(`/storage/folders/tree/${props.folder?.id ?? ''}`)
    .then(({ data }) => data)
    .then(({ data }) => {
      folders.value = [...data];
    })
    .finally(() => {
      loading.value = false;
    });
}

function toggleOpen() {
  open.value = !open.value;

  if (open.value && folders.value.length < 1) {
    fetchChildren();
  }
}

function handleSelect(folder) {
  emit('select', folder);
}

if (!props.folder) {
  fetchChildren();
}
</script>

<template>
  <div v-loading="loading">
    <div v-if="folder" class="flex items-center">
      <div class="w-8 flex-shrink-0 ml-2">
        <button v-if="hasChildren" @click.prevent.stop="toggleOpen">
          <AppIcon v-if="open" name="minus-square" />
          <AppIcon v-else name="plus-square" />
        </button>
      </div>

      <div class="w-8 flex-shrink-0">
        <AppIcon name="folder" />
      </div>

      <div class="flex-grow cursor-pointer hover:text-primary" @click="() => handleSelect(folder)">
        {{ folder.title }}
      </div>
    </div>

    <KeepAlive>
      <div v-if="hasChildren && open" :class="{ 'pl-4': !!folder }" class="space-y-2 mt-2">
        <FolderSelectorItem
          v-for="child in folders"
          :key="child.id"
          :folder="child"
          @select="handleSelect"
        />
      </div>
    </KeepAlive>
  </div>
</template>
