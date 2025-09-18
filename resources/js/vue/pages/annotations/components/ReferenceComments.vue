<script setup>
import { inject, ref } from 'vue';
import NormaIcon from '@/collaborate/components/NormaIcon.vue';
import CommentInput from '@/vue/components/CommentInput.vue';
import useCRUD from '@/vue/pages/annotations/composables/useCRUD';
import CommentItem from '@/vue/pages/annotations/components/CommentItem.vue';

const props = defineProps({
  referenceId: { type: Number, required: true },
});
const current = ref(null);
const currentContent = ref('');
const editing = ref(null);
const showEditBox = ref(false);
const can = inject('can');
const task = inject('task');
const userId = inject('userId');
const { items, fetchAll, loading, store, update, destroy } = useCRUD(`/references/${props.referenceId}/comments`);
const canEdit = can('collaborate.comments.collaborate.comment.update');
const canDelete = can('collaborate.comments.collaborate.comment.delete');

function submitComment(comment) {
  store({ comment }, `/references/${props.referenceId}/task/${task.id}/comments`)
    .then(() => {
      currentContent.value = '';
      current.value = null;
      fetchAll();
    });
}

fetchAll();
function handleEdit(comment) {
  showEditBox.value = false;
  editing.value = comment;
  setTimeout(() => {
    showEditBox.value = true;
    document.querySelector('.comment-box')?.scrollIntoView();
  }, 100);
}

function updateComment(comment) {
  update({ ...editing.value, comment }, `/references/${props.referenceId}/task/${task.id}/comments/${editing.value.id}`)
    .then(() => {
      editing.value = null;
      showEditBox.value = false;
      fetchAll();
    });
}
function handleDelete(comment) {
  destroy(comment, `/references/${props.referenceId}/task/${task.id}/comments/${comment.id}`)
    .then(() => {
      editing.value = null;
      showEditBox.value = false;
    });
}
</script>

<template>
  <div v-loading="loading">
    <div class="comment-box" />
    <div class="mb-4 flex items-center pl-1">
      <NormaIcon name="comments" />
      <span class="font-semibold ml-2">Comments</span>
    </div>

    <div v-if="task.id" class="mb-4">
      <CommentInput v-if="showEditBox && editing" :model-value="editing.comment" @model-value:update="updateComment" />
      <CommentInput v-else :model-value="currentContent" @model-value:update="submitComment" />
    </div>

    <div v-if="items.length < 1" class="mb-4">
      No Comments
    </div>

    <div class="space-y-2">
      <CommentItem
        v-for="comment in items"
        :key="comment.id"
        :comment="comment"
        :can-edit="canEdit"
        :can-delete="canDelete"
        :author="comment.author_id === userId"
        @edit="handleEdit"
        @delete="handleDelete"
      />
    </div>
  </div>
</template>
