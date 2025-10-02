<div>
  <div class="flex justify-between mb-3">
    <div>
      @if ($folderId)
        <a href="{{ route('my.settings.drives.setup', $parentId ? ['type' => $routeFolderType, 'folder' => $parentId] : []) }}"
           class="text-primary hover:text-primary-darker">
          <x-ui.icon name="chevron-left" size="6" />
        </a>

      @endif

    </div>

    <div>
      <x-ui.modal>
        <x-slot name="trigger">
          <x-ui.button @click="open = true" theme="primary" class="tippy"
                       data-tippy-content="{{ __('storage.setup.create_new_folder') }}">
            <x-ui.icon name="plus" size="4" />
          </x-ui.button>
        </x-slot>

        <div class="">
          <x-ui.form :action="route('my.settings.folder.store.for.organisation', ['parentFolder' => $folderId])"
                     method="post">
            @include('partials.storage.my.folder.settings.form')
            <x-slot name="footer">
              <div></div>
              <x-ui.button theme="primary" type="submit">{{ __('actions.save') }}</x-ui.button>
            </x-slot>
          </x-ui.form>
        </div>
      </x-ui.modal>
    </div>

  </div>
  <ul role="list" class="divide-y divide-norma-gray-200">
    @forelse ($folders as $folder)
      <li class="py-1">
        <div class="flex items-center space-x-4">
          <div class="flex-shrink-0">
            <x-ui.icon name="folder" />
          </div>
          <div class="flex-1 min-w-0">
            <p class="text-sm font-medium truncate">
              <a href="{{ route('my.settings.drives.setup', ['type' => $routeFolderType, 'folder' => $folder->id]) }}"
                 class="text-primary hover:text-primary-darker">{{ $folder->title }}</a>
            </p>
          </div>
          <div class="flex flex-row justify-end space-x-2">
            <div>
              <x-ui.modal>
                <x-slot name="trigger">
                  <x-ui.button @click="open = true" theme="gray" styling="outline" size="sm">
                    <x-ui.icon name="pencil" size="3" />
                  </x-ui.button>
                </x-slot>

                <div class="">
                  <x-ui.form :action="route('my.settings.folder.update.for.organisation', ['folder'=> $folder, 'parentFolder' => $folderId])"
                             method="put">
                    @include('partials.storage.my.folder.settings.form', ['folder' => $folder])
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
                <x-ui.confirm-modal
                                    :route="route('my.settings.folder.destroy.for.organisation', ['folder' => $folder])">
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
            </div>

          </div>
        </div>
      </li>
    @empty
      <x-ui.empty-state-icon icon="folder-open"
                             :title="__('storage.setup.no_folders_created')"
                             :subline="__('storage.setup.no_folders_created_info')" />

    @endforelse

  </ul>
</div>
