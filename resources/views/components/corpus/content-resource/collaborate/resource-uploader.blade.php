<div {{ $attributes }}>

  <x-ui.form :action="route('collaborate.corpus.content-resources.upload')" method="POST" enctype="multipart/form-data">
    <label for="file"
           class="px-44 cursor-pointer flex justify-center px-6 py-10 border-2 border-norma-gray-300 border-dashed rounded-md">
      <div class="space-y-4 text-center">
        <div>
          <x-ui.icon name="upload" size="lg" />
        </div>

        <div class="flex text-sm text-norma-gray-600">
          <span
                class="cursor-pointer relative cursor-pointer rounded-md font-medium text-primary hover:text-primary-darker focus-within:outline-none">
            <span>{{ __('storage.upload_files') }}</span>
            <input
                   @change="$event.target.closest('form').requestSubmit()"
                   id="file"
                   name="file"
                   type="file"
                   class="sr-only"
                   accept="image/jpeg,image/png,application/pdf,text/plain,text/html">
          </span>
        </div>
        <p class="text-xs text-norma-gray-500">{{ __('storage.max', ['size' => '50MB']) }}</p>
      </div>
    </label>
  </x-ui.form>

  <turbo-frame id="content_resource_uploader_url"></turbo-frame>
</div>
