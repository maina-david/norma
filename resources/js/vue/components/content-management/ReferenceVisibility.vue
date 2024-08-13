<script setup>
import { inject } from 'vue';
import LibryoIcon from '@/vue/components/LibryoIcon.vue';
import InputElement from '@/vue/components/InputElement.vue';
import ReferenceVisibilityFilters from '@/vue/pages/annotations/components/ReferenceVisibilityFilters.vue';

defineProps({
  actionAreas: { type: Number, required: true },
  assessmentItems: { type: Number, required: true },
  categories: { type: Number, required: true },
  contextQuestions: { type: Number, required: true },
  legalDomains: { type: Number, required: true },
  locations: { type: Number, required: true },
  tags: { type: Number, required: true },
  summary: { type: Number, required: true },
  requirement: { type: Number, required: true },
  linking: { type: Number, required: true },
  comments: { type: Number, required: true },
  assessmentItemsWithDrafts: { type: Array, required: true },
  actionAreasWithDrafts: { type: Array, required: true },
  questionsWithDrafts: { type: Array, required: true },
  locationsWithDrafts: { type: Array, required: true },
  domainsWithDrafts: { type: Array, required: true },
  topicsWithDrafts: { type: Array, required: true },
  noToggles: { type: Boolean, default: false },
  noSelection: { type: Boolean, default: false },
});

const emit = defineEmits(['toggle', 'bulk', 'apply']);

const can = inject('can');

</script>

<template>
  <div class="bg-white border-b border-gray-100 flex items-center justify-between flex-shrink-0">
    <ReferenceVisibilityFilters
      :action-areas-with-drafts="actionAreasWithDrafts"
      :assessment-items-with-drafts="assessmentItemsWithDrafts"
      :questions-with-drafts="questionsWithDrafts"
      :locations-with-drafts="locationsWithDrafts"
      :domains-with-drafts="domainsWithDrafts"
      :topics-with-drafts="topicsWithDrafts"
      @apply="(e) => emit('apply', e)"
    />

    <slot />

    <div class="flex items-center justify-end space-x-1 flex-grow">
      <div v-if="!noToggles" class="flex items-center space-x-1">
        <a
          v-tooltip="'Action Areas'"
          href="#"
          class="reference-visibility-toggle"
          :class="`active${actionAreas}`"
          @click.prevent="() => emit('toggle', 'actionAreas')"
        >
          <LibryoIcon name="clipboard-list-check" />
        </a>

        <a
          v-tooltip="'Assessment Items'"
          href="#"
          class="reference-visibility-toggle"
          :class="`active${assessmentItems}`"
          @click.prevent="() => emit('toggle', 'assessmentItems')"
        >
          <LibryoIcon name="check" />
        </a>

        <a
          v-tooltip="'Topics'"
          href="#"
          class="reference-visibility-toggle"
          :class="`active${categories}`"
          @click.prevent="() => emit('toggle', 'categories')"
        >
          <LibryoIcon name="hashtag" />
        </a>

        <a
          v-tooltip="'Context Questions'"
          href="#"
          class="reference-visibility-toggle"
          :class="`active${contextQuestions}`"
          @click.prevent="() => emit('toggle', 'contextQuestions')"
        >
          <LibryoIcon name="question" />
        </a>

        <a
          v-tooltip="'Legal Domains'"
          href="#"
          class="reference-visibility-toggle"
          :class="`active${legalDomains}`"
          @click.prevent="() => emit('toggle', 'legalDomains')"
        >
          <LibryoIcon name="scale-balanced" />
        </a>

        <a
          v-tooltip="'Jurisdictions'"
          href="#"
          class="reference-visibility-toggle"
          :class="`active${locations}`"
          @click.prevent="() => emit('toggle', 'locations')"
        >
          <LibryoIcon name="location-dot" />
        </a>

        <a
          v-tooltip="'Tags'"
          href="#"
          class="reference-visibility-toggle"
          :class="`active${tags}`"
          @click.prevent="() => emit('toggle', 'tags')"
        >
          <LibryoIcon name="tags" />
        </a>

        <a
          v-tooltip="'Summaries'"
          href="#"
          class="reference-visibility-toggle"
          :class="`active${summary}`"
          @click.prevent="() => emit('toggle', 'summary')"
        >
          <LibryoIcon name="file" />
        </a>

        <a
          v-tooltip="'Requirements'"
          href="#"
          class="reference-visibility-toggle"
          :class="`active${requirement}`"
          @click.prevent="() => emit('toggle', 'requirement')"
        >
          <LibryoIcon name="marker" />
        </a>

        <a
          v-tooltip="'Linked'"
          href="#"
          class="reference-visibility-toggle"
          :class="`active${linking}`"
          @click.prevent="() => emit('toggle', 'linking')"
        >
          <LibryoIcon name="paperclip" />
        </a>

        <a
          v-tooltip="'Comments'"
          href="#"
          class="reference-visibility-toggle"
          :class="`active${comments}`"
          @click.prevent="() => emit('toggle', 'comments')"
        >
          <LibryoIcon name="comments" />
        </a>
      </div>

      <slot name="right" />

      <div class="ml-2 w-6">
        <InputElement v-if="!noSelection && can('collaborate.corpus.work-expression.use-bulk-actions')" class="w-4 h-4" type="checkbox" @change="(e) => emit('bulk', e)" />
      </div>
    </div>
  </div>
</template>

<style scoped>
.reference-visibility-toggle {
  @apply py-2 px-1.5 hover:text-info border-t-2 border-transparent;
}
.reference-visibility-toggle.active1 {
  @apply text-negative border-t-2 border-negative;
}
.reference-visibility-toggle.active2 {
  @apply text-primary border-t-2 border-primary;
}
.reference-visibility-toggle.active3 {
  @apply text-warning border-t-2 border-warning;
}
</style>
