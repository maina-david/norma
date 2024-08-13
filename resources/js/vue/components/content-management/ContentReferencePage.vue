<script setup>
import { computed, inject, onMounted, provide, ref } from 'vue';
import useCRUD from '@/vue/pages/annotations/composables/useCRUD';
import { useState } from '@/vue/composables/useState';
import { useMetaVisibility } from '@/vue/composables/useMetaVisibility';
import { useReferenceVisibility } from '@/vue/composables/useReferenceVisibility';
import { useBulkActions } from '@/vue/composables/useBulkActions';
import Pagination from '@/vue/components/Pagination.vue';
import ConfirmAction from '@/collaborate/components/ConfirmAction.vue';
import WorkNotes from '@/vue/pages/annotations/components/WorkNotes.vue';
import WorkExpressionSource from '@/vue/pages/annotations/components/WorkExpressionSource.vue';
import WorkExpressionContent from '@/vue/pages/annotations/components/WorkExpressionContent.vue';
import WorkExpressionToC from '@/vue/pages/annotations/components/WorkExpressionToC.vue';
import MetaVisibility from './MetaVisibility.vue';
import ReferenceVisibility from './ReferenceVisibility.vue';

const props = defineProps({
  expression: { type: Object, required: true },
  magi: { type: Boolean, required: true },
  forIdentification: { type: Boolean, default: false },
  noToc: { type: Boolean, default: false },
  hasNotes: { type: Boolean, required: true },
  plainTextSource: { type: String, default: null },
  appliedFilters: { type: Object, default: () => ({}) },
  pageMeta: { type: Object, default: () => ({}) },
  task: { type: [Object, null], default: null },
  work: { type: Object, required: true },
  userId: { type: Number, required: true },
  activate: { type: [Number, String], default: null },
});

const can = inject('can');
const { toc, sourceDocument, notes, meta, mataIncludes, toggleMeta, plainText } = useMetaVisibility();
const { selected, selectedObjects, toggleSelected: toggleMainSelected, setSelected, setSelectedObject } = useBulkActions();
const { referenceVisibility: refVisibility, visibilityFilter, toggleReferenceVisibility, setDetailedFilters } = useReferenceVisibility();
const activateReference = ref(props.activate ? parseInt(props.activate, 10) : null);
const contentComponent = ref(null);
const referenceListComponent = ref(null);
const referencePayload = computed(() => ({ ...mataIncludes.value, ...visibilityFilter.value }));
const activatePayload = computed(() => ({ activate: activateReference.value }));

const [appliedMeta, setAppliedMeta] = useState(props.pageMeta);

const {
  items,
  getIds,
  fetchAll: fetchReferences,
  pagination: referencePagination,
  changePage: changeReferencePage,
  loading: referencesLoading,
  fetchOne,
} = useCRUD(`/work-expressions/${props.expression.id}/task/${props.task.id || '-'}/references`, referencePayload, 50, activatePayload);

function fetchAll() {
  return new Promise((res) => {
    fetchReferences(() => {
      activateReference.value = null;
      res();
    });
  });
}

const toggleSelectAll = (event) => {
  setSelected(getIds(), event.target.checked);
  setSelectedObject(items.value);
};

function toggleSelected(key) {
  const item = items.value.filter((i) => i.id === key);
  toggleMainSelected(key, item[0] || null);
}

const fetchOneReference = (id, params = {}) => {
  return fetchOne(id, { ...referencePayload.value, ...params });
};

function updateDetailedFilters(filters) {
  setDetailedFilters(filters);
}

function scrollToReference(reference) {
  contentComponent.value?.setScrollTo(reference.selectors);
  contentComponent.value?.changePage(reference.volume);
}

function handleChangeReferencesPage(page) {
  if (page !== referencePagination.page) {
    return changeReferencePage(page).then(() => referenceListComponent.value.$el.scrollTo(0, 0));
  }
}

const contentWidth = computed(() => {
  if (notes.value && toc.value) {
    return 'w-1/3';
  }

  if (notes.value || toc.value) {
    return 'w-1/2';
  }

  return '';
});

function setContentRef(el) {
  referenceListComponent.value = el;
}

provide('appliedFilters', props.appliedFilters);
provide('appliedMeta', appliedMeta);
provide('activateReference', activateReference);
provide('changeReferencePage', handleChangeReferencesPage);
provide('expression', props.expression);
provide('fetchAllReferences', fetchAll);
provide('fetchOneReference', fetchOneReference);
provide('referenceMeta', meta);
provide('scrollToReference', scrollToReference);
provide('setAppliedMeta', setAppliedMeta);
provide('task', props.task);
provide('toggleSelected', toggleSelected);
provide('work', props.work);
provide('userId', props.userId);
provide('magi', props.magi);
provide('forIdentification', props.forIdentification);

fetchAll();

const showingSourceContent = computed(() => sourceDocument.value || plainText.value);

onMounted(() => {
  window.Split(['#split-left-side', '#split-right-side'], { sizes: [60, 40] });
});
</script>

<template>
  <div class="h-full w-full flex space-x-2 splitter">
    <div id="split-left-side" class="h-full overflow-hidden flex flex-col">
      <MetaVisibility
        v-bind="meta"
        :has-plain-text="!!plainTextSource"
        :toc="toc"
        :source-document="sourceDocument"
        :plain-text="plainText"
        :notes="notes"
        :has-notes="hasNotes"
        :no-toc="noToc"
        @toggle="toggleMeta"
      />

      <div class="flex flex-grow overflow-hidden">
        <KeepAlive v-if="!noToc">
          <WorkExpressionToC v-if="toc" :class="contentWidth" :expression="expression" />
        </KeepAlive>
        <KeepAlive>
          <WorkNotes v-if="can('collaborate.workflows.note.viewAny') && notes" :class="contentWidth" />
        </KeepAlive>

        <WorkExpressionSource v-if="sourceDocument" :class="contentWidth" :expression="expression" />

        <KeepAlive>
          <WorkExpressionSource v-if="plainTextSource && plainText" :plain-text="plainTextSource" :class="contentWidth" :expression="expression" />
        </KeepAlive>

        <KeepAlive>
          <WorkExpressionContent v-if="!showingSourceContent" ref="contentComponent" :class="contentWidth" :expression="expression" />
        </KeepAlive>
      </div>
    </div>

    <div id="split-right-side" class="h-full overflow-hidden flex flex-col">
      <ReferenceVisibility
        class="pl-4 pr-6"
        v-bind="refVisibility"
        @toggle="toggleReferenceVisibility"
        @bulk="toggleSelectAll"
        @apply="(e) => updateDetailedFilters(e)"
      >
        <slot name="reference-visibility-actions" />
      </ReferenceVisibility>

      <slot
        :set-ref="setContentRef"
        :loading="referencesLoading"
        :references="items"
        :selected="selected"
        :selected-objects="selectedObjects"
        :toggle-selected="toggleSelected"
      />

      <Pagination :last-page="referencePagination.lastPage" :per-page="referencePagination.perPage" :current="referencePagination.page" @page="handleChangeReferencesPage" />
    </div>

    <ConfirmAction />
  </div>
</template>
