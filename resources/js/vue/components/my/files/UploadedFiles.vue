<script setup>
import { ref } from 'vue';
import AppButton from '@/vue/components/AppButton.vue';
import AppIcon from '@/vue/components/AppIcon.vue';
import AppModal from '@/vue/components/AppModal.vue';
import FileUploader from '@/vue/components/my/files/FileUploader.vue';
import { useAxios } from '@/vue/composables/useAxios';
import FileIcon from '@/vue/components/my/files/FileIcon.vue';
import EmptyState from '@/vue/components/EmptyState.vue';

const props = defineProps({
  canUpload: { type: Boolean, default: false },
  requiresFolder: { type: Boolean, default: false },
  folderId: { type: [String, Number], default: null },
  normaId: { type: [String, Number], default: null },
  multiple: { type: Boolean, default: false },
  relatedId: { type: [String, Number], default: null },
  relation: { type: String, default: null },
});

const files = ref([]);
const loading = ref(false);
const axios = useAxios();

function fetchFiles() {
  loading.value = true;

  axios.get(`/storage/files/related/${props.relation}/${props.relatedId}`)
    .then(({ data }) => data)
    .then(({ data }) => {
      files.value = [...data];
    })
    .finally(() => {
      loading.value = false;
    });
}

function handleClose(toggle) {
  toggle();
  fetchFiles();
}

fetchFiles();
</script>

<template>
  <div v-loading="loading" class="min-h-20">
    <div class="flex justify-end w-full">
      <AppModal v-if="canUpload">
        <template #trigger="{ toggle }">
          <AppButton theme="primary" @click="toggle">
            <span class="flex items-center">
              <AppIcon name="upload" size="3" />
              <span class="ml-2">{{ $t('storage.upload_files') }}</span>
            </span>
          </AppButton>
        </template>

        <template #default="{ toggle, visible }">
          <div v-if="visible" class="max-w-screen-75 lg:max-w-screen-50 bg-white rounded-lg px-8 py-4">
            <FileUploader
              :requires-folder="requiresFolder"
              :folder-id="folderId"
              :norma-id="normaId"
              :multiple="multiple"
              name="file"
              :related-id="relatedId"
              :relation="relation"
            />

            <div class="mt-4 flex justify-end">
              <AppButton @click="() => handleClose(toggle)">
                {{ $t('actions.close') }}
              </AppButton>
            </div>
          </div>
        </template>
      </AppModal>
    </div>

    <EmptyState v-if="!loading && files.length < 1" :title="$t('storage.no_files')" icon="folder-open" />

    <div v-else class="mt-6 divide-y divide-gray-200">
      <div v-for="file in files" :key="file.id" class="flex relative items-center group p-2">
        <div class="flex-shrink-0 mr-4">
          <FileIcon :mime-type="file.mime_type" />
        </div>
        <div class="flex-grow mr-4">
          <a :href="`/drives/files/${file.id}`" class="block">
            <div class="text-primary max-w-screen-md">{{ file.title }}</div>
            <div class="text-norma-gray-500 text-xs">{{ file.description ?? '-' }}</div>
          </a>
        </div>

        <div class="hidden group-hover:block mr-4 flex-shrink-0">
          <a :href="`/drives/files/download/${file.id}`" target="_blank" class="inset-0 flex items-center font-semibold text-primary">
            <AppIcon name="download" class="mr-1" size="4" />
            <span>{{ $t('actions.download') }}</span>
          </a>
        </div>
        <div>{{ file.extension }}</div>
        <div class="ml-4">
          {{ file.size }}
        </div>
        <div class="ml-4">
          {{ $format.date(file.created_at) }}
        </div>
      </div>
    </div>
  </div>
</template>
