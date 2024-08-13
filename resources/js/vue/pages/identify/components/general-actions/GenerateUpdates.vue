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
    title="Generate Updates"
    message="Are you sure you want to generate updates for citations that have changed?"
    :target="`/corpus/expressions/${expression.id}/identify/references/generate-drafts`"
    base-url="/"
    @confirm="handleConfirm"
  >
    <span class="flex items-center">
      <LibryoIcon name="recycle" />
      <span class="ml-2 whitespace-nowrap">Generate Updates</span>
    </span>
  </ConfirmButton>
</template>
