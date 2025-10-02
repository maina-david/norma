<x-layouts.app>
  <x-slot name="header">
    <div class="flex items-center">
      <x-ui.icon name="folder-open" class="mr-5 text-norma-gray-400" size="8" />
      <div>
        {{ __('my.nav.drives') }}
        <span class="text-xs text-norma-gray-500 italic ml-3">{{ $subTitle }}</span>
      </div>
    </div>
  </x-slot>
  <x-slot name="actions">
    <div class="flex flex-row justify-between items-center">
      <div class="w-60 ">
        <x-ui.progress :color="$storageColor" :percentage="$percentage" />
        <div class="text-xs text-norma-gray-600 mt-2">
          {!! __('storage.drives.storage_used_description', [
    'percentage' => $percentage,
    'used' => $storageAllocation['used_gb'],
    'total' => $storageAllocation['total_gb'],
    'organisation' => $organisation->title,
]) !!}
        </div>
      </div>
      <a class="ml-5" target="_blank"
         href="https://success.norma.com/en/knowledge/getting-started-with-norma/documents/delving-into-your-documents">
        <x-ui.icon name="question-circle" />
      </a>
    </div>
  </x-slot>

  <div class="grid justify-items-center">
    <a href="{{ route('my.drives.files.index') }}"
       class="w-full md:w-1/2 lg:w-1/5 flex flex-row items-center cursor-pointer border bg-white rounded-lg px-3 py-2 text-norma-gray-500">
      <div>{{ __('storage.drives.search_drives') }}...</div>
    </a>
  </div>

  <div class="rounded text-positive my-2 p-4 flex flex-row items-center">
    <x-ui.icon name="folder" class="mr-5" />
    <a href="{{ route('my.drives.folders.show', ['folder' => 1823]) }}">
      {{ __('storage.drives.covide_quick_link') }}</a>
  </div>

  @if ($singleMode)
    <x-storage.my.drive-panel class="mb-5" color="primary"
                              :route="route('my.drives.folders.show.root', ['type' => 'norma'])"
                              :subtext="__('storage.drives.norma_drive_info')">
      {{ __('storage.drives.norma_drive', ['title' => $norma->title]) }}
    </x-storage.my.drive-panel>
  @endif

  @if (isset($organisation))
    <x-storage.my.drive-panel class="mb-5" color="secondary"
                              :route="route('my.drives.folders.show.root', ['type' => 'organisation'])"
                              :subtext="__('storage.drives.organisation_drive_info')">
      {{ __('storage.drives.organisation_drive', ['title' => $organisation->title]) }}
    </x-storage.my.drive-panel>
  @endif



  <x-storage.my.drive-panel color="gray-500" :route="route('my.drives.folders.show.root', ['type' => 'shared'])"
                            :subtext="__('storage.drives.shared_drive_info')">
    {{ __('storage.drives.shared_drive') }}
  </x-storage.my.drive-panel>


</x-layouts.app>
