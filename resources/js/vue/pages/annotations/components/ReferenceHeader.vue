<script setup>
import { computed, inject } from 'vue';
import NormaIcon from '@/collaborate/components/NormaIcon.vue';
import { useState } from '@/vue/composables/useState';
import InputElement from '@/vue/components/InputElement.vue';
import ReferenceMeta from '@/vue/pages/annotations/components/meta/ReferenceMeta.vue';
import ReferenceMetaAttacher from '@/vue/pages/annotations/components/meta/ReferenceMetaAttacher.vue';

defineProps({
  reference: { type: Object, required: true },
  selected: { type: Boolean, required: true },
  bulkEnabled: { type: Boolean, default: false },
});

const emit = defineEmits(['open']);

const referenceMeta = inject('referenceMeta');
const toggleSelected = inject('toggleSelected');
const [adding, setAdding] = useState(null);

const metaVisible = computed(() => Object.values(referenceMeta).some((e) => e));

</script>

<template>
  <div>
    <div class="flex items-center justify-between pl-4 pr-2">
      <div
        v-tooltip="`${reference.parent_parent_parent_toc_item_label ? reference.parent_parent_parent_toc_item_label + '<br/>' : ''} ${reference.parent_parent_toc_item_label ? reference.parent_parent_toc_item_label + '<br/>' : ''} ${reference.parent_toc_item_label || ''}`"
        class="r-title"
        data-tippy-delay="400"
        @click="() => emit('open')"
      >
        {{ reference.title ?? reference.content_draft?.title ?? '-' }}
      </div>

      <div class="flex-shrink-0 flex items-center space-x-5">
        <span href="#" class="r-taggable" :class="{ 'active': reference.summary_count !== null, 'active-draft': reference.summary_draft_count !== null }">
          <NormaIcon v-if="reference.summary_draft_count !== null" :name="reference.summary_draft_count > 10 ? 'file-alt' : 'file'" icon-size="md" />
          <NormaIcon v-else :name="reference.summary_count > 10 ? 'file-alt' : 'file'" icon-size="md" />
        </span>

        <span href="#" class="r-taggable" :class="{ 'active': reference.requirement_count, 'active-draft': reference.requirement_draft_count }">
          <NormaIcon type="fas" name="marker" icon-size="md" />
        </span>

        <span href="#" class="r-taggable" :class="{ 'active': reference.linked_children_count || reference.linked_parents_count }">
          <NormaIcon name="paperclip" icon-size="md" />
        </span>

        <span href="#" class="r-taggable" :class="{ 'active': reference.collaborate_comments_count }">
          <NormaIcon name="comments" icon-size="md" />
        </span>

        <div class="w-6">
          <InputElement
            v-if="bulkEnabled"
            class="w-4 h-4"
            :checked="selected"
            type="checkbox"
            @change="() => toggleSelected(reference.id)"
          />
        </div>
      </div>
    </div>

    <div v-if="metaVisible" class="ml-3 text-xs space-y-1 pb-3 notranslate">
      <ReferenceMeta v-if="referenceMeta.actionAreas" meta="actionAreas" :reference="reference" @add="setAdding" />
      <ReferenceMeta v-if="referenceMeta.assessmentItems" meta="assessmentItems" :reference="reference" @add="setAdding" />
      <ReferenceMeta v-if="referenceMeta.categories" meta="categories" :reference="reference" @add="setAdding" />
      <ReferenceMeta v-if="referenceMeta.contextQuestions" meta="contextQuestions" :reference="reference" @add="setAdding" />
      <ReferenceMeta v-if="referenceMeta.legalDomains" meta="legalDomains" :reference="reference" @add="setAdding" />
      <ReferenceMeta v-if="referenceMeta.locations" meta="locations" :reference="reference" @add="setAdding" />
      <ReferenceMeta v-if="referenceMeta.tags" meta="tags" :reference="reference" @add="setAdding" />
    </div>

    <ReferenceMetaAttacher v-if="adding" :tab="adding" :reference="reference" @close="() => setAdding(null)" />
  </div>
</template>

<style>
.ref-card:not(.selecting) .r-title  {
  @apply text-primary;
}

.ref-card.selecting .r-title  {
  @apply text-white;
}

.r-title {
  @apply cursor-pointer font-semibold text-sm py-2 flex-grow pr-2;
}

.r-title:hover {
  @apply text-norma-gray-800;
}

.r-taggable {
  @apply text-norma-gray-200;
}

.r-taggable.active:not(.active-draft) {
  @apply text-primary;
}

.r-taggable.active-draft {
  @apply text-negative;
}
</style>
