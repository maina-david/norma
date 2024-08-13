<script setup>
import { computed, watch } from 'vue';

import LibryoIcon from '@/collaborate/components/LibryoIcon.vue';
import { useState } from '@/vue/composables/useState';
import MetaAttacher from '@/vue/pages/identify/components/meta/MetaAttacher.vue';

const props = defineProps({
  selected: { type: Object, required: true },
  selectedObjects: { type: Object, required: true },
});

const [open, setOpen] = useState(false);
const [viewSelected, setViewSelected] = useState(false);

const visible = computed(() => Object.keys(props.selected).filter((key) => !!props.selected[key]));
const selectedRefs = computed(() => visible.value.map((item) => props.selectedObjects[item]));

function toggleOpen() {
  setOpen(!open.value);
  setViewSelected(false);
}

function toggleViewSelected() {
  setViewSelected(!viewSelected.value);
  setOpen(false);
}

watch(visible, () => {
  if (visible.value.length < 1) {
    setOpen(false);
  }
});
</script>

<template>
  <div v-if="visible.length > 0" class="border border-gray-200 rounded-lg bg-neutral-100">
    <div class="flex items-center justify-between">
      <div class="cursor-pointer flex items-center px-4 py-2 text-primary font-semibold text-sm" @click="toggleOpen">
        <LibryoIcon v-if="open" name="angle-down" />
        <LibryoIcon v-else name="angle-right" />

        <div class="ml-2">
          Bulk Actions
        </div>
      </div>

      <div class="text-sm text-primary font-semibold px-4 cursor-pointer" @click="toggleViewSelected">
        {{ selectedRefs.length }} Selected
      </div>
    </div>

    <div v-if="open" class="border-t border-gray-200">
      <MetaAttacher bulk :selected="visible" without-close :reference="{ id: 'bulk' }" />
    </div>

    <div v-if="viewSelected" class="border-t border-gray-200 py-4 text-sm h-[40vh] overflow-y-auto custom-scroll space-y-1">
      <div v-for="reference in selectedRefs" :key="reference.id" class="px-4">
        {{ reference.title ?? reference.content_draft?.title ?? '-' }}
      </div>
    </div>
  </div>
</template>
