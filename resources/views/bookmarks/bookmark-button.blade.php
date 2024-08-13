  @if($bookmarks->isEmpty())

    <x-ui.form id="{{ $turboKey }}-bookmark" method="POST" action="{{ route($routePrefix . '.store', $routePayload) }}">
      <input type="hidden" name="bookmark-button" />
      <x-ui.button type="submit" theme="primary" styling="outline" class="tippy" data-tippy-content="{{ __('bookmarks.bookmark') }}">
        <span class="flex items-center flex-no-wrap">
          <x-ui.icon
              type="light"
              class="mr-3"
              name="bookmark"
              size="3"
          />

          <span class="whitespace-nowrap">{{ __('bookmarks.bookmark') }}</span>
        </span>
      </x-ui.button>
    </x-ui.form>

  @else

    <div id="{{ $turboKey }}-bookmark">
      <x-ui.confirm-button
          route="{{ route($routePrefix . '.destroy', $routePayload) }}"
          method="DELETE"
          label="{{ __('bookmarks.remove_bookmark') }}"
          confirmation="{{ __('bookmarks.remove_bookmark_confirmation') }}"
          styling="default"
      >
        <x-slot:formBody>
          <input type="hidden" name="bookmark-button" />
        </x-slot:formBody>

        <span class="flex items-center flex-no-wrap">
          <x-ui.icon
              type="solid"
              class="mr-3"
              name="bookmark"
              size="3"
          />

          <span class="whitespace-nowrap">{{ __('bookmarks.remove_bookmark') }}</span>
        </span>

      </x-ui.confirm-button>
    </div>

  @endif

