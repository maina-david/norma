<script setup>
import { computed, inject } from 'vue';
import { useAxios } from '@/vue/composables/useAxios';
import DeleteButton from '@/vue/components/DeleteButton.vue';

const axios = useAxios();
const can = inject('can');
const emit = defineEmits(['refresh', 'tab']);
const props = defineProps({
  reference: { type: Object, required: true },
});

function requestUpdate() {
  axios.post(`/references/${props.reference.id}/summary`).then(() => {
    emit('refresh');
    emit('tab', 'Draft');
  });
}

const hasSummary = computed(() => props.reference.summary && props.reference.summary.id);
const hasSummaryDraft = computed(() => props.reference.summary_draft && props.reference.summary_draft.id);

</script>

<template>
  <div>
    <div class="relative py-8">
      <div v-if="reference.summary" class="norma-legislation wysiwyg-content" v-html="reference.summary.summary_body" />
      <div v-else>
        No Summary
      </div>
    </div>

    <div class="flex justify-between items-center mt-4">
      <div>
        <button v-if="(!hasSummary || !hasSummaryDraft) && can('collaborate.requirements.summary.draft.create')" class="px-4 py-2 rounded-md border border-gray-800 text-norma-gray-800 text-xs font-semibold" @click.stop="requestUpdate">
          <span v-if="hasSummary && !hasSummaryDraft">Request Update</span>
          <span v-else>Create Summary</span>
        </button>
      </div>

      <div v-if="reference.summary && reference.summary.id">
        <DeleteButton v-if="can('collaborate.requirements.summary.delete')" :target="`/references/${reference.id}/summary`" @delete="() => emit('refresh')">
          Delete Live Summary
        </DeleteButton>
      </div>
    </div>
  </div>
</template>
