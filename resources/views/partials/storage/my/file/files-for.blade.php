<div>

  @if ($upload)
    <div class="">
      <x-ui.modal>
        <x-slot name="trigger">

          <div class="grid justify-items-end mb-5">
            <x-ui.button theme="primary" @click="open = true">
              <x-ui.icon name="upload" size="3" class="mr-3" />
              {{ __('storage.upload_files') }}
            </x-ui.button>
          </div>

        </x-slot>

        <x-storage.my.file.upload-form
          :route="route('my.drives.files.upload')"
          :relation="$relation"
          :related-id="$related->id"
          :norma-id="$norma->id ?? null"
        />
      </x-ui.modal>
    </div>
  @endif

  @if ($files->isEmpty())
    <x-ui.empty-state-icon icon="folder-open"
                           :title="__('storage.no_files')"
                           :subline="__('storage.when_uploaded')" />
  @else
    @include('partials.storage.my.file.files-list', [
        'files' => $files,
        'filesRelation' => $relation,
        'filesRelatedId' => $related->id,
    ])
  @endif

</div>
