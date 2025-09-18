<script setup>
import { onMounted, provide, ref } from 'vue';
import DocToc from '@/vue/components/aug-model/DocToc.vue';
import { useAxios } from '@/vue/composables/useAxios';

const axios = useAxios();
const props = defineProps({
  expression: { type: Object, required: true },
  doc: { type: Object, required: true },
});

const docTitle = `<div class="text-3xl text-center mt-40 px-10">${props.doc.title}</div>`;
const docTitleTranslation = props.doc.title_translation
  ? `<div class="text-lg text-center mt-10 mb-40 px-10 text-norma-gray-400">${props.doc.title_translation}</div>`
  : '';

const loading = ref(false);
const currentContentURL = ref('');
const currentContent = ref(`${docTitle}${docTitleTranslation}`);

onMounted(() => {
  if (!props.doc.resource_link) {
    window.Split([`#split-left-${props.doc.id}`, `#split-right-${props.doc.id}`], { sizes: [30, 70] });
  }
});

function changeContent(target) {
  if (target === currentContentURL.value) {
    return;
  }

  loading.value = true;
  axios.get(target, { baseURL: '/' })
    .then(({ data }) => {
      const parser = (new DOMParser).parseFromString(data, 'text/html');
      const el = parser.querySelector('template');

      if (el) {
        currentContent.value = el.innerHTML;
      }
    })
    .finally(() => {
      loading.value= false;
    });

  currentContentURL.value = target;
}

provide('changeContent', changeContent);
</script>

<template>
  <div v-if="doc.resource_link" class="norma-legislation shadow bg-white p-3 max-h-screen overflow-y-auto">
    <div class="text-3xl text-center mt-40 px-10">
      {{ doc.title }}
    </div>
    <div v-if="doc.title_translation" class="text-lg text-center mt-10 mb-40 px-10 text-norma-gray-400">
      {{ doc.title_translation }}
    </div>

    <div class="pb-40">
      <turbo-frame :id="`content-full-text-${doc.id}`" :src="doc.resource_link" />
    </div>
  </div>

  <div v-else v-loading="loading" class="flex flex-row splitter w-full">
    <div :id="`split-left-${doc.id}`">
      <DocToc :expression="expression" />
    </div>

    <div :id="`split-right-${doc.id}`">
      <div class="norma-legislation shadow bg-white ml-3 p-7 max-h-screen overflow-y-auto">
        <div v-html="currentContent" />
      </div>
    </div>
  </div>
</template>
