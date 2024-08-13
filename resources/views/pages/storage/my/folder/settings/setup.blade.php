@php
use App\Enums\Storage\My\FolderType;
@endphp
<x-layouts.settings>
  <x-slot name="header">
    <div class="flex items-center">
      <x-ui.icon name="hdd" size="10" class="mr-5 text-libryo-gray-600" /> {{ __('settings.nav.drives') }}
    </div>
  </x-slot>


  @if (empty($organisation))
    <turbo-frame id="settings-drives-setup-global-folders" data-turbo-action="advance">
      <x-storage.my.folder.settings.global-folder-setup :folder-id="$globalFolderId" />
    </turbo-frame>
  @else
    {{-- Organisation Drive Setup --}}
    <div class="p-5 bg-white rounded shadow mt-10">
      <div class="flex flex-row items-center mb-3">
        <div class="px-3 mr-5">
          <x-ui.icon name="hdd" size="10" class="text-secondary" />
        </div>
        <div>
          <div class="font-bold text-lg">{{ __('storage.drives.organisation_drive_setup') }}</div>
          <div class="text-libryo-gray-600 text-sm">{{ __('storage.drives.organisation_drive_setup_info') }}</div>
        </div>
      </div>

      <div class="grid md:grid-cols-2 gap-4">
        <div class="p-5">
          <x-ui.alert-box class="mb-10">
            {{ __('storage.setup.organisation_folders_setup_info') }}
          </x-ui.alert-box>

          <turbo-frame id="settings-drives-setup-organisation-folders-{{ FolderType::organisation()->value }}" data-turbo-action="advance">
            <x-storage.my.folder.settings.organisation-folder-setup :folder-id="$organisationFolderId" :folder-type="FolderType::organisation()->value" />
          </turbo-frame>
        </div>

        <div class="p-5">
          <x-ui.alert-box class="mb-10">
            {{ __('storage.drives.organisation_drive_setup_explainer') }}
          </x-ui.alert-box>
          <turbo-frame id="settings-drives-libryo-specific-folders-{{ FolderType::organisation()->value }}">
            <x-storage.my.folder.settings.libryo-folder-setup :folder-type="FolderType::organisation()->value" />
          </turbo-frame>
        </div>
      </div>

    </div>


    {{-- Libryo Stream Drive Setup --}}

    <div class="p-5 bg-white rounded shadow mt-10">
      <div class="flex flex-row items-center mb-3">
        <div class="px-3 mr-5">
          <x-ui.icon name="hdd" size="10" class="text-primary" />
        </div>
        <div>
          <div class="font-bold text-lg">{{ __('storage.drives.libryo_stream_drive_setup') }}</div>
          <div class="text-libryo-gray-600 text-sm">{{ __('storage.drives.libryo_stream_drive_setup_info') }}</div>
        </div>
      </div>

      <div class="grid md:grid-cols-2 gap-4">
        <div class="p-5">
          <x-ui.alert-box class="mb-10">
            {{ __('storage.setup.organisation_folders_setup_info_libryo_streams') }}
          </x-ui.alert-box>

          <turbo-frame id="settings-drives-setup-organisation-folders-{{ FolderType::libryo()->value }}" data-turbo-action="advance">
            <x-storage.my.folder.settings.organisation-folder-setup :folder-id="$libryoFolderId" :folder-type="FolderType::libryo()->value" />
          </turbo-frame>
        </div>

        <div class="p-5">
          <x-ui.alert-box class="mb-10">
            {{ __('storage.drives.libryo_stream_drive_setup_explainer') }}
          </x-ui.alert-box>
          <turbo-frame id="settings-drives-libryo-specific-folders-{{ FolderType::libryo()->value }}">
            <x-storage.my.folder.settings.libryo-folder-setup :folder-type="FolderType::libryo()->value" />
          </turbo-frame>
        </div>
      </div>
    </div>
  @endif

</x-layouts.settings>
