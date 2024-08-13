<script setup>
import { computed, inject, ref, watch } from 'vue';
import { debounce } from 'lodash';
import { useAxios } from '@/vue/composables/useAxios';
import WysiwygEditor from '@/vue/components/WysiwygEditor.vue';
import InputElement from '@/vue/components/InputElement.vue';
import AppButton from '@/vue/components/AppButton.vue';
import LibryoIcon from '@/collaborate/components/LibryoIcon.vue';

const can = inject('can');
const expression = inject('expression');
const axios = useAxios();
const props = defineProps({
  reference: { type: Object, required: true },
});

const loading = ref(true);
const previousOpen = ref(false);
const versions = ref([]);
const contentDraft = computed(() => props.reference.content_draft);
const canEdit = computed(() => props.reference.content_draft && can('collaborate.corpus.reference.update'));
const content = ref(props.reference.content_draft?.html_content ?? '');
const title = ref(props.reference.content_draft?.title ?? '');

function updateFromResponse(data) {
  content.value = data.live ?? '';
  versions.value = data.versions;
  if (props.reference.content_draft) {
    props.reference.content_draft.title = data.draft.title ?? null;
  }

  if (canEdit.value) {
    title.value = data.draft.title ?? '';
    content.value = data.draft.html_content ?? '';
  }

  setTimeout(() => {
    loading.value = false;
  }, 100);

  return data;
}

const getContent = debounce(() => {
  loading.value = true;
  axios.get(`/references/${props.reference.id}/content`)
    .then(({ data }) => updateFromResponse(data));
}, 500);

function handleSaveContent() {
  const payload = {
    title: title.value,
    content: content.value,
  };

  axios.put(`/references/${props.reference.id}/content`, payload)
    .then(({ data }) => updateFromResponse(data))
    .then(() => window.toast.success({ message: 'Saved successfully' }));
}

getContent();
watch(contentDraft, () => getContent());
</script>

<template>
  <div v-if="!loading" class="p-4 libryo-legislation">
    <div v-if="canEdit" class="notranslate">
      <form action="#" @submit.prevent="handleSaveContent">
        <div class="text-sm font-medium text-libryo-gray-700 block mt-4 mb-1">
          Title
        </div>

        <InputElement v-model="title" />

        <div class="text-sm font-medium text-libryo-gray-700 block mt-4 mb-1">
          Text
        </div>

        <WysiwygEditor v-model="content" />

        <div class="flex justify-end mt-8">
          <AppButton type="submit">
            Save
          </AppButton>
        </div>
      </form>
    </div>

    <div v-else-if="content.length > 0" class="relative" v-html="content" />

    <div class="mt-8">
      <div class="flex items-center border-b border-gray-200">
        <div class="font-semibold flex-grow cursor-pointer hover:text-primary" @click="previousOpen = !previousOpen">
          Previous Versions
        </div>

        <LibryoIcon :name="previousOpen ? 'angle-down' : 'angle-right'" class="flex-shrink-0" />
      </div>

      <div v-if="previousOpen" class="mt-2">
        <div v-for="version in versions" :key="version.id" class="border border-gray-200 rounded-lg px-4 pb-4">
          <div class="text-right mb-4 text-sm font-semibold mt-2">
            {{ $format.datetime(version.created_at) }}
          </div>
          <div class="text-sm font-medium text-libryo-gray-700 block mt-4 mb-1">
            Title
          </div>
          <div class="font-semibold">
            {{ version.title }}
          </div>
          <div class="text-sm font-medium text-libryo-gray-700 block mt-4 mb-1">
            Text
          </div>
          <div v-html="version.html_content" />
        </div>
      </div>
    </div>
  </div>
</template>
