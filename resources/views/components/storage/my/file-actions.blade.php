@props(['file', 'allowedActions'])

@foreach ($allowedActions as $action)
  {{-- DELETE --}}
  @if ($action['action'] === 'delete')
    <x-ui.confirm-modal :route="route('my.drives.files.destroy', ['file' => $file])" method="delete"
                        :label="__('actions.delete')"
                        :confirmation="__('actions.delete_confirmation')">
      <div @click="open = true"
           class="cursor-pointer text-libryo-gray-700 hover:bg-libryo-gray-100 group flex items-center px-4 py-2 text-sm"
           role="menuitem"
           tabindex="-1">
        <x-ui.icon :name="$action['icon']" class="mr-4" />
        {{ $action['label'] }}
      </div>
    </x-ui.confirm-modal>
    {{-- EDIT --}}
  @elseif ($action['action'] === 'edit')
    <a href="{{ route('my.drives.files.edit', ['file' => $file->id]) }}"
       class="cursor-pointer text-libryo-gray-700 hover:bg-libryo-gray-100 group flex items-center px-4 py-2 text-sm"
       role="menuitem"
       tabindex="-1">
      <x-ui.icon :name="$action['icon']" class="mr-4" />
      {{ $action['label'] }}
    </a>

  @elseif ($action['action'] === 'move')
    {{-- MOVE --}}
    <x-ui.modal>
      <x-slot name="trigger">
        <a @click="open = true"
           class="cursor-pointer text-libryo-gray-700 hover:bg-libryo-gray-100 group flex items-center px-4 py-2 text-sm"
           role="menuitem"
           tabindex="-1">
          <x-ui.icon :name="$action['icon']" class="mr-4" />
          {{ $action['label'] }}
        </a>
      </x-slot>

      <x-ui.alert-box class="my-7">
        {{ __('storage.drives.move_info') }}
      </x-ui.alert-box>

      <x-ui.form class="" method="PUT" :action="route('my.drives.files.move', ['file' => $file->id])">
        <label class="">
          <x-storage.my.folder-selector name="destination" />
        </label>

        <div class="grid place-items-end">
          <x-ui.button class="mt-5 " theme="primary" type="submit">{{ __('actions.move') }}</x-ui.button>
        </div>

      </x-ui.form>
    </x-ui.modal>

  @else
    {{-- REPLACE --}}
    <x-ui.modal>
      <x-slot name="trigger">
        <a @click="open = true"
           class="cursor-pointer text-libryo-gray-700 hover:bg-libryo-gray-100 group flex items-center px-4 py-2 text-sm"
           role="menuitem"
           tabindex="-1">
          <x-ui.icon :name="$action['icon']" class="mr-4" />
          {{ $action['label'] }}
        </a>
      </x-slot>

      <x-ui.alert-box class="my-7">
        {{ __('storage.drives.replace_info') }}
      </x-ui.alert-box>

      <x-ui.form class="text-center" method="PUT" :action="route('my.drives.files.replace', ['file' => $file->id])"
                 enctype="multipart/form-data">
        <label class="p-96 w-full inline-block bg-libryo-gray-200 rounded-2xl cursor-pointer text-xl">
          {{ __('storage.select_files') }}
          <x-ui.input class="hidden" type="file" name="file"
                      accept="{{ implode(',', $acceptedUploads) }}"
                      @change="$event.target.closest('form').requestSubmit(); open = false;" />
        </label>
      </x-ui.form>
    </x-ui.modal>

  @endif

@endforeach
