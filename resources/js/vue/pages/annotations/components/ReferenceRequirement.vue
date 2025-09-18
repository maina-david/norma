<script setup>
import { inject } from 'vue';
import NormaIcon from '@/collaborate/components/NormaIcon.vue';
import DeleteButton from '@/vue/components/DeleteButton.vue';
import ConfirmButton from '@/vue/components/ConfirmButton.vue';
import AppButton from '@/vue/components/AppButton.vue';
import { useAxios } from '@/vue/composables/useAxios';
import { useState } from '@/vue/composables/useState';

const props = defineProps({
  reference: { type: Object, required: true },
});

const axios = useAxios();
const refresh = inject('refresh');
const task = inject('task');
const can = inject('can');
const [loading, setLoading] = useState(false);

function requestRemoval() {
  setLoading(true);
  axios.post(`/references/${props.reference.id}/requirement`)
    .then(() => refresh())
    .finally(() => setLoading(false));
}
</script>

<template>
  <div v-loading="loading" class="pt-4">
    <div v-if="reference.requirement_draft_count === 1" class="flex flex-col items-center">
      <div class="font-semibold">
        <NormaIcon name="circle-info" class="text-info-darker" />
        <span class="text-info-darker ml-4">Has Draft Requirement</span>
      </div>

      <div class="flex justify-center space-x-8 mt-4">
        <ConfirmButton
          v-if="can('collaborate.corpus.reference.requirement.apply')"
          method="put"
          title="Approve"
          message="Are you sure you want to approve the draft requirement?"
          :target="`/references/${reference.id}/requirement`"
          theme="primary"
          @confirm="refresh"
        >
          <span class="flex space-x-1">
            <span>Approve</span>
            <span v-if="reference.requirement_draft_type === 1">Addition</span>
            <span v-else-if="reference.requirement_draft_type === 0">Removal</span>
          </span>
        </ConfirmButton>

        <DeleteButton
          v-if="task.is_assignee || can('collaborate.corpus.reference.requirement.delete')"
          :target="`/references/${reference.id}/requirement/draft/task/${task.id || ''}`"
          @delete="refresh"
        >
          Delete Draft Requirement
        </DeleteButton>
      </div>
    </div>

    <div v-else-if="reference.requirement_count === 1" class="flex flex-col items-center">
      <div class="font-semibold">
        <NormaIcon name="circle-info" class="text-info-darker" />
        <span class="text-info-darker ml-4">Has Requirement</span>
      </div>

      <div class="flex justify-center space-x-8 mt-4">
        <AppButton v-if="can('collaborate.corpus.reference.requirement.create')" @click="requestRemoval">
          Request Removal
        </AppButton>

        <DeleteButton v-if="can('collaborate.corpus.reference.requirement.delete-applied')" :target="`/references/${reference.id}/requirement`" @delete="refresh">
          Delete Requirement
        </DeleteButton>
      </div>
    </div>
  </div>
</template>
