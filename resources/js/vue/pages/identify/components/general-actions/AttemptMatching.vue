<script setup>
import {inject} from 'vue';
import ConfirmButton from '@/vue/components/ConfirmButton.vue';
import LibryoIcon from '@/collaborate/components/LibryoIcon.vue';

const expression = inject('expression');
const fetchAllReferences = inject('fetchAllReferences');

function handleConfirm() {
  fetchAllReferences().then(() => {
    window.toast.success({ message: 'Successfully Queued for matching.' });
  });
}
</script>

<template>
  <ConfirmButton
    method="post"
    title="Attempt Toc Matching"
    message="Are you sure you want to attempt matching the ToC Items to the existing references?"
    :target="`/work-expressions/${expression.id}/references/actions`"
    base-url="/"
    theme="primary"
    @confirm="handleConfirm"
    :payload="{ 'action': 'match-tocs' }"
  >
    <span class="flex items-center">
      <LibryoIcon name="list" />
      <span class="ml-2 whitespace-nowrap">Attempt Toc Matching</span>
    </span>
  </ConfirmButton>
</template>
