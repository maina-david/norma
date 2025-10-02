<div class="w-screen max-w-7xl">
  <div class="font-semibold">{{ __('corpus.work_expression.file_browser') }}</div>

  <x-storage.collaborate.uploader :route="route('collaborate.document-editor.file-browser.store', ['expression' => $expression->id])" />

  <div class="grid grid-cols-5 gap-4 mt-4">
    @foreach($images as $image)
      <div class="h-48 rounded-lg border-norma-gray-100 bg-norma-gray-100 relative group">
        <img src="{{ $image }}" alt="{{ Str::afterLast($image, '/') }}" class="h-full w-full object-contain">

        <div class="hidden group-hover:flex items-center justify-center h-full w-full absolute top-0 left-0 space-x-2 bg-gray-500 bg-opacity-25 rounded-lg">
          <x-ui.button type="link" href="{{ $image }}" target="_blank">
            <x-ui.icon name="magnifying-glass" />
          </x-ui.button>

          <x-ui.button @click="navigator.clipboard.writeText('{{ $image }}');window.toast.success({ message: '{{ __('actions.copied') }}' })">
            <x-ui.icon name="copy" />
          </x-ui.button>

          <x-ui.delete-button styling="default" :route="route('collaborate.document-editor.file-browser.destroy', ['expression' => $expression->id])">
            <x-slot name="formBody">
              <input type="hidden" name="file" value="{{ $image }}">
            </x-slot>
            <x-ui.icon name="trash-alt" />
          </x-ui.delete-button>
        </div>
      </div>
    @endforeach
  </div>
</div>
