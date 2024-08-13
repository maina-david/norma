<script setup>
import {computed, inject, ref, watch} from 'vue';
import AppTabs from '@/vue/components/AppTabs.vue';
import {metaInfo} from '@/vue/composables/useMetaVisibility';
import LibryoIcon from '@/collaborate/components/LibryoIcon.vue';
import {useState} from '@/vue/composables/useState';
import ReferenceMetaLegalDomainForm from '@/vue/pages/annotations/components/meta/ReferenceMetaLegalDomainForm.vue';
import ReferenceMetaLocationForm from '@/vue/pages/annotations/components/meta/ReferenceMetaLocationForm.vue';
import ReferenceMetaTagForm from '@/vue/pages/annotations/components/meta/ReferenceMetaTagForm.vue';
import ReferenceBulkRequestForm from '@/vue/pages/annotations/components/meta/ReferenceBulkRequestForm.vue';
import AppButton from '@/vue/components/AppButton.vue';
import ReferenceMetaForm from './ReferenceMetaForm.vue';
import ReferenceMetaActionAreasForm from './ReferenceMetaActionAreasForm.vue';
import ReferenceMetaAssessmentItemForm from './ReferenceMetaAssessmentItemForm.vue';
import ReferenceMetaCategoryForm from './ReferenceMetaCategoryForm.vue';
import ReferenceMetaContextQuestionForm from './ReferenceMetaContextQuestionForm.vue';

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
  const items = Object.values(metaInfo)
    .filter((item) => can(item.attach))
    .map((item) => item.label);

  if (!props.bulk) {
    return items;
  }

  items.push('Requirements');
  items.push('Summaries');

  if (can('collaborate.corpus.reference.apply') || can('collaborate.corpus.reference.request-update')) {
    items.push('References');
  }

  items.push('Extracts');

  return items;
});

const activeTab = ref(props.tab || tabs.value[0]);

watch(props, () => {
  activeTab.value = props.tab || tabs.value[0];
});

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
      <LibryoIcon name="times-circle" icon-size="2xl" />
    </button>

    <AppTabs :tabs="tabs" :active="activeTab">
      <template v-if="!loading" #default="{ active }">
        <ReferenceMetaForm
          v-show="active === metaInfo.actionAreas.label"
          :reference="reference"
          :selected="selected"
          meta="actionAreas"
          @refresh="fetchOneReference"
        >
          <template #body="{ set }">
            <ReferenceMetaActionAreasForm :location="work.primary_location_id" :reference="reference" @change="set" />
          </template>
        </ReferenceMetaForm>

        <ReferenceMetaForm
          v-show="active === metaInfo.assessmentItems.label"
          :reference="reference"
          :selected="selected"
          meta="assessmentItems"
          @refresh="fetchOneReference"
        >
          <template #body="{ set }">
            <ReferenceMetaAssessmentItemForm :location="work.primary_location_id" :reference="reference" @change="set" />
          </template>
        </ReferenceMetaForm>

        <ReferenceMetaForm
          v-show="active === metaInfo.categories.label"
          :reference="reference"
          :selected="selected"
          meta="categories"
          @refresh="fetchOneReference"
        >
          <template #body="{ set }">
            <ReferenceMetaCategoryForm :location="work.primary_location_id" :reference="reference" @change="set" />
          </template>
        </ReferenceMetaForm>

        <ReferenceMetaForm
          v-show="active === metaInfo.contextQuestions.label"
          :reference="reference"
          :selected="selected"
          meta="contextQuestions"
          @refresh="fetchOneReference"
        >
          <template #body="{ set }">
            <ReferenceMetaContextQuestionForm :location="work.primary_location_id" :reference="reference" @change="set" />
          </template>
        </ReferenceMetaForm>

        <ReferenceMetaForm
          v-if="work.primary_location_id"
          v-show="active === metaInfo.legalDomains.label"
          :reference="reference"
          :selected="selected"
          meta="legalDomains"
          @refresh="() => fetchAllReferences()"
        >
          <template #body="{ set }">
            <ReferenceMetaLegalDomainForm :location="work.primary_location_id" :reference="reference" @change="set" />
          </template>
        </ReferenceMetaForm>

        <ReferenceMetaForm
          v-show="active === metaInfo.locations.label"
          :reference="reference"
          :selected="selected"
          meta="locations"
          @refresh="() => fetchAllReferences()"
        >
          <template #body="{ set }">
            <ReferenceMetaLocationForm no-magi :reference="reference" @change="set" />
          </template>
        </ReferenceMetaForm>

        <ReferenceMetaForm
          v-show="active === metaInfo.tags.label"
          :reference="reference"
          :selected="selected"
          meta="tags"
          @refresh="fetchOneReference"
        >
          <template #body="{ set }">
            <ReferenceMetaTagForm no-magi :reference="reference" @change="set" />
          </template>
        </ReferenceMetaForm>

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
          :can-delete="false"
          :can-request="can('collaborate.corpus.reference.request-update')"
          apply-action="apply-drafts"
          request-action="request-changes"
          delete-action="delete-references"
          has-draft
        />

        <ReferenceBulkRequestForm
          v-show="bulk && active === 'Extracts'"
          label="Extracts"
          :selected="selected"
          :can-apply="false"
          :can-delete="can('collaborate.corpus.reference-content-extract.delete')"
          :can-request="can('collaborate.corpus.reference-content-extract.create')"
          request-action="generate-extracts"
          apply-action=""
          delete-action="delete-extracts"
        >
        </ReferenceBulkRequestForm>

        <ReferenceBulkRequestForm
          v-show="bulk && active === 'Summaries'"
          label="Summaries"
          :selected="selected"
          :can-apply="can('collaborate.requirements.summary.draft.apply')"
          :can-delete="can('collaborate.requirements.summary.delete')"
          :can-delete-draft="can('collaborate.requirements.summary.draft.delete')"
          :can-request="can('collaborate.requirements.summary.draft.create')"
          apply-action="apply-summary-drafts"
          request-action="request-summary-changes"
          delete-action="delete-summaries"
          delete-draft-action="delete-summary-drafts"
          has-draft
        />
      </template>
    </AppTabs>
  </div>
</template>
