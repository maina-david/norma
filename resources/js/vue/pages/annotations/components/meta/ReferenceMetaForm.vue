<script setup>
import { inject } from 'vue';
import AppButton from '@/vue/components/AppButton.vue';
import { metaInfo } from '@/vue/composables/useMetaVisibility';
import { useState } from '@/vue/composables/useState';
import { useAxios } from '@/vue/composables/useAxios';
import NormaIcon from '@/collaborate/components/NormaIcon.vue';
import { confirm } from '@/vue/plugins/bus';

const axios = useAxios();
const can = inject('can');
const applied = inject('appliedMeta');
const setApplied = inject('setAppliedMeta');
const fetchAllReferences = inject('fetchAllReferences');
const [loading, setLoading] = useState(false);
const [related, setRelated] = useState([]);
const emit = defineEmits(['refresh']);
const props = defineProps({
  meta: { type: String, required: true },
  reference: { type: Object, required: true },
  selected: { type: Array, default: () => [] },
});

function updateApplied() {
  const aliases = {
    contextQuestions: 'questions',
    categories: 'topics',
  };

  const existing = [...(applied.value[aliases[props.meta] || props.meta] || [])];

  setApplied({ ...applied.value, [props.meta]: [...existing, ...related.value] });
  setRelated([]);
}

function addMeta() {
  setLoading(true);

  const body = { related: related.value.map((i) => i.id) };

  if (props.reference.id === 'bulk') {
    body.references = [...props.selected];
  }

  axios.post(`/references/${props.reference.id}/meta/${props.meta}`, body)
    .then(() => {
      emit('refresh');
      updateApplied();
    })
    .finally(() => setLoading(false));
}

function deleteMeta() {
  setLoading(true);

  const body = { _method: 'DELETE', related: related.value.map((i) => i.id), references: [...props.selected] };

  axios.post(`/references/${props.reference.id}/meta/${props.meta}`, body)
    .then(() => emit('refresh'))
    .finally(() => setLoading(false));
}
function deleteAllMeta() {
  setLoading(true);

  const body = { _method: 'DELETE', _delete_target: 'all', related: [], references: [...props.selected] };

  axios.post(`/references/${props.reference.id}/meta/${props.meta}`, body)
    .then(() => emit('refresh'))
    .finally(() => setLoading(false));
}

function handleApplyAllDraft() {
  const data = { references: [...props.selected] };

  confirm({ title: 'Apply requested draft changes', message: 'Are you sure you want to apply the requested draft changes?' })
    .then(() => {
      setLoading(true);
      return axios.post(`/references/bulk/meta-drafts/${props.meta}`, data);
    })
    .then(() => fetchAllReferences())
    .catch(() => {})
    .finally(() => setLoading(false));
}

function handleDeleteAllDraft() {
  const data = { _method: 'DELETE', references: [...props.selected] };

  confirm({ title: 'Remove requested draft changes', message: 'Are you sure you want to remove the requested draft changes?' })
    .then(() => {
      setLoading(true);
      return  axios.post(`/references/bulk/meta-drafts/${props.meta}`, data);
    })
    .then(() => fetchAllReferences())
    .catch(() => {})
    .finally(() => setLoading(false));
}
</script>

<template>
  <div v-loading="loading" class="px-2 overflow-y-auto custom-scroll" style="max-height: 30vh;">
    <div class="flex justify-between">
      <div class="text-lg font-light">
        {{ metaInfo[meta].label }}
      </div>

      <div class="flex items-center space-x-4">
        <AppButton v-if="can(metaInfo[meta].attach)" theme="primary" @click="addMeta">
          Add Selected
        </AppButton>

        <template v-if="can(metaInfo[meta].detach) && reference.id === 'bulk'">
          <AppButton v-tooltip="'Delete only the selected metadata.'" theme="negative" @click="deleteMeta">
            Delete Selected
          </AppButton>

          <AppButton v-tooltip="'Delete all the applied metadata.'" theme="negative" @click="deleteAllMeta">
            Delete All
          </AppButton>
        </template>
      </div>
    </div>

    <div v-if="can(metaInfo[meta].apply) && reference.id === 'bulk'" class="flex items-center">
      <div class="text-base text-negative">
        * Draft Changes
      </div>

      <div class="ml-4 flex items-center">
        <button v-tooltip="`Convert draft changes to applied metadata`" class="rounded-full tippy text-primary" @click="handleApplyAllDraft">
          <NormaIcon icon-size="xl" name="circle-check" />
        </button>

        <button v-tooltip="`Remove requested draft changes`" class="rounded-full tippy ml-2 text-secondary" @click="handleDeleteAllDraft">
          <NormaIcon icon-size="xl" name="times-circle" />
        </button>
      </div>
    </div>

    <div class="mt-4">
      <slot name="body" :set="setRelated" />
    </div>
  </div>
</template>
