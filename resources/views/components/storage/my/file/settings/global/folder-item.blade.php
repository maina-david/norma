<div class="flex items-center space-x-5">
  <div class="flex-shrink-0">
    <x-ui.icon name="folder" />
  </div>
  <div class="flex-1 min-w-0">
    <p class="text-sm font-medium truncate">
      <a
          href="{{ route('my.settings.drives.setup', ['type' => 'global', 'folder' => $folder->id]) }}"
          class="text-primary hover:text-primary-darker"
      >
        {{ $folder->title }}
      </a>
    </p>
  </div>
  <div class="flex flex-row justify-end space-x-2">
    {{-- disabled for now as deleting and editing folders will affect all users - can uncomment once we've set up permissions for this --}}
    {{-- <div>
      <x-ui.modal>
        <x-slot name="trigger">
          <x-ui.button @click="open = true" theme="gray" styling="outline" size="sm">
            <x-ui.icon name="pencil" size="3" />
          </x-ui.button>
        </x-slot>

        <div class="">
          <x-ui.form :action="route('my.settings.folder.global.update', ['folder'=> $folder])" method="put">
            @include('partials.storage.my.folder.settings.form', [
                'folder' => $folder,
            ])
            <x-slot name="footer">
              <div></div>
              <x-ui.button theme="primary" type="submit">{{ __('actions.save') }}</x-ui.button>
            </x-slot>
          </x-ui.form>
        </div>
      </x-ui.modal>
    </div>


    <div>
      @if ($folder->children_count === 0 && $folder->files_count === 0)
        <x-ui.confirm-modal :route="route('my.settings.folder.global.destroy', ['folder' => $folder])">
          <x-ui.button theme="negative" styling="outline" size="sm">
            <x-ui.icon name="trash-alt" size="3" />
          </x-ui.button>
        </x-ui.confirm-modal>
      @else
        <div class="tippy" data-tippy-content="{{ __('storage.setup.folder_cant_be_deleted') }}">
          <x-ui.button disabled theme="gray" styling="outline" size="sm">
            <x-ui.icon name="trash-alt" size="3" />
          </x-ui.button>
        </div>
      @endif
    </div> --}}

  </div>
</div>
