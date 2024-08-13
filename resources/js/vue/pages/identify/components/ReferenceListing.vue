<script setup>
import { computed, inject } from 'vue';
import BulkActions from '@/vue/pages/identify/components/BulkActions.vue';
import AppButton from '@/vue/components/AppButton.vue';
import { useAxios } from '@/vue/composables/useAxios';
import ReferenceCard from './ReferenceCard.vue';

const scrollToReference = inject('scrollToReference');
const props = defineProps({
  loading: { type: Boolean, required: true },
  references: { type: Array, required: true },
  selected: { type: Object, required: true },
  selectedObjects: { type: Object, required: true },
  toggleSelected: { type: Function, required: true },
});

const axios = useAxios();
const can = inject('can');
const fetchAllReferences = inject('fetchAllReferences');
const expression = inject('expression');
const workReference = inject('workReference');
const bulkEnabled = can('collaborate.corpus.work-expression.use-bulk-actions');
const citations = computed(() => props.references.filter((reference) => reference.type !== 16));

function handleCreateReference() {
  axios.post(`/corpus/expressions/${expression.id}/identify/references/${workReference}/insert`, {}, { baseURL: '/' })
    .then(() => fetchAllReferences());
}
</script>

<template>
  <div v-loading="loading" class="flex-grow overflow-y-auto custom-scroll space-y-2 mt-2 pr-2">
    <BulkActions v-if="bulkEnabled" class="sticky top-0 z-[1]" :selected="selected" :selected-objects="selectedObjects" />

    <ReferenceCard
      v-for="reference in citations"
      :id="`ref-${reference.id}`"
      :key="reference.id"
      :selected="selected[reference.id]"
      :reference="reference"
      :bulk-enabled="bulkEnabled"
      @scroll="scrollToReference"
    />

    <div v-if="!loading && workReference && citations.length < 1" class="flex justify-center">
      <AppButton @click.prevent="handleCreateReference">
        Create First Reference
      </AppButton>
    </div>
  </div>
</template>
