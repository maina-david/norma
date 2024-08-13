<x-layouts.app x-data="{selectedFiles: {}}">
  <x-slot name="header">
    <div class="flex items-center">
      <a href="{{ $backRoute }}" class="text-primary flex justify-center p-2 rounded-full border border-primary">
        <x-ui.icon name="chevron-left" size="2" />
      </a>
      <x-ui.icon name="folder-open" class="md:ml-4 mr-4 text-tertiary" size="8" />
      <span>{{ $title }}</span>
    </div>
  </x-slot>

  <x-slot name="actions">
    <div class="flex items-center space-x-4">
      <template x-if="Object.keys(selectedFiles).length > 0">
        <x-ui.delete-button route="{{ route('my.drives.files.bulk.destroy') }}">
          <x-slot:formBody>
            <template x-for="item in Object.keys(selectedFiles)" :key="item">
              <input type="hidden" :name="item" value="true">
            </template>
          </x-slot:formBody>


          <span class="py-0.5">
            {{ __('actions.delete') }}
          </span>
        </x-ui.delete-button>
      </template>

      @if ($canUpload)
        <x-ui.modal>
          <x-slot name="trigger">
            <x-ui.button @click="open = true" theme="primary" styling="outline">
              <x-ui.icon name="upload" class="mr-2" />
              {{ __('storage.upload_files') }}
            </x-ui.button>
          </x-slot>

          <div class="w-screen-50">
            <div class="text-lg mb-5">{{ __('storage.upload_files') }}</div>
            <div class="">
              <x-storage.my.file.upload-form :route="$uploadRoute" :folder-id="$currentFolder->id" filepond />
            </div>
          </div>
        </x-ui.modal>
      @endif
    </div>
  </x-slot>

  @if($files->isNotEmpty())
  <div class="bg-white px-3 py-4 flex items-center">
    <div class="flex items-center w-8">
      <x-ui.input type="checkbox" name="check-all" @click="document.querySelectorAll('#files-in-folder .file-action-checkbox').forEach(i => i.click())" />
    </div>
  </div>
  @endif

  {{-- FOLDERS --}}
  @foreach ($folders as $folder)
    <x-storage.folder-panel :color="$iconColor"
                            :route="route('my.drives.folders.show', ['folder' => $folder->id])">
      <div>{{ $folder->title }}</div>
      @if ($folder->originalTitle)
        <div class="text-libryo-gray-400 italic text-xs">{{ __('storage.drives.original_name') }}:
          {{ $folder->originalTitle ?? '' }}
        </div>
      @endif
    </x-storage.folder-panel>
  @endforeach
  {{-- END FOLDERS --}}


  {{-- EMPTY FOLDERS --}}
  @if (count($emptyFolders) > 0)

    <div x-data="{ showing: true }">
      <div @click="showing = !showing"
           class="flex flex-row justify-between cursor-pointer py-5 px-3 mt-3 hover:bg-libryo-gray-100">
        <div>{{ __('storage.drives.empty_folders') }}</div>
        <div>
          <x-ui.icon x-show="showing" name="chevron-up" />
          <x-ui.icon x-show="!showing" name="chevron-down" />
        </div>
      </div>

      <div x-show="showing">
        @foreach ($emptyFolders as $folder)
          <x-storage.folder-panel :color="$iconColor"
                                  empty
                                  :route="route('my.drives.folders.show', ['folder' => $folder->id])">
            <div>{{ $folder->title }}</div>
            @if ($folder->originalTitle)
              <div class="text-libryo-gray-400 italic text-xs">{{ __('storage.drives.original_name') }}:
                {{ $folder->originalTitle ?? '' }}</div>
            @endif
          </x-storage.folder-panel>
        @endforeach
      </div>
    </div>

  @endif
  {{-- END EMPTY FOLDERS --}}


  {{-- FILES --}}
  <turbo-frame id="files-in-folder">
    @include('partials.storage.my.file.files-list', [
        'files' => $files,
        'currentFolder' => $currentFolder,
        'bulk' => true
    ])
  </turbo-frame>
  {{-- END FILES --}}

</x-layouts.app>
