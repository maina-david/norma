@props(['file', 'color', 'route', 'allowDelete', 'showDetails' => true, 'filesRelation' => null, 'filesRelatedId' => null, 'deleteRoute' => route('my.drives.files.destroy', ['file' => $file->id,'relation' => $filesRelation ?? null, 'related_id' => $filesRelatedId ?? null]), 'bulk' => false])

<div x-data="{
  showingButtons: false,
  timeout: null,
  debounce(func, wait) {
      let later = function() {
        this.timeout = null;
        func.apply(this, []);
      }
      clearTimeout(this.timeout);
      this.timeout = setTimeout(later, wait);
  },
 }" x-on:mouseenter="debounce(() => showingButtons = true, 150)"
     @mouseover.away="debounce(() => showingButtons = false, 0)"
     {{ $attributes->merge(['class' => 'flex flex-row justify-between bg-white hover:bg-libryo-gray-50 border p-2 items-center -mt-px']) }}
>
  <div class="flex items-center w-8">
    @if($allowDelete && $bulk)
    <x-ui.input type="checkbox" class="file-action-checkbox" name="actions-checkbox-{{ $file->id }}" @click="$event.target.checked ? selectedFiles['actions-checkbox-{{ $file->id }}'] = true : delete selectedFiles['actions-checkbox-{{ $file->id }}']" />
    @endif
  </div>

  <div class="flex items-center pr-5">
    <x-ui.file-icon :mime-type="$file->mime_type" class="text-{{ $color ?? 'gray' }}" />
  </div>
  <a href="{{ $route }}" data-turbo-frame="_top" class="block grow">
    <div class="text-primary max-w-screen-md">{{ $file->title }}</div>
    <div class="text-libryo-gray-500 text-xs">{{ $file->description ?? '-' }}</div>
    <div>{{ $slot ?? '' }}</div>
  </a>

  {{-- ACTION BUTTONS --}}
  <div x-cloak>
    <div x-show="showingButtons" class="flex flex-row">

      @if ($allowDelete)
        <div class="mr-1">
          <x-ui.confirm-button :route="$deleteRoute"
                               :button-theme="$buttonTheme ?? 'negative'"
                               size="xs"
                               styling="flat"
                               :label="__('actions.delete')" method="DELETE"
                               :confirmation="__('actions.delete_confirmation')"
                               danger>
            <x-ui.icon name="trash" class="mr-3" size="3" />
            {{ __('actions.delete') }}
          </x-ui.confirm-button>
        </div>
      @endif

      <a target="_blank" href="{{ route('my.drives.files.download', ['file' => $file->id]) }}">
        <x-ui.button size="xs" styling="flat" theme="primary">
          <x-ui.icon name="download" class="mr-3" size="3" />
          {{ __('actions.download') }}
        </x-ui.button>
      </a>

    </div>
  </div>
  {{-- END ACTION BUTTONS --}}

  @if ($showDetails)

    <div class="text-sm px-4 w-20 text-libryo-gray-400">
      {{ $file->extension }}
    </div>
    <div class="text-sm px-4 w-20 text-libryo-gray-400">
      {{ $file->getHumanReadableSize() }}
    </div>
    <div class="text-sm pl-4 w-32 text-libryo-gray-400">
      <x-ui.timestamp :timestamp="$file->updated_at" />
    </div>

  @endif
</div>
