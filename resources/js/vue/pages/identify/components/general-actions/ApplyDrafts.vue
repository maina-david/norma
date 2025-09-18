<script setup>
import { inject } from 'vue';
import ConfirmButton from '@/vue/components/ConfirmButton.vue';
import NormaIcon from '@/collaborate/components/NormaIcon.vue';

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
    title="Apply Drafts"
    message="Are you sure you want to apply all draft changes across the whole work?"
    :target="`/corpus/expressions/${expression.id}/identify/references/apply-drafts`"
    base-url="/"
    theme="primary"
    @confirm="handleConfirm"
  >
    <span class="flex items-center">
      <NormaIcon name="check" />
      <span class="ml-2 whitespace-nowrap">Apply Drafts</span>
    </span>
  </ConfirmButton>
</template>
