<script setup>
import { provide, ref } from 'vue';
import ContentReferencePage from '@/vue/components/content-management/ContentReferencePage.vue';
import GeneralActions from '@/vue/pages/identify/components/GeneralActions.vue';
import ReferenceListing from './components/ReferenceListing.vue';

defineProps({
  expression: { type: Object, required: true },
  magi: { type: Boolean, required: true },
  hasNotes: { type: Boolean, required: true },
  plainTextSource: { type: String, default: null },
  appliedFilters: { type: Object, default: () => ({}) },
  pageMeta: { type: Object, default: () => ({}) },
  task: { type: [Object, null], default: null },
  work: { type: Object, required: true },
  userId: { type: Number, required: true },
  workReference: { type: Number, required: true },
  activate: { type: [Number, String], default: null },
});

const openReferenceId = ref(null);

function setOpenReferenceId(referenceId) {
  if (openReferenceId.value === referenceId) {
    openReferenceId.value = null;
    return;
  }

  openReferenceId.value = referenceId;
}

provide('workReference', workReference);
provide('openReferenceId', openReferenceId);
provide('setOpenReferenceId', setOpenReferenceId);
</script>

<template>
  <ContentReferencePage
    :magi="magi"
    :task="task"
    :expression="expression"
    :work="work"
    :applied-filters="appliedFilters"
    :page-meta="pageMeta"
    :user-id="userId"
    :has-notes="hasNotes"
    :plain-text-source="plainTextSource"
    :activate="activate"
    no-toc
    for-identification
  >
    <template #reference-visibility-actions>
      <div class="ml-2">
        <GeneralActions class="sticky top-0 z-[9]" />
      </div>
    </template>

    <template #default="{ setRef, loading, references, selected, selectedObjects, toggleSelected }">
      <ReferenceListing
        :ref="setRef"
        :loading="loading"
        :references="references"
        :selected="selected"
        :selected-objects="selectedObjects"
        :toggle-selected="toggleSelected"
      />
    </template>
  </ContentReferencePage>
</template>
