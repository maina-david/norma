@props(['name', 'label' => __('actions.upload'), 'required' => false, 'mimes' => (new \App\Services\Storage\MimeTypeManager())->getAcceptedUploadMimes()])
<div x-data="{file: {}}">
  <label for="{{ $name }}" class="mt-4 cursor-pointer flex justify-center px-6 py-10 border-2 border-norma-gray-300 border-dashed rounded-md">
    <div class="space-y-4 text-center">
      <div>
        <x-ui.icon name="upload" size="lg" />
      </div>

      <div class="flex text-sm text-norma-gray-600">
        <span class="relative cursor-pointer rounded-md font-medium text-primary hover:text-primary-darker focus-within:outline-none">
          <span>{{ $label }}</span>
          <input @if($required) required @endif x-ref="relatedFile" @change="file = $event.target.files.item(0)" id="{{ $name }}" name="{{ $name }}" type="file" class="sr-only" accept="{{ implode(',', $mimes) }}">
        </span>
      </div>
      <p class="text-xs text-norma-gray-500">{{ __('storage.max', ['size' => '50MB']) }}</p>
    </div>
  </label>

  <div x-show="file && file.name" class="text-sm font-semibold mt-2 rounded-lg border border-norma-gray-200 p-2 flex items-center justify-between">
    <span x-html="file.name"></span>
    <button class="text-negative" type="button" @click="file = {};$refs.relatedFile.value = null;">
      <x-ui.icon name="trash-alt" />
    </button>
  </div>
</div>
