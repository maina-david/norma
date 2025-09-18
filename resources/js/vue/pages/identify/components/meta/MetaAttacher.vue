<script setup>
import { computed, inject } from 'vue';
import AppTabs from '@/vue/components/AppTabs.vue';
import NormaIcon from '@/collaborate/components/NormaIcon.vue';
import { useState } from '@/vue/composables/useState';
import ReferenceBulkRequestForm from '@/vue/pages/annotations/components/meta/ReferenceBulkRequestForm.vue';
import AppButton from '@/vue/components/AppButton.vue';

const can = inject('can');
const work = inject('work');
const fetchReference = inject('fetchOneReference');
const fetchAllReferences = inject('fetchAllReferences');
const [loading, setLoading] = useState(false);
const emit = defineEmits(['close']);

const props = defineProps({
  bulk: { type: Boolean, default: false },
  reference: { type: Object, required: true },
  tab: { type: String, default: null },
  withoutClose: { type: Boolean, default: false },
  selected: { type: Array, default: () => [] },
});

const tabs = computed(() => {
  const items = [];

  if (!props.bulk) {
    return items;
  }

  if (can('collaborate.corpus.reference.apply') || can('collaborate.corpus.reference.request-update')) {
    items.push('References');
  }

  items.push('Requirements');

  return items;
});

const activeTab = computed(() => props.tab || tabs.value[0]);

function fetchOneReference() {
  if (props.reference.id === 'bulk') {
    fetchAllReferences();
    return;
  }

  setLoading(true);
  fetchReference(props.reference.id).finally(() => setLoading(false));
}

</script>

<template>
  <div v-loading="loading" class="rounded-lg bg-neutral-100 p-4 relative notranslate">
    <button v-if="!withoutClose" class="absolute bg-white rounded-full -top-3 right-0" @click="() => emit('close')">
      <NormaIcon name="times-circle" icon-size="2xl" />
    </button>

    <AppTabs :tabs="tabs" :active="activeTab">
      <template v-if="!loading" #default="{ active }">
        <ReferenceBulkRequestForm
          v-show="bulk && active === 'Requirements'"
          label="Requirements"
          :selected="selected"
          :can-apply="can('collaborate.corpus.reference.requirement.apply')"
          :can-delete="can('collaborate.corpus.reference.requirement.delete')"
          :can-delete-draft="can('collaborate.corpus.reference.requirement.delete')"
          :can-request="can('collaborate.corpus.reference.requirement.create')"
          request-action="request-requirement"
          apply-action="apply-requirement-drafts"
          delete-action="delete-requirement"
          delete-draft-action="delete-requirement-drafts"
          has-draft
        >
          <template #request="{ handleAction}">
            <AppButton theme="primary" @click="() => handleAction('request')">
              Request Addition
            </AppButton>
            <AppButton theme="primary" @click="() => handleAction('request', { removal: true })">
              Request Removal
            </AppButton>
          </template>
        </ReferenceBulkRequestForm>

        <ReferenceBulkRequestForm
          v-show="bulk && active === 'References'"
          label="References"
          :selected="selected"
          :can-apply="can('collaborate.corpus.reference.apply')"
          :can-delete="can('collaborate.corpus.reference.delete')"
          :can-delete-draft="can('collaborate.corpus.reference.delete')"
          :can-request="can('collaborate.corpus.reference.request-update')"
          apply-action="apply-content-drafts"
          request-action="request-content-changes"
          delete-action="delete-references"
          delete-draft-action="delete-content-changes"
          has-draft
        />
      </template>
    </AppTabs>
  </div>
</template>
