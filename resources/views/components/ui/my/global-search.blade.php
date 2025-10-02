<x-ui.modal>
  <x-slot name="trigger">
    <button @click="open = true" type="button"
            class="bg-white p-1 rounded-full text-norma-gray-600 hover:text-norma-gray-900 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-900 mr-1.5 md:mr-5">
      <span class="sr-only">Search</span>

      <x-ui.icon name="search" />
    </button>
  </x-slot>

  <div x-data="{ searchStr: '', handleNavigate: function(href) { window.location = href + '?search=' + encodeURIComponent(this.searchStr) } }"
       class="" style="width: 85vw;">

    <x-ui.input x-model="searchStr"
                name="global-search"
                :placeholder="__('interface.search') . '...'"
                class="mb-5 mt-5"
                autocomplete="off" />

    <div x-show="searchStr === ''">
      <x-ui.empty-state-icon icon="arrow-up"
                             :title="__('interface.start_typing_to_search') . '...'" />
    </div>

    <div x-show="searchStr !== ''">

      <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-3">
        @foreach ($searchItems as $item)
          <div
               class="relative rounded-lg border border-norma-gray-100 bg-white px-6 py-5 shadow-sm flex items-center space-x-10 hover:border-primary focus-within:ring-2 focus-within:ring-offset-2 focus-within:primary">
            <div class="flex-shrink-0">
              <x-ui.icon :name="$item['icon']" />
            </div>
            <div class="flex-1 min-w-0">
              <a @click="handleNavigate('{{ $item['link'] }}')"
                 class="focus:outline-none cursor-pointer">
                <span class="absolute inset-0" aria-hidden="true"></span>
                <p class="text-sm font-medium text-norma-gray-900">{{ $item['heading'] }}</p>
                <p class="text-sm text-norma-gray-500 truncate">{{ $item['text'] }}
                  <span class="cursorPointer text-primary hover:text-primary-darker" x-text="searchStr"></span>
                </p>
              </a>
            </div>
          </div>
        @endforeach
      </div>
    </div>
  </div>

</x-ui.modal>
