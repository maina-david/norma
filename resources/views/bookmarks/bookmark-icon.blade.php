<div class="w-6 h-8 -mt-1 flex items-center" id="{{ $turboKey }}-bookmark">
  @if($bookmarks->isEmpty())

    <x-ui.form method="POST" action="{{ route($routePrefix . '.store', $routePayload) }}">
      <button @click.stop="" type="submit" class="tippy hidden group-hover:block" data-tippy-content="{{ __('bookmarks.bookmark') }}">
        <x-ui.icon
            type="light"
            class="text-norma-gray-400 mr-3"
            name="bookmark"
            size="{{ $size ?? 3 }}"
        />
      </button>
    </x-ui.form>

  @else

    <x-ui.form method="DELETE" action="{{ route($routePrefix . '.destroy', $routePayload) }}">
      <button @click.stop="" type="submit" class="tippy" data-tippy-content="{{ __('bookmarks.remove_bookmark') }}">
        <x-ui.icon
            type="solid"
            class="text-primary mr-3"
            name="bookmark"
            size="{{ $size ?? 3 }}"
        />
      </button>
    </x-ui.form>

  @endif
</div>

