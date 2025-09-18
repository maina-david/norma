<script setup>
import vueFilePond from 'vue-filepond';
import FilePondPluginFileValidateType from 'filepond-plugin-file-validate-type';
import { ref } from 'vue';
import acceptedUploads from '@/vue/components/my/files/accepted-uploads';
import { useAxios } from '@/vue/composables/useAxios';
import FolderSelector from '@/vue/components/my/files/FolderSelector.vue';

const props = defineProps({
  folderId: { type: [String, Number], default: null },
  normaId: { type: [String, Number], default: null },
  multiple: { type: Boolean, default: false },
  name: { type: String, default: 'file' },
  relatedId: { type: [String, Number], default: null },
  relation: { type: String, default: null },
  requiresFolder: { type: Boolean, default: false },
  target: { type: String, default: null },
  withTags: { type: Boolean, default: false },
});

const axios = useAxios();
const selectedFolder = ref(props.folderId);

const FilePond = vueFilePond(FilePondPluginFileValidateType);
const server = {
  process: (fieldName, file, metadata, load, error, progress, abort, transfer, options) => {
    const formData = new FormData();
    formData.append(fieldName, file, file.name);

    formData.append('relation', props.relation);
    formData.append('related_id', props.relatedId);
    formData.append('folder_id', selectedFolder.value);
    formData.append('target_norma_id', props.normaId);

    const controller = new AbortController();

    const config = {
      signal: controller.signal,
      onUploadProgress: (e) => {
        progress(e.estimated, e.loaded, e.total);
      },
    };

    axios.postForm(props.target ?? '/storage/files', formData, config)
      .then(({ data }) => data)
      .then(({ data }) => {
        load(data.id);
      })
      .catch((e) => {
        error(e);
      });

    return {
      abort: () => {
        controller.abort();
        abort();
      },
    };
  },
};
</script>

<template>
  <div class="pt-4">
    <div class="italic text-info px-5 py-2 my-5 border border-dashed border-info rounded">
      {{ $t('storage.drives.upload_help_text') }}
    </div>

    <div v-if="requiresFolder">
      <div v-if="!folderId" class="mb-5">
        <label class="font-medium mb-2">
          {{ $t('storage.drives.select_a_folder_from_drive') }}
        </label>

        <FolderSelector v-model="selectedFolder" />
      </div>

      <slot />

      <!--      <div v-if="withTags" class="mb-5">-->
      <!--        <x-ontology.user-tag-selector />-->
      <!--      </div>-->
    </div>

    <div class="mt-8">
      <FilePond
        v-if="!requiresFolder || selectedFolder"
        label-idle="Drop files here..."
        :name="name"
        :allow-multiple="multiple"
        :allow-replace="false"
        :allow-revert="false"
        :allow-remove="false"
        :accepted-file-types="acceptedUploads.join(',')"
        :server="server"
      />
    </div>
  </div>
</template>

<!--<div x-data="{ showUploader: {{ $folderId ? 'true' : 'false' }}, canUpload: false }">-->

<!--<x-ui.form method="POST" :action="$route" enctype="multipart/form-data">-->

<!--  @if($filepond ?? false)-->

<!--  <div x-show="canUpload" class="mb-4">-->
<!--    <x-ui.input-->
<!--      class="filepond"-->
<!--      type="file"-->
<!--      :name="name"-->
<!--      multiple-->
<!--      :accept="acceptedUploads.join(',')"-->
<!--      data-url="{{ $route }}"-->
<!--    />-->
<!--  </div>-->

<!--  <div class="flex items-center justify-between mt-4">-->
<!--    <div class="flex items-center justify-between flex-grow">-->
<!--      <x-ui.button x-show="canUpload" type="button" @click.prevent="canUpload = false">{{ $t('actions.back') }}</x-ui.button>-->
<!--      <x-ui.button x-show="canUpload" type="button" @click.prevent="window.location.reload()">{{ $t('actions.close') }}</x-ui.button>-->
<!--    </div>-->
<!--    <div>-->
<!--      <x-ui.button x-show="!canUpload" type="button" @click.prevent="canUpload = true">{{ $t('actions.next') }}</x-ui.button>-->
<!--    </div>-->
<!--  </div>-->

<!--</x-ui.form>-->

<!--</div>-->
