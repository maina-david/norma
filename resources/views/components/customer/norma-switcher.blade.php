@if ($isAllMode)
  <span class="text-secondary text-sm font-semibold">
    {!! __('customer.organisation.multi_norma_stream_mode') !!}
  </span>
@else
  <span class="text-primary-darker text-sm font-semibold">
    {!! __('customer.norma.single_norma_stream_mode') !!}
  </span>
@endif

<div x-data="{ searchStr: '' }">
  <x-ui.dropdown class="w-80">
    <x-slot name="trigger">
      <div class="text-sm flex flex-row justify-between cursor-pointer">
        <div class="text-sm truncate max-w-screen-50 text-primary">
          {{ $active['title'] ?? __('customer.organisation.all_normas_for_org', ['title' => $organisation->title]) }}
        </div>
        <div class="ml-3">
          <x-ui.icon name="chevron-down" size="3">
          </x-ui.icon>
        </div>
      </div>
    </x-slot>

    <div class="px-6 pt-3 pb-6 text-sm">
      @if (!$isAllMode)
        <div class="border-b mb-4 pb-3 text-center">
          <x-ui.form method="post" action="{{ route('my.normas.activate.all') }}" data-turbo="false" target="_top">
            <x-ui.button type="submit" theme="secondary" styling="outline" role="menuitem"
                         tabindex="-1">
              {{ __('customer.norma.activate_multi') }}
            </x-ui.button>
          </x-ui.form>
        </div>
      @else
        @if ($lastActive)
          <div class="border-b mb-4 pb-3 text-center">
            <x-ui.form method="post" action="{{ route('my.normas.activate', ['norma' => $lastActive->id]) }}" data-turbo="false" target="_top">
              <x-ui.button type="submit" theme="primary" styling="outline" role="menuitem"
                           tabindex="-1">
                {{ __('customer.norma.activate_single') }}
              </x-ui.button>
            </x-ui.form>
          </div>
        @endif
      @endif

      <div class="font-bold">{{ __('customer.norma.switch_stream') }}</div>
      <div class="flex flex-row items-center mt-5">
        <x-ui.icon name="search" class="text-xl mr-5" />
        <turbo-frame id="norma-switcher-search-input">
          <x-ui.form x-ref="normaSwitcherSearchForm" method="post" :action="route('my.normas.switcher.search')">
            <x-ui.input
                        @keyup.debounce="searchStr = $el.value;  if(searchStr !== '') $refs.normaSwitcherSearchForm.requestSubmit();"
                        name="search_streams" :placeholder="__('customer.norma.search_for_stream')"
                        class="text-sm"
                        autocomplete="off" />
          </x-ui.form>
        </turbo-frame>
      </div>


      <div x-show="searchStr === ''">
        <div class="flex flex-row items-center text-sm mt-5 font-bold">
          <x-ui.icon name="history" class="mr-5" />
          <div>{{ __('customer.norma.recently_viewed') }}</div>
        </div>
        <div>
          <turbo-frame src="{{ route('my.normas.switcher.recent') }}"
                       loading="lazy"
                       id="norma-switcher-recent-list"
                       target="_top">
            <x-ui.skeleton />
          </turbo-frame>
        </div>
      </div>
      <div x-show="searchStr !== ''" class="h-screen overflow-hidden" style="max-height: 50vh;">
        <turbo-frame id="norma-switcher-search-results" target="_top" class="h-full"></turbo-frame>
      </div>

      <div class="mt-5 text-center hover:text-primary">
        <a href="{{ route('my.customer.normas.index') }}">{{ __('interface.see_all') }}</a>
      </div>

    </div>
  </x-ui.dropdown>
</div>
