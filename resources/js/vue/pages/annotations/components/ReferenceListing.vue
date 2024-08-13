<script setup>
import { inject } from 'vue';
import ReferenceCard from '@/vue/pages/annotations/components/ReferenceCard.vue';
import ReferenceBulkActions from '@/vue/pages/annotations/components/meta/ReferenceBulkActions.vue';

const scrollToReference = inject('scrollToReference');
defineProps({
  loading: { type: Boolean, required: true },
  references: { type: Array, required: true },
  selected: { type: Object, required: true },
  selectedObjects: { type: Object, required: true },
  toggleSelected: { type: Function, required: true },
});

const can = inject('can');
const bulkEnabled = can('collaborate.corpus.work-expression.use-bulk-actions');
</script>

<template>
  <div v-loading="loading" class="flex-grow overflow-y-auto custom-scroll space-y-2 mt-2 pr-2">
    <ReferenceBulkActions v-if="bulkEnabled" class="sticky top-0 z-[9]" :selected="selected" :selected-objects="selectedObjects" />

    <ReferenceCard
      v-for="reference in references"
      :id="`ref-${reference.id}`"
      :key="reference.id"
      :style="`margin-left:${reference.level}rem`"
      :selected="selected[reference.id]"
      :reference="reference"
      :bulk-enabled="bulkEnabled"
      @scroll="scrollToReference"
    />
  </div>
</template>
