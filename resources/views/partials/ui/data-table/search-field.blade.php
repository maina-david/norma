<input type="hidden" name="page" value="1">

<div class="flex items-center relative max-w-md w-full justify-start">
  <div x-ref="searchContainer" class="flex-grow">
    @if (isset($customSearch))
      {{ $customSearch }}
    @else
      {{-- If this data table has a custom search route, we will use it to populate the dropdown --}}
      @if ($searchSuggestRoute !== '')
        <x-ui.dropdown @open-search-suggest.window="open = true" position="right" class="xl:-left-1/2">
          <x-slot name="trigger">
            <x-ui.form method="POST" :action="$searchSuggestRoute">
              <div>
                <x-ui.input @input.debounce.350ms="if($el.value.length > 2) { $el.closest('form').requestSubmit(); $dispatch('open-search-suggest')}"
                            class="mt-0 rounded-md {{ $filterable ? ($sideFilters ? 'rounded-r-none lg:rounded-r-md' : 'rounded-r-none') : '' }}"
                            {{-- @keyup.enter="submitFilterForm()" --}}
                            name="search"
                            autocomplete="off"
                            {{-- value="{{ $getFilterValue('search') }}" --}}
                            placeholder="{{ $searchPlaceholder ?? __('interface.search') . '...' }}" />
              </div>
            </x-ui.form>
          </x-slot>

          <div class="w-full lg:w-screen-50 px-5 py-3">
            <turbo-frame id="{{ $searchSuggestTarget }}" loading="lazy"
                         src="{{ $searchSuggestRoute }}">
              <x-ui.skeleton />
            </turbo-frame>
          </div>
        </x-ui.dropdown>
      @else
        {{-- No search route specified --}}
        <x-ui.dropdown @open-search-suggest.window="open = true" position="right" class="xl:-left-1/2">
          <x-slot name="trigger">
            <x-ui.input x-model="search" x-ref="searchField"
                        placeholder="{{ $searchPlaceholder ?? __('interface.search') . '...' }}"
                        name="search"
                        class="mt-0 rounded-md {{ $filterable ? 'rounded-r-none' : '' }} {{ $filterable && ($sideFilters ?? false) ? 'lg:rounded-r-md' : '' }}"
                        @keyup="$refs['filter-inputs-search'].value = $el.value; $dispatch('open-search-suggest'); if($el.value === '') submitFilterForm();"
                        @keydown.enter="function (e) { e.preventDefault(); submitFilterForm()}"
                        {{-- value="{{ $getFilterValue('search') }}" --}}
                        autocomplete="off"
                        onfocus="val = this.value; this.value=''; this.value=val; //puts cursor at the end" />
          </x-slot>

          <div x-show="search.length > 0" class="w-full lg:w-screen-50 px-5 py-3">
            <div class="text-xl mt-10 mb-3">
              <x-ui.icon name="search" class="mr-3" />
              <span>{{ __('interface.word_search') }}</span>
            </div>
            <div class="italic text-sm">
              <span>{{ $searchPlaceholder ?? __('interface.search') }}: </span>
              <a x-text="search" @click="submitFilterForm()" data-turbo-frame="_top"
                 class="text-primary cursor-pointer">
              </a>
            </div>
          </div>
        </x-ui.dropdown>
      @endif
    @endif
  </div>
  <button x-show="search.length > 0" style="display:none" type="button"
          @click="search.value = ''; $refs['filter-inputs-search'].value=''; submitFilterForm();"
          class="mt-1 hover:text-primary text-libryo-gray-300 transition-colors ease-in-out w-8 flex justify-center items-center absolute right-0 top-0 h-full bg-transparent">
    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
         stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M6 18L18 6M6 6l12 12" />
    </svg>
  </button>
</div>
