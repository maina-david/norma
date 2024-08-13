<script setup>
import { inject } from 'vue';
import ConfirmButton from '@/vue/components/ConfirmButton.vue';
import LibryoIcon from '@/collaborate/components/LibryoIcon.vue';

const expression = inject('expression');
const fetchAllReferences = inject('fetchAllReferences');

function handleConfirm() {
  fetchAllReferences().then(() => {
    window.toast.success({ message: 'Successfully Generated.' });
  });
}
</script>

<template>
  <ConfirmButton
    method="post"
    title="Delete Non-Requirements"
    message="Are you sure you want to delete all references without requirements and attached metadata across the whole work?"
    :target="`/corpus/expressions/${expression.id}/identify/references/delete-non-requirements`"
    base-url="/"
    theme="negative"
    @confirm="handleConfirm"
  >
    <span class="flex items-center">
      <LibryoIcon name="trash" />
      <span class="ml-2 whitespace-nowrap">Delete Non-Requirements</span>
    </span>
  </ConfirmButton>
</template>
