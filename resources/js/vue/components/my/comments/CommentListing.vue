<script setup>
import { ref } from 'vue';
import { useAxios } from '@/vue/composables/useAxios';
import CommentBox from '@/vue/components/my/comments/CommentBox.vue';
import CommentListItem from '@/vue/components/my/comments/CommentListItem.vue';

const emit = defineEmits(['save', 'delete']);
const props = defineProps({
  relatedId: { type: [String, Number], default: null },
  relation: { type: String, default: null },
  reply: { type: Boolean, default: false },
});

const axios = useAxios();
const comments = ref([]);
const loading = ref(false);

function fetchComments() {
  loading.value =true;
  axios.get(`/comments/related/${props.relation}/${props.relatedId}`)
    .then(({ data }) => data)
    .then(({ data }) => {
      comments.value = [...data];
    })
    .finally(() => {
      loading.value = false;
    });
}

function handleSave(comment) {
  const index = comments.value.findIndex((item) => item.id === comment.id);

  if(index === -1) {
    emit('save');
    comments.value.unshift(comment);
    return;
  }

  comments.value.splice(index, 1, comment);
}

function handleDelete() {
  fetchComments();
  emit('delete');
}

fetchComments();
</script>

<template>
  <div v-loading="loading">
    <CommentBox :related-id="relatedId" :relation="relation" @save="handleSave" />

    <div class="max-h-[500px] overflow-y-auto custom-scroll pr-2">
      <CommentListItem
        v-for="comment in comments"
        :key="comment.id"
        :comment="comment"
        :related-id="relatedId"
        :relation="relation"
        :reply="reply"
        @delete="handleDelete"
      />
    </div>
  </div>
</template>
