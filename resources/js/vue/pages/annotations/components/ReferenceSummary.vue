<script setup>
import { computed, inject } from 'vue';
import AppTabs from '@/vue/components/AppTabs.vue';
import ReferenceSummaryLive from '@/vue/pages/annotations/components/ReferenceSummaryLive.vue';
import ReferenceSummaryDraft from '@/vue/pages/annotations/components/ReferenceSummaryDraft.vue';

const refresh = inject('refresh');
const props = defineProps({
  reference: { type: Object, required: true },
});

const tabs = computed(() => {
  const items = ['Live'];

  if (props.reference.summary_draft_count !== null) {
    items.push('Draft');
  }

  return items;
});

</script>

<template>
  <div>
    <AppTabs :tabs="tabs" :active="tabs[1] || tabs[0]">
      <template #default="{ active, activateTab }">
        <ReferenceSummaryLive v-show="active === 'Live'" :reference="reference" @refresh="refresh" @tab="activateTab" />
        <ReferenceSummaryDraft v-show="active === 'Draft'" :reference="reference" @refresh="refresh" @tab="activateTab" />
      </template>
    </AppTabs>
  </div>
</template>
