<x-layouts.app>
  <x-slot name="header">
    <div class="flex items-center">
      <x-ui.icon name="folder-open" class="mr-5 text-norma-gray-400" size="8" />
      <div>
        {{ __('storage.drives.drives') }}
        <span class="text-xs text-norma-gray-500 italic">{{ $subTitle }}</span>
      </div>
    </div>
  </x-slot>

  <x-slot name="actions">
    <div class="flex flex-row justify-between items-center">
      <a target="_blank"
         href="https://success.norma.com/en/knowledge/getting-started-with-norma/documents/delving-into-your-documents">
        <x-ui.icon name="question-circle" />
      </a>
    </div>
  </x-slot>


  @include('partials.storage.my.file.files-search', [
      'tableFields' => $tableFields,
  ])

</x-layouts.app>
