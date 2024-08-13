
<script setup>
import { ref } from 'vue';
import LibryoIcon from '@/collaborate/components/LibryoIcon.vue';
import { useAxios } from '@/vue/composables/useAxios';
import AppButton from '@/vue/components/AppButton.vue';

const axios = useAxios();
const loading = ref(false);
const open = ref(false);
const uploadedItems = ref(null);

function processUpload(event) {
  loading.value = true;
  const data = new FormData(event.target);

  axios.post('/content-resources', data, { baseURL: '/' })
    .then(({ data }) => {
      const parser = (new DOMParser).parseFromString(data, 'text/html');
      const el = parser.querySelector('template');

      if (el && uploadedItems.value) {
        uploadedItems.value.innerHTML = el.innerHTML;
      }
    })
    .finally(() => {
      loading.value = false;
    });
}
</script>

<template>
  <div>
    <AppButton class="w-full" @click="open = true">
      <span class="flex items-center">
        <LibryoIcon name="upload" />
        <span class="ml-4 whitespace-nowrap">File Upload</span>
      </span>
    </AppButton>

    <div v-show="open" class="fixed inset-0 w-screen h-screen flex items-center justify-center overflow-hidden z-20 bg-gray-400 bg-opacity-25">
      <div v-loading="loading" class="py-4 px-4 bg-white rounded-lg border border-gray-200 shadow-lg overflow-hidden flex flex-col w-full max-w-lg max-h-[75vh]">
        <div class="flex-shrink-0 mb-4 flex items-center justify-between">
          <div class="font-semibold px-2">
            Upload Files
          </div>

          <button class="p-2 hover:text-primary" @click="open = false">
            <LibryoIcon name="times" />
          </button>
        </div>

        <div class="flex-grow overflow-hidden">
          <form action="/content-resources" method="POST" enctype="multipart/form-data" @submit.prevent="processUpload">
            <label
              for="file"
              class="px-44 cursor-pointer flex justify-center px-6 py-10 border-2 border-gray-300 border-dashed rounded-md"
            >
              <div class="space-y-4 text-center">
                <div>
                  <LibryoIcon name="upload" icon-size="lg" />
                </div>

                <div class="flex text-sm text-libryo-gray-600">
                  <span
                    class="cursor-pointer relative cursor-pointer rounded-md font-medium text-primary hover:text-primary-darker focus-within:outline-none"
                  >
                    <span>Upload Files</span>
                    <input
                      id="file"
                      name="file"
                      type="file"
                      class="sr-only"
                      accept="image/jpeg,image/png,application/pdf,text/plain,text/html"
                      @change="$event.target.closest('form').requestSubmit()"
                    >
                  </span>
                </div>
                <p class="text-xs text-libryo-gray-500">50MB Max</p>
              </div>
            </label>
          </form>

          <div ref="uploadedItems" />
        </div>
      </div>
    </div>
  </div>
</template>
