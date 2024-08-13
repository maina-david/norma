<x-layouts.app>
  <x-slot name="header">
    <div class="flex items-center">
      <x-ui.icon name="folder-open" class="mr-5 text-libryo-gray-400" size="8" />
      <div>
        {{ __('storage.drives.drives') }}
        <span class="text-xs text-libryo-gray-500 italic">{{ $subTitle }}</span>
      </div>
    </div>
  </x-slot>

  <x-slot name="actions">
    <div class="flex flex-row justify-between items-center">
      <a target="_blank"
         href="https://success.libryo.com/en/knowledge/getting-started-with-libryo/documents/delving-into-your-documents">
        <x-ui.icon name="question-circle" />
      </a>
    </div>
  </x-slot>


  @include('partials.storage.my.file.files-search', [
      'tableFields' => $tableFields,
  ])

</x-layouts.app>
