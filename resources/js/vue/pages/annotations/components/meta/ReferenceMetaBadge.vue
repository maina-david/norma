<script setup>
// bg-[#e57061] bg-[#e05773] bg-[#f7c73b] bg-[#64b775] bg-[#45a8ba]
// text-purple-600 bg-purple-100 text-white text-blue-600 bg-blue-100 text-green-600 bg-green-100 text-red-600 bg-red-100 text-norma-gray-600 bg-norma-gray-100
import { computed, inject } from 'vue';
import NormaIcon from '@/collaborate/components/NormaIcon.vue';
import { metaInfo } from '@/vue/composables/useMetaVisibility';
import DeleteButton from '@/vue/components/DeleteButton.vue';

const props = defineProps({
  change: { type: Boolean, default: false },
  noDetach: { type: Boolean, default: false },
  items: { type: Array, required: true },
  draftItems: { type: Array, default: () => [] },
  meta: { type: String, required: true },
});

const emit = defineEmits(['delete']);

const can = inject('can');
function getClasses(row) {
  if (props.change) {
    return 'border border-negative text-negative';
  }

  if (typeof metaInfo[props.meta].colour === 'function') {
    return metaInfo[props.meta].colour(row);
  }

  return metaInfo[props.meta].colour;
}

function isRemoved(item) {
  return props.draftItems.findIndex((i) => i.id === item.id && i.change_status === 0) !== -1;
}

const updated = computed(() => props.items.map((item) => {
  item.removed = isRemoved(item);

  return item;
}));

</script>

<template>
  <span v-for="item in updated" :key="item.id" class="mr-1 mb-1 px-3 pt-1 pb-0.5 rounded-full flex items-center" :class="getClasses(item)">
    <span v-if="change && item.change_status === 1">Add:</span>
    <span v-if="change && item.change_status === 0">Remove:</span>
    <span class="ml-1" :class="{ 'line-through': (change && item.change_status === 0) || item.removed }">{{ item.title }}</span>
    <button v-if="!noDetach && can(metaInfo[meta].detach) && !item.removed" class="ml-2" @click="() => emit('delete', item.id)">
      <NormaIcon name="times" icon-size="sm" />
    </button>
  </span>

  <div v-if="!noDetach && !change && updated.length > 1" class="shrink-0 ml-1">
    <DeleteButton
      v-if="can(metaInfo[meta].detach)"
      v-tooltip="'Request to delete all applied metadata'"
      message="Are you sure you want to delete all applied items?"
      class="px-1.5 pt-0.5 pb-0"
      @delete="() => emit('delete', 'all')"
    >
      <NormaIcon icon-size="sm" name="times" />
    </DeleteButton>
  </div>
</template>
