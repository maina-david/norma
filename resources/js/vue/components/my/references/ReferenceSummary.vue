<script setup>
import { ref } from 'vue';
import { debounce } from 'lodash';
import { useAxios } from '@/vue/composables/useAxios';
import TranslationLanguageSelector from '@/vue/components/TranslationLanguageSelector.vue';

const props =defineProps({
  referenceId: { type: Number, required: true },
  workId: { type: Number, required: true },
});

const axios = useAxios();
const loading = ref(false);
const content = ref('');
const language = ref(' ');

function fetchContent() {
  loading.value = true;

  axios.get(`/references/${props.referenceId}/summary/${language.value ?? ''}`)
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

</script>

<template>
  <div v-loading="loading">
    <div v-if="!loading && !content" class="text-center text-norma-gray-600 pt-8">
      {{ $t('requirements.no_notes') }}
    </div>

    <div v-if="!loading">
      <div class="flex justify-end ">
        <div class="max-w-48 w-full pb-8">
          <TranslationLanguageSelector v-model="language" @update:model-value="handleChange" />
        </div>
      </div>

      <div class="wysiwyg-content norma-legislation">
        <div v-html="content" />
      </div>
    </div>
  </div>
</template>
