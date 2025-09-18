<script setup>
import { computed, inject, provide } from 'vue';
import { useState } from '@/vue/composables/useState';
import ReferenceComments from '@/vue/pages/annotations/components/ReferenceComments.vue';
import AppButton from '@/vue/components/AppButton.vue';
import AppTabs from '@/vue/components/AppTabs.vue';
import ReferenceRelated from '@/vue/pages/annotations/components/ReferenceRelated.vue';
import ReferenceText from '@/vue/pages/annotations/components/ReferenceText.vue';
import ReferenceSummary from '@/vue/pages/annotations/components/ReferenceSummary.vue';
import ReferenceRequirement from '@/vue/pages/annotations/components/ReferenceRequirement.vue';
import NormaIcon from '@/collaborate/components/NormaIcon.vue';
import { useAxios } from '@/vue/composables/useAxios';
import ReferenceContentExtracts from '@/vue/pages/annotations/components/ReferenceContentExtracts.vue';

const axios = useAxios();
const can = inject('can');
const props = defineProps({ reference: { type: Object, required: true } });
const fetchOneReference = inject('fetchOneReference');
const [loading, setLoading] = useState(true);

function refresh() {
  setLoading(true);
  fetchOneReference(props.reference.id).finally(() => setLoading(false));
}

const tabs = computed(() => {
  const items = ['Related', 'Text'];

  if (props.reference.reference_content_extracts_count > 0) {
    items.push('Extracts');
  }

  if (props.reference.type === 11) {
    items.push('Summary');
  }

  if (props.reference.requirement_count + props.reference.requirement_draft_count > 0) {
    items.push('Requirement');
  }

  return items;
});

function requestRequirement() {
  setLoading(true);
  axios.post(`/references/${props.reference.id}/requirement`)
    .then(() => refresh())
    .finally(() => setLoading(false));
}

provide('refresh', refresh);
refresh();
</script>

<template>
  <div v-loading="loading" class="border-t border-gray-200 px-6 py-4">
    <div class="h-10">
      <AppButton v-if="reference.requirement_count === 0 && reference.requirement_draft_count === 0 && can('collaborate.corpus.reference.requirement.create')" @click="requestRequirement">
        <NormaIcon name="marker" />
      </AppButton>
    </div>
    <AppTabs :tabs="tabs">
      <template #default="{ active }">
        <ReferenceRelated v-if="active === 'Related'" :reference="reference" />
        <KeepAlive>
          <ReferenceText v-if="active === 'Text'" :reference="reference" />
        </KeepAlive>
        <KeepAlive>
          <ReferenceContentExtracts v-if="active === 'Extracts'" :reference="reference" />
        </KeepAlive>
        <ReferenceSummary v-if="active === 'Summary'" :reference="reference" />
        <ReferenceRequirement v-if="active === 'Requirement'" :reference="reference" />
      </template>
    </AppTabs>

    <div class="mt-6">
      <ReferenceComments :reference-id="reference.id" />
    </div>
  </div>
</template>
