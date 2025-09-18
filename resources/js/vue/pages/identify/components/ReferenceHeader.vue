<script setup>
import { computed, inject } from 'vue';
import NormaIcon from '@/collaborate/components/NormaIcon.vue';
import ReferenceMeta from '@/vue/pages/annotations/components/meta/ReferenceMeta.vue';
import ReferenceActions from '@/vue/pages/identify/components/ReferenceActions.vue';
import InputElement from '@/vue/components/InputElement.vue';

defineProps({
  reference: { type: Object, required: true },
  selected: { type: Boolean, required: true },
  bulkEnabled: { type: Boolean, default: false },
});

const emit = defineEmits(['open']);

const referenceMeta = inject('referenceMeta');
const toggleSelected = inject('toggleSelected');

const metaVisible = computed(() => Object.values(referenceMeta).some((e) => e));

</script>

<template>
  <div>
    <div class="flex items-center justify-between pl-4 pr-2 group">
      <div
        v-tooltip="`${reference.parent_parent_parent_toc_item_label ? reference.parent_parent_parent_toc_item_label + '<br/>' : ''} ${reference.parent_parent_toc_item_label ? reference.parent_parent_toc_item_label + '<br/>' : ''} ${reference.parent_toc_item_label || ''}`"
        class="r-title"
        data-tippy-delay="400"
        @click="() => emit('open')"
      >
        {{ reference.content_draft?.title ?? reference.title ?? '-' }}
      </div>

      <div class="flex-shrink-0 flex items-center space-x-5">
        <ReferenceActions :reference="reference" />

        <span v-tooltip="`${reference.has_linked_toc ? '' : 'Not '}Linked to Toc Item`" href="#" class="r-taggable" :class="{ 'active-draft': !reference.has_linked_toc }">
          <NormaIcon type="fas" name="list" icon-size="md" />
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
      <ReferenceMeta
        v-if="referenceMeta.assessmentItems"
        no-attach
        no-detach
        meta="assessmentItems"
        :reference="reference"
      />
      <ReferenceMeta
        v-if="referenceMeta.categories"
        no-attach
        no-detach
        meta="categories"
        :reference="reference"
      />
      <ReferenceMeta
        v-if="referenceMeta.contextQuestions"
        no-attach
        no-detach
        meta="contextQuestions"
        :reference="reference"
      />
      <ReferenceMeta
        v-if="referenceMeta.legalDomains"
        no-attach
        no-detach
        meta="legalDomains"
        :reference="reference"
      />
      <ReferenceMeta
        v-if="referenceMeta.locations"
        no-attach
        no-detach
        meta="locations"
        :reference="reference"
      />
      <ReferenceMeta
        v-if="referenceMeta.tags"
        no-attach
        no-detach
        meta="tags"
        :reference="reference"
      />
    </div>
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
