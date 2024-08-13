<script setup>
import { inject, watch } from 'vue';
import LibryoIcon from '@/collaborate/components/LibryoIcon.vue';
import { useState } from '@/vue/composables/useState';
import DeleteButton from '@/vue/components/DeleteButton.vue';
import WysiwygEditor from '@/vue/components/WysiwygEditor.vue';
import { useAxios } from '@/vue/composables/useAxios';

const axios = useAxios();
const can = inject('can');
const emit = defineEmits(['refresh', 'tab']);
const props = defineProps({
  reference: { type: Object, required: true },
});

const [loading, setLoading] = useState(false);
const [editing, setEditing] = useState(false);
const [content, setContent] = useState(props.reference.summary_draft?.summary_body || '');

function onDelete() {
  emit('refresh');
  emit('tab', 'Live');
}

function updateContent() {
  setLoading(true);
  axios.put(`references/${props.reference.id}/summary/draft`, { body: content.value })
    .then(() => {
      emit('refresh');
      setEditing(false);
    })
    .finally(() => setLoading(false));
}

function applySummary() {
  setLoading(true);
  axios.put(`references/${props.reference.id}/summary`)
    .then(() => {
      setEditing(false);
      onDelete();
    })
    .finally(() => setLoading(false));
}

watch(props, () => {
  setContent(props.reference.summary_draft?.summary_body || '');
});
</script>

<template>
  <div v-loading="loading">
    <div v-if="!editing">
      <div class="relative group py-8">
        <div v-if="reference.summary_draft && reference.summary_draft.summary_body" class="libryo-legislation wysiwyg-content" v-html="reference.summary_draft.summary_body" />
        <div v-else>
          No Content
        </div>

        <button v-if="can('collaborate.requirements.summary.draft.update')" class="block absolute top-2 right-2 hover:text-primary hover:border-primary px-2 py-1 rounded-md border border-gray-500" @click="() => setEditing(true)">
          <LibryoIcon name="pencil" />
        </button>
      </div>

      <div class="flex justify-between items-center mt-4">
        <div>
          <button v-if="can('collaborate.requirements.summary.draft.apply')" class="px-4 py-2 rounded-md border border-gray-800 text-libryo-gray-800 text-xs font-semibold" @click.stop="applySummary">
            <span>Apply Summary</span>
          </button>
        </div>
        <div>
          <DeleteButton v-if="can('collaborate.requirements.summary.draft.delete')" :target="`/references/${reference.id}/summary/draft`" @delete="onDelete">
            Delete Draft Summary
          </DeleteButton>
        </div>
      </div>
    </div>

    <div v-else>
      <WysiwygEditor :model-value="content" @update:model-value="setContent" />

      <div class="flex justify-between items-center mt-4">
        <div>
          <button class="px-4 py-2 rounded-md border border-primary text-primary text-xs font-semibold" @click="updateContent">
            Save Changes
          </button>
        </div>
        <div>
          <button class="px-4 py-2 rounded-md border border-negative text-negative text-xs font-semibold" @click="() => setEditing(false)">
            Cancel
          </button>
        </div>
      </div>
    </div>
  </div>
</template>
