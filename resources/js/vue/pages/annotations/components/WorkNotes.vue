<script setup>
import { inject } from 'vue';
import AppTabs from '@/vue/components/AppTabs.vue';

const expression = inject('expression');

const tabs = ['Work Notes', 'Work Expression Notes'];

</script>

<template>
  <div class="overflow-hidden px-2 bg-white h-full">
    <AppTabs no-overflow :tabs="tabs" :active="tabs[0]" class="h-full">
      <template #default="{ active }">
        <div v-show="active === tabs[0]" class="h-full overflow-y-auto custom-scroll">
          <turbo-frame id="work-notes" loading="lazy" class="h-full flex flex-col overflow-hidden" :src="`/notes/works/${expression.work_id}/expressions`" />
        </div>
        <div v-show="active === tabs[1]" class="h-full overflow-y-auto custom-scroll">
          <turbo-frame id="expression-notes" loading="lazy" class="h-full flex flex-col overflow-hidden" :src="`/notes/works/${expression.work_id}/expressions/${expression.id}`" />
        </div>
      </template>
    </AppTabs>
  </div>
</template>
