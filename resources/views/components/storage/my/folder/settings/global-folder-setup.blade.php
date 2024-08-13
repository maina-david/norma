<div class="p-4 bg-white rounded shadow mt-10 mx-2">
  <div class="flex flex-row items-center mb-3">
    @if ($folderId)
      <a
          href="{{ route('my.settings.drives.setup', $parentId ? ['type' => 'global', 'folder' => $parentId] : []) }}"
          class="text-primary hover:text-primary-darker"
      >
        <x-ui.icon name="chevron-left" size="6" />
      </a>
    @endif
    <div class="px-3 mr-5">
      <x-ui.icon name="hdd" size="10" class="text-secondary" />
    </div>
    <div>
      <div class="font-bold text-lg">{{ __('storage.drives.shared_drive_setup') }}</div>
      <div class="text-libryo-gray-600 text-sm">{{ __('storage.drives.shared_drive_setup_info') }}</div>
    </div>
  </div>

  @if(!$fileQuery)
  <ul role="list" class="divide-y divide-libryo-gray-200">
    @if ($folders->isEmpty())
      <x-ui.empty-state-icon
          icon="folder-open"
          :title="__('storage.setup.no_folders_created')"
          :subline="__('storage.setup.no_folders_created_info')"
      />
    @endif

    @foreach ($folders as $folder)
      <li class="py-1">
        @include('components.storage.my.file.settings.global.folder-item')
      </li>
    @endforeach
  </ul>
  @endif

</div>


@if($fileQuery)
  <div class="-mt-1 mx-2">
    <x-storage.my.file.settings.file-data-table
      :base-query="$fileQuery"
      :striped="false"
      filterable
      :filters="['domains', 'jurisdictions']"
      searchable
    >
      <x-slot name="actionButton">
        <div class="flex space-x-2">
          @if ($folderId)
            <x-ui.modal teleport>
              <x-slot name="trigger">
                <x-ui.button @click="open = true" class="tippy" data-tippy-content="{{ __('storage.setup.upload_file') }}">
                  <x-ui.icon name="upload" size="4" />
                </x-ui.button>
              </x-slot>

              <div class="max-w-[70vw]">
                <x-storage.my.file.upload-form :folder-id="$folderId" :route="route('my.settings.files.global.post', $folderId)" no-tagging>
                  <x-geonames.location.location-selector name="locations[]" multiple />

                  <x-ontology.legal-domain-selector annotations name="legal_domains[]" :label="__('ontology.legal_domain.add_legal_domains')" multiple />
                </x-storage.my.file.upload-form>
              </div>
            </x-ui.modal>
          @endif

          {{-- disabled for now as people always create wrong folders - can uncomment once we've set up permissions for this --}}
          {{-- <x-ui.modal>
            <x-slot name="trigger">
              <x-ui.button @click="open = true" theme="primary" class="tippy" data-tippy-content="{{ __('storage.setup.create_new_folder') }}">
                <x-ui.icon name="plus" size="4" />
              </x-ui.button>
            </x-slot>

            <div class="">
              <x-ui.form :action="route('my.settings.folder.global.store', ['parentFolder' => $folderId])" method="post">
                @include('partials.storage.my.folder.settings.form')

                <x-slot name="footer">
                  <div></div>
                  <x-ui.button theme="primary" type="submit">{{ __('actions.save') }}</x-ui.button>
                </x-slot>
              </x-ui.form>
            </div>
          </x-ui.modal> --}}
        </div>
      </x-slot>

      <x-slot name="prepend">
        @foreach ($folders as $folder)
          <x-ui.tr :loop="$loop">
            <td class="px-4 py-2 whitespace-normal text-sm font-medium text-libryo-gray-900 border-b border-libryo-gray-200">
              @include('components.storage.my.file.settings.global.folder-item')
            </td>
          </x-ui.tr>
        @endforeach
      </x-slot>

    </x-storage.my.file.settings.file-data-table>
  </div>
@endif
