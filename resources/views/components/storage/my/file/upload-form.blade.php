<div x-data="{ showUploader: {{ $folderId ? 'true' : 'false' }}, canUpload: false }">

  <x-ui.form method="POST" :action="$route" enctype="multipart/form-data">
    <div>
      <div class="italic text-info px-5 py-2 my-5 border border-dashed border-info">
        {{ __('storage.drives.upload_help_text') }}
      </div>
    </div>

    <div x-show="!canUpload">
      @if ($folderId)
        <input type="hidden" name="folder_id" value="{{ $folderId }}" />
      @else
        <div class="mb-5">
          <label class="font-medium mb-2">
            {{ __('storage.drives.select_a_folder_from_drive') }}
          </label>
          <x-storage.my.folder-selector @selected="showUploader = true;" name="folder_id" />
        </div>
      @endif

      @if ($relation)
        <input type="hidden" name="relation" value="{{ $relation }}" />
        <input type="hidden" name="related_id" value="{{ $relatedId }}" />
      @endif

      @if($libryoId)
        <input type="hidden" name="target_libryo_id" value="{{ $libryoId }}"/>
      @endif

      {{ $slot ?? '' }}

        <div class="mb-5">
          @if(!$noTagging)
          <x-ontology.user-tag-selector />
          @endif
        </div>
    </div>

    @if($filepond ?? false)

      <div x-show="canUpload" class="mb-4">
        <x-ui.input
            class="filepond"
            type="file"
            :name="$name"
            multiple
            accept="{{ implode(',', $acceptedUploads) }}"
            data-url="{{ $route }}"
        />
      </div>

      <div class="flex items-center justify-between mt-4">
        <div class="flex items-center justify-between flex-grow">
          <x-ui.button x-show="canUpload" type="button" @click.prevent="canUpload = false">{{ __('actions.back') }}</x-ui.button>
          <x-ui.button x-show="canUpload" type="button" @click.prevent="window.location.reload()">{{ __('actions.close') }}</x-ui.button>
        </div>
        <div>
          <x-ui.button x-show="!canUpload" type="button" @click.prevent="canUpload = true">{{ __('actions.next') }}</x-ui.button>
        </div>
      </div>

    @else
      <label x-show="showUploader"
             class="grid place-content-center py-96 w-full bg-libryo-gray-200 rounded-2xl cursor-pointer text-xl">
        {{ __('storage.select_files') }}
        <x-ui.input class="hidden" type="file" :name="$name" multiple
                    accept="{{ implode(',', $acceptedUploads) }}"
                    @change="$event.target.closest('form').requestSubmit(); open = false;" />
      </label>
    @endif


  </x-ui.form>

</div>
