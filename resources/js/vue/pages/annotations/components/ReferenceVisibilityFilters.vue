<script setup>
import { computed, inject, onBeforeUpdate, ref, watch } from 'vue';
import NormaIcon from '@/vue/components/NormaIcon.vue';
import { useState } from '@/vue/composables/useState';
import AssessmentItemSearch from '@/vue/pages/annotations/components/AssessmentItemSearch.vue';
import ContextQuestionSearch from '@/vue/pages/annotations/components/ContextQuestionSearch.vue';
import LocationSearch from '@/vue/pages/annotations/components/LocationSearch.vue';
import LegalDomainSearch from '@/vue/pages/annotations/components/LegalDomainSearch.vue';
import CategorySearch from '@/vue/pages/annotations/components/CategorySearch.vue';
import AppButton from '@/vue/components/AppButton.vue';
import ActionAreasSearch from '@/vue/pages/annotations/components/ActionAreasSearch.vue';

const [open, setOpen] = useState(false);
const emit = defineEmits(['apply']);
const appliedFilters = inject('appliedFilters');
const props = defineProps({
  actionAreasWithDrafts: { type: Array, required: true },
  assessmentItemsWithDrafts: { type: Array, required: true },
  questionsWithDrafts: { type: Array, required: true },
  locationsWithDrafts: { type: Array, required: true },
  domainsWithDrafts: { type: Array, required: true },
  topicsWithDrafts: { type: Array, required: true },
});

function getPropAttributes(reset = false) {
  return {
    actionAreasWithDrafts: reset ? [] : [...(props.actionAreasWithDrafts || [])],
    assessmentItemsWithDrafts: reset ? [] : [...(props.assessmentItemsWithDrafts || [])],
    questionsWithDrafts: reset ? [] : [...(props.questionsWithDrafts || [])],
    locationsWithDrafts: reset ? [] : [...(props.locationsWithDrafts || [])],
    domainsWithDrafts: reset ? [] : [...(props.domainsWithDrafts || [])],
    topicsWithDrafts: reset ? [] : [...(props.topicsWithDrafts || [])],
  };
}

const attributes = ref(getPropAttributes());

const hasApplied = computed(() => Object.values(attributes.value).some((item) => item.length > 0));

function setLocalAttributes(reset = false) {
  attributes.value = getPropAttributes(reset);
}

watch(props, () => setLocalAttributes());

function setAttribute(attribute, value) {
  attributes.value[attribute] = [...value];
}

let filterRefs = [];

onBeforeUpdate(() => {
  filterRefs = [];
});
const setFilterRef = (el) => {
  if (el) {
    filterRefs.push(el);
  }
};
function handleClear() {
  setLocalAttributes(true);
  emit('apply', attributes.value);
  filterRefs.forEach((el) => {
    if (el.reset) {
      el.reset();
    }
  });
  setOpen(false);
}
function handleApply() {
  emit('apply', attributes.value);
  setOpen(false);
  setLocalAttributes();
}
</script>

<template>
  <div>
    <button
      class="reference-visibility-toggle"
      :class="{ 'active': hasApplied }"
      @click="() => setOpen(!open)"
    >
      <NormaIcon name="filter" />
    </button>

    <div v-if="open" class="fixed inset-0 z-10" @click="() => setOpen(false)" />

    <div class="relative">
      <div v-show="open" class="absolute top-1 left-0 shadow-md border border-gray-100 z-20 bg-white p-4 max-w-sm w-screen space-y-4 overflow-y-auto custom-scroll" style="max-height:50vh;">
        <div class="flex justify-between">
          <AppButton theme="negative" @click="handleClear">
            Clear All
          </AppButton>
          <AppButton theme="primary" @click="handleApply">
            Apply Filters
          </AppButton>
        </div>
        <div>
          <label class="text-sm font-semibold text-gray-600">Action Areas</label>
          <ActionAreasSearch
            :ref="setFilterRef"
            with-remove
            multiple
            :options="appliedFilters.actionAreas || []"
            :value="attributes.actionAreasWithDrafts"
            @change="(e) => setAttribute('actionAreasWithDrafts', e)"
          />
        </div>
        <div>
          <label class="text-sm font-semibold text-norma-gray-600">Assessment Items</label>
          <AssessmentItemSearch
            :ref="setFilterRef"
            with-remove
            multiple
            :options="appliedFilters.assessmentItems || []"
            :value="attributes.assessmentItemsWithDrafts"
            @change="(e) => setAttribute('assessmentItemsWithDrafts', e)"
          />
        </div>
        <div>
          <label class="text-sm font-semibold text-norma-gray-600">Context Questions</label>
          <ContextQuestionSearch
            :ref="setFilterRef"
            with-remove
            multiple
            :options="appliedFilters.questions || []"
            :value="attributes.questionsWithDrafts"
            @change="(e) => setAttribute('questionsWithDrafts', e)"
          />
        </div>
        <div>
          <label class="text-sm font-semibold text-norma-gray-600">Locations</label>
          <LocationSearch
            :ref="setFilterRef"
            with-remove
            multiple
            :options="appliedFilters.locations || []"
            :value="attributes.locationsWithDrafts"
            @change="(e) => setAttribute('locationsWithDrafts', e)"
          />
        </div>
        <div>
          <label class="text-sm font-semibold text-norma-gray-600">Legal Domains</label>
          <LegalDomainSearch
            :ref="setFilterRef"
            with-remove
            multiple
            :options="appliedFilters.domains || []"
            :value="attributes.domainsWithDrafts"
            @change="(e) => setAttribute('domainsWithDrafts', e)"
          />
        </div>
        <div>
          <label class="text-sm font-semibold text-norma-gray-600">Topics</label>
          <CategorySearch
            :ref="setFilterRef"
            with-remove
            multiple
            :options="appliedFilters.topics || []"
            :value="attributes.topicsWithDrafts"
            @change="(e) => setAttribute('topicsWithDrafts', e)"
          />
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.reference-visibility-toggle {
  @apply py-2 px-1.5 hover:text-primary border-t-2 border-transparent;
}
.reference-visibility-toggle.active {
  @apply text-primary border-t-2 border-primary;
}
</style>
