@props(['route', 'method' => 'POST', 'name' => 'file'])

<x-ui.form :action="$route" :method="$method" enctype="multipart/form-data" {{ $attributes }}>
  <label for="{{ $name }}" class="cursor-pointer flex justify-center px-6 py-10 border-2 border-libryo-gray-300 border-dashed rounded-md">
    <div class="space-y-4 text-center">
      <div>
        <x-ui.icon name="upload" size="lg" />
      </div>

      <div class="flex text-sm text-libryo-gray-600">
        <span class="cursor-pointer relative cursor-pointer rounded-md font-medium text-primary hover:text-primary-darker focus-within:outline-none">
          <span>{{ __('storage.upload_files') }}</span>
          <input
            @if(!isset($formBody))
            @change="$event.target.closest('form').requestSubmit()"
            @endif
            id="{{ $name }}"
            name="{{ $name }}"
            type="file"
            class="sr-only"
            accept="{{ implode(',', (new \App\Services\Storage\MimeTypeManager())->getAcceptedUploadMimes()) }}"
          >
        </span>
      </div>
      <p class="text-xs text-libryo-gray-500">{{ __('storage.max', ['size' => '50MB']) }}</p>
    </div>
  </label>

  @error($name)
  <div class="text-sm text-red-400">{{ $message }}</div>
  @enderror

  {{ $formBody ?? '' }}

  @isset($formBody)
    <div class="flex justify-end mt-4">
      <x-ui.button type="submit">
        {{ __('actions.save') }}
      </x-ui.button>
    </div>
  @endisset
</x-ui.form>
