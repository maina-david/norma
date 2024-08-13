<script setup>
import { computed, ref } from 'vue';
import LibryoIcon from '@/collaborate/components/LibryoIcon.vue';
import ConfirmButton from '@/vue/components/ConfirmButton.vue';

const props = defineProps({
  author: { type: Boolean, required: true },
  canEdit: { type: Boolean, required: true },
  canDelete: { type: Boolean, required: true },
  comment: { type: Object, required: true },
});

const emit = defineEmits(['edit', 'delete']);
const editable = computed(() => props.author || props.canDelete);
const deletable = computed(() => props.author || props.canEdit);

const open = ref(false);

function onEdit() {
  open.value = false;
  emit('edit', props.comment);
}

function onDelete() {
  open.value = false;
  emit('delete', props.comment);
}

</script>

<template>
  <div class="border border-gray-300 rounded-lg" :style="`border-color:${comment.taskType.colour};`">
    <div class="flex">
      <div class="p-4 flex-grow wysiwyg-content">
        <div v-html="comment.comment" />
      </div>
      <div v-if="editable || deletable" class="flex-shrink-0">
        <button class="px-3 py-1" @click="open = !open">
          <LibryoIcon name="ellipsis-vertical" />
        </button>

        <div v-if="open" class="fixed inset-0" @click="open = false" />

        <div v-if="open" class="relative">
          <div class="text-xs text-libryo-gray-700 absolute top-0 right-0 z-20 shadow-lg rounded-b border border-gray-100 bg-white divide-gray-200 divide-y">
            <button v-if="editable" class="font-semibold flex items-center px-4 py-2 hover:text-primary" @click="onEdit">
              <LibryoIcon name="pencil" size="3" />
              <span class="ml-2">Edit</span>
            </button>

            <ConfirmButton
              title="Delete Comment"
              message="Are you sure you want to delete this comment?"
              method="DELETE"
              class="!rounded-none !border-none whitespace-nowrap"
              theme="negative"
              @confirm="onDelete"
            >
              <LibryoIcon name="trash" size="3" />
              <span class="ml-3">Delete</span>
            </ConfirmButton>
          </div>
        </div>
      </div>
    </div>
    <div class="text-xs flex justify-end p-1">
      <div class="mr-2 italic">
        {{ $format.dateDiff(comment.created_at) }}
      </div>
      <div class="border rounded-full px-3 text-white" :style="`background-color: ${comment.taskType.colour}`">
        {{ comment.taskType.name }}
      </div>
    </div>
  </div>
</template>
