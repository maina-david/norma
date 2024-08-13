<script setup>
import { inject } from 'vue';
import AppButton from '@/vue/components/AppButton.vue';
import { useState } from '@/vue/composables/useState';
import { useAxios } from '@/vue/composables/useAxios';
import ConfirmButton from '@/vue/components/ConfirmButton.vue';
import LibryoIcon from '@/collaborate/components/LibryoIcon.vue';

const axios = useAxios();
const expression = inject('expression');
const [loading, setLoading] = useState(false);
const fetchAllReferences = inject('fetchAllReferences');
const props = defineProps({
  canApply: { type: Boolean, required: true },
  canDelete: { type: Boolean, required: true },
  canRequest: { type: Boolean, required: true },
  canDeleteDraft: { type: Boolean, default: false },
  applyAction: { type: String, required: true },
  deleteAction: { type: String, required: true },
  deleteDraftAction: { type: String, default: '' },
  requestAction: { type: String, required: true },
  hasDraft: { type: Boolean, default: false },
  label: { type: String, required: true },

  selected: { type: Array, default: () => [] },
});

function handleAction(action, payload = {}) {
  setLoading(true);
  const body = {
    ...payload,
    action: props[`${action}Action`],
  };

  props.selected.forEach((item) => {
    body[`reference_${item}`] = true;
  });

  axios.post(`/work-expressions/${expression.id}/references/actions`, body, { baseURL: '' })
    .then(() => fetchAllReferences())
    .catch(() => {})
    .finally(() => setLoading(false));
}
</script>

<template>
  <div v-loading="loading" class="px-2 overflow-y-auto custom-scroll" style="max-height: 30vh;">
    <div class="flex items-end justify-between flex-wrap">
      <div>
        <div class="text-lg font-light">
          {{ label }}
        </div>

        <div v-if="hasDraft && (canDeleteDraft || canApply)" class="flex items-center">
          <div class="text-base text-negative">
            * Draft Changes
          </div>

          <div class="ml-4 flex items-center">
            <button v-if="canApply" v-tooltip="`Convert draft changes to applied metadata`" class="rounded-full tippy text-primary" @click="() => handleAction('apply')">
              <LibryoIcon icon-size="xl" name="circle-check" />
            </button>

            <button v-if="canDeleteDraft" v-tooltip="`Remove requested draft changes`" class="rounded-full tippy ml-2 text-secondary" @click="() => handleAction('deleteDraft')">
              <LibryoIcon icon-size="xl" name="times-circle" />
            </button>
          </div>
        </div>
      </div>

      <div class="flex items-center space-x-4 pt-2">
        <template v-if="canRequest">
          <slot name="request" :handle-action="handleAction">
            <AppButton theme="primary" @click="() => handleAction('request')">
              Request Update
            </AppButton>
          </slot>
        </template>

        <ConfirmButton
          v-if="canDelete"
          theme="negative"
          title="Delete"
          message="Are you sure you want to delete?"
          method="DELETE"
          @confirm="() => handleAction('delete')"
        >
          Delete Applied
        </ConfirmButton>
      </div>
    </div>
  </div>
</template>
