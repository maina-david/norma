<script setup>
import { inject } from 'vue';
import LibryoIcon from '@/vue/components/LibryoIcon.vue';

defineProps({
  actionAreas: { type: Boolean, required: true },
  assessmentItems: { type: Boolean, required: true },
  categories: { type: Boolean, required: true },
  hasPlainText: { type: Boolean, default: false },
  contextQuestions: { type: Boolean, required: true },
  legalDomains: { type: Boolean, required: true },
  locations: { type: Boolean, required: true },
  tags: { type: Boolean, required: true },
  toc: { type: Boolean, required: true },
  sourceDocument: { type: Boolean, required: true },
  plainText: { type: Boolean, required: true },
  notes: { type: Boolean, required: true },
  hasNotes: { type: Boolean, required: true },
  noToc: { type: Boolean, default: false },
});

const can = inject('can');
const emit = defineEmits(['toggle']);
</script>

<template>
  <div class="px-4 bg-white border-b border-gray-100 flex items-center space-x-2 flex-shrink-0">
    <div class="flex items-center space-x-2 text-libryo-gray-500 mr-2">
      <LibryoIcon name="eye" /> <span>Show</span>
    </div>

    <a
      v-tooltip="'Action Areas'"
      href="#"
      class="visibility-toggle"
      :class="{ 'active': actionAreas }"
      @click.prevent="() => emit('toggle', 'actionAreas')"
    >
      <LibryoIcon name="clipboard-list-check" />
    </a>

    <a
      v-tooltip="'Assessment Items'"
      href="#"
      class="visibility-toggle"
      :class="{ 'active': assessmentItems }"
      @click.prevent="() => emit('toggle', 'assessmentItems')"
    >
      <LibryoIcon name="check" />
    </a>

    <a
      v-tooltip="'Topics'"
      href="#"
      class="visibility-toggle"
      :class="{ 'active': categories }"
      @click.prevent="() => emit('toggle', 'categories')"
    >
      <LibryoIcon name="hashtag" />
    </a>

    <a
      v-tooltip="'Context Questions'"
      href="#"
      class="visibility-toggle"
      :class="{ 'active': contextQuestions }"
      @click.prevent="() => emit('toggle', 'contextQuestions')"
    >
      <LibryoIcon name="question" />
    </a>

    <a
      v-tooltip="'Legal Domains'"
      href="#"
      class="visibility-toggle"
      :class="{ 'active': legalDomains }"
      @click.prevent="() => emit('toggle', 'legalDomains')"
    >
      <LibryoIcon name="scale-balanced" />
    </a>

    <a
      v-tooltip="'Jurisdictions'"
      href="#"
      class="visibility-toggle"
      :class="{ 'active': locations }"
      @click.prevent="() => emit('toggle', 'locations')"
    >
      <LibryoIcon name="location-dot" />
    </a>

    <a
      v-tooltip="'Tags'"
      href="#"
      class="visibility-toggle"
      :class="{ 'active': tags }"
      @click.prevent="() => emit('toggle', 'tags')"
    >
      <LibryoIcon name="tags" />
    </a>

    <a
      v-if="!noToc"
      v-tooltip="'Table of Content'"
      href="#"
      class="visibility-toggle"
      :class="{ 'active': toc }"
      @click.prevent="() => emit('toggle', 'toc')"
    >
      <LibryoIcon name="list" />
    </a>

    <a
      v-tooltip="'Source Document'"
      href="#"
      class="visibility-toggle"
      :class="{ 'active': sourceDocument }"
      @click.prevent="() => emit('toggle', 'sourceDocument')"
    >
      <LibryoIcon name="file-pdf" />
    </a>

    <a
      v-if="hasPlainText"
      v-tooltip="'Plain Text'"
      href="#"
      class="visibility-toggle"
      :class="{ 'active': plainText }"
      @click.prevent="() => emit('toggle', 'plainText')"
    >
      <LibryoIcon name="file-lines" />
    </a>

    <a
      v-if="can('collaborate.workflows.note.viewAny')"
      v-tooltip="'Notes'"
      href="#"
      class="visibility-toggle"
      :class="{ 'active': notes }"
      @click.prevent="() => emit('toggle', 'notes')"
    >
      <span :class="{ 'bg-primary text-white rounded-full p-2': hasNotes }">
        <LibryoIcon name="notes" />
      </span>
    </a>
  </div>
</template>

<style scoped>
.visibility-toggle {
  @apply p-2 hover:text-info border-t-2 border-transparent;
}
.visibility-toggle.active {
  @apply text-primary border-t-2 border-primary;
}
</style>
