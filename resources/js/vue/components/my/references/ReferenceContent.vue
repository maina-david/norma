<script setup>
import { ref } from 'vue';
import { debounce } from 'lodash';
import { useAxios } from '@/vue/composables/useAxios';
import TranslationLanguageSelector from '@/vue/components/TranslationLanguageSelector.vue';
import AppIcon from '@/vue/components/AppIcon.vue';

const props = defineProps({
  referenceId: { type: [String, Number], required: true },
  workId: { type: [String, Number], required: true },
  withRequirementLink: { type: Boolean, default: false },
  withAnnotationLink: { type: Boolean, default: false },
});
const axios = useAxios();
const loading = ref(false);
const content = ref('');
const language = ref(' ');

function fetchContent() {
  loading.value = true;

  axios.get(`/references/${props.referenceId}/content/${language.value ?? ''}`)
    .then(({ data }) => data)
    .then(({ data }) => {
      content.value = data.content;
    })
    .finally(() => {
      loading.value = false;
    });
}

const handleChange = debounce(fetchContent, 200);

fetchContent();

function toCollaborate() {
  const parts = window.location.host.split('.');
  parts.shift();
  parts.unshift('collaborate');

  return `https://${parts.join('.')}`;
}
</script>

<template>
  <div v-loading="loading">
    <div class="flex justify-end ">
      <div class="max-w-48 w-full pb-8">
        <TranslationLanguageSelector v-model="language" @update:model-value="handleChange" />
      </div>
    </div>

    <div class="wysiwyg-content norma-legislation">
      <div v-html="content" />
    </div>

    <div class="flex space-x-4 items-center font-semibold mt-8">
      <a :href="`/requirements/citations/${referenceId}`" target="_blank" class="space-x-2 text-primary-lighter">
        <span>{{ $t('actions.action_area.see_in_requirements') }}</span>
        <AppIcon name="arrow-up-right-from-square" size="4" />
      </a>

      <a v-if="$root.isSuperUser()" target="_blank" :href="`${toCollaborate()}/corpus/expressions/annotations/work/${workId}?activate=${referenceId}`" class="space-x-2 text-primary-lighter">
        <span>{{ $t('actions.action_area.see_in_annotations') }}</span>
        <AppIcon name="arrow-up-right-from-square" size="4" />
      </a>
    </div>
  </div>
</template>
