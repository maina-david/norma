<script setup>
import { inject } from 'vue';
import LibryoIcon from '@/collaborate/components/LibryoIcon.vue';
import { useAxios } from '@/vue/composables/useAxios';

const axios = useAxios();
const refresh = inject('refresh');

const props = defineProps({
  reference: { type: Object, required: true },
  related: { type: Object, required: true },
  child: { type: Boolean, default: false },
  canDelete: { type: Boolean, default: false },
  deleteEndpoint: { type: String, required: true },
});

function handleDelete() {
  if (props.canDelete) {
    axios.delete(`${props.deleteEndpoint}/${props.related.id}`)
      .then(() => {
        window.toast.success({ message: 'Unlinked successfully.' });
        refresh();
      })
      .catch(() => {});
  }
}
</script>

<template>
  <div class="flex">
    <div class="flex-grow">
      <div class="text-primary text-sm">
        {{ related.title ?? related.content_draft?.title ?? '-' }}
      </div>
      <div class="text-primary-lighter text-xs">
        {{ related.work.title }}
      </div>
    </div>

    <div v-if="canDelete">
      <button class="border rounded-md px-2 py-1 border-negative text-negative" @click.stop="handleDelete">
        <LibryoIcon name="unlink" icon-size="md" />
      </button>
    </div>
  </div>
</template>
