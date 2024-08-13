<script setup>
// bg-[#e57061] bg-[#e05773] bg-[#f7c73b] bg-[#64b775] bg-[#45a8ba]
import { inject } from 'vue';
import { metaInfo } from '@/vue/composables/useMetaVisibility';
import { useAxios } from '@/vue/composables/useAxios';
import { useState } from '@/vue/composables/useState';
import LibryoIcon from '@/collaborate/components/LibryoIcon.vue';
import { confirm } from '@/vue/plugins/bus';
import ReferenceMetaBadge from './ReferenceMetaBadge.vue';

const axios = useAxios();
const can = inject('can');
const fetchOneReference = inject('fetchOneReference');
const fetchAllReferences = inject('fetchAllReferences');
const props = defineProps({
  meta: { type: String, required: true },
  noAttach: { type: Boolean, default: false },
  noDetach: { type: Boolean, default: false },
  reference: { type: Object, required: true },
});

const emit = defineEmits(['add']);

const [loading, setLoading] = useState(false);

function handleFetch() {
  if (['legalDomains', 'locations'].includes(props.meta)) {
    return fetchAllReferences();
  }

  return fetchOneReference(props.reference.id);
}

function handleDelete(related) {
  setLoading(true);
  axios.delete(`/references/${props.reference.id}/meta/${props.meta}/${related}`)
    .then(() => handleFetch())
    .finally(() => setLoading(false));
}
function handleDeleteDraft(related) {
  setLoading(true);
  axios.delete(`/references/${props.reference.id}/meta-drafts/${props.meta}/${related}`)
    .then(() => handleFetch())
    .finally(() => setLoading(false));
}

function handleApplyAllDraft() {
  const data = { references: [props.reference.id] };

  confirm({ title: 'Apply requested draft changes', message: 'Are you sure you want to apply the requested draft changes?' })
    .then(() => {
      setLoading(true);
      return axios.post(`/references/bulk/meta-drafts/${props.meta}`, data);
    })
    .then(() => handleFetch())
    .catch(() => {})
    .finally(() => setLoading(false));
}

function handleDeleteAllDraft() {
  const data = { _method: 'DELETE', references: [props.reference.id] };

  confirm({ title: 'Remove requested draft changes', message: 'Are you sure you want to remove the requested draft changes?' })
    .then(() => {
      setLoading(true);
      return  axios.post(`/references/bulk/meta-drafts/${props.meta}`, data);
    })
    .then(() => handleFetch())
    .catch(() => {})
    .finally(() => setLoading(false));
}
</script>

<template>
  <div v-loading="loading">
    <div v-if="reference[metaInfo[meta].field]" class="flex font-semibold text-xs">
      <div class="px-1 pt-0.5 w-6 h-5 flex justify-center items-center shrink-0">
        <LibryoIcon icon-size="md" :name="metaInfo[meta].icon" />
      </div>

      <div v-if="!noAttach && can(metaInfo[meta].attach)" class="flex items-start shrink-0 mr-1 pt-0.5">
        <button class="rounded-full mt-0.5" @click="() => emit('add', metaInfo[meta].label)">
          <LibryoIcon icon-size="xl" name="plus-circle" />
        </button>
      </div>

      <div class="flex-grow flex flex-wrap">
        <ReferenceMetaBadge
          :items="reference[metaInfo[meta].field]"
          :draft-items="reference[metaInfo[meta].draft_field]"
          :meta="meta"
          :no-detach="noDetach"
          @delete="handleDelete"
        />
      </div>
    </div>

    <div v-if="reference[metaInfo[meta].draft_field] && reference[metaInfo[meta].draft_field].length > 0" class="w-full mt-1 font-semibold text-xs">
      <div class="text-negative mb-2 flex items-center">
        <div>* Draft Changes</div>

        <div v-if="!noAttach && can(metaInfo[meta].apply)" class="ml-4 flex items-center">
          <button v-tooltip="`Convert draft changes to applied`" class="rounded-full tippy text-primary" @click="handleApplyAllDraft">
            <LibryoIcon icon-size="xl" name="circle-check" />
          </button>

          <button v-if="!noDetach" v-tooltip="`Remove requested draft changes`" class="rounded-full tippy ml-2 text-secondary" @click="handleDeleteAllDraft">
            <LibryoIcon icon-size="xl" name="times-circle" />
          </button>
        </div>
      </div>

      <div class="flex-grow flex flex-wrap">
        <ReferenceMetaBadge
          change
          :items="reference[metaInfo[meta].draft_field]"
          :meta="meta"
          :no-detach="noDetach"
          @delete="handleDeleteDraft"
        />
      </div>
    </div>
  </div>
</template>
