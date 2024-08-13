<script setup>
import {inject, ref} from 'vue';
import ContentResourceUploader from '@/vue/pages/identify/components/general-actions/ContentResourceUploader.vue';
import GenerateUpdates from '@/vue/pages/identify/components/general-actions/GenerateUpdates.vue';
import DeleteNonRequirement from '@/vue/pages/identify/components/general-actions/DeleteNonRequirement.vue';
import ApplyDrafts from '@/vue/pages/identify/components/general-actions/ApplyDrafts.vue';
import LibryoIcon from '@/vue/components/LibryoIcon.vue';
import AttemptMatching from "@/vue/pages/identify/components/general-actions/AttemptMatching.vue";

const can = inject('can');
const open = ref(false);
</script>

<template>
  <div class="relative">
    <button v-tooltip="`Actions`" class="hover:text-primary" @click.prevent="open = !open">
      <LibryoIcon name="hand-pointer" />
    </button>

    <div v-if="open" class="fixed inset-0" @click="open = false" />

    <div v-show="open" class="z-50 absolute py-2 space-y-2 px-2 border border-gray-200 rounded-lg bg-white shadow mt-1 flex flex-col" @click="open = false">
      <ContentResourceUploader v-if="can('collaborate.corpus.content-resource.upload')" />

      <GenerateUpdates v-if="can('collaborate.corpus.reference.generate-content-drafts')" />

      <DeleteNonRequirement v-if="can('collaborate.corpus.reference.delete-non-requirements')" />

      <ApplyDrafts v-if="can('collaborate.corpus.reference.apply-content-drafts')" />

      <AttemptMatching v-if="can('collaborate.corpus.reference.apply')" />
    </div>
  </div>
</template>
