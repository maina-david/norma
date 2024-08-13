<div x-data="{ showChildren: null,
timeout: null,
  debounce(func, wait) {
      let later = function() {
        this.timeout = null;
        func.apply(this, []);
      }
      clearTimeout(this.timeout);
      this.timeout = setTimeout(later, wait);
  }
}" class="mt-10 print:hidden">
  @foreach ($items as $index => $item)
    @if (empty($item))
      <div class="mt-14"></div>
    @else
      <div @mouseenter="debounce(() => showChildren = {{ $index }}, 150)"
           @mouseleave="debounce(() => showChildren = null, 0)">
        <a
          target="_top"
          data-turbo="false"
          href="{{ $item['route'] }}"
          class="flex flex-row mt-6 mb-2 items-center hover:text-sidebar-active {{ $item['current_module'] ? 'text-sidebar-active' : '' }}"
        >
          <x-ui.icon :name="$item['icon']" class="mx-5 text-lg text-center -mt-1" />
          <span class="whitespace-nowrap">
            {{ $item['label'] }}
          </span>
          @if (isset($item['beta']) && $item['beta'])
            <span class="text-xs text-libryo-gray-300 mt-1 ml-1.5 italic">{{ __('interface.beta') }}</span>
          @endif
        </a>

        @if (isset($item['children']))
          <div x-show="showChildren === {{ $index }} || {{ $item['current_module'] ? 'true' : 'false' }}"
               x-cloak
               x-transition:enter="transition ease-out duration-300"
               x-transition:enter-start="opacity-0 scale-y-0 -translate-y-1/2"
               x-transition:enter-end="opacity-100 scale-y-100 translate-y-0"
               x-transition:leave="transition ease-in duration-100"
               x-transition:leave-start="opacity-100 scale-y-100 translate-y-0"
               x-transition:leave-end="opacity-0 scale-y-0 -translate-y-1/2"
               class="bg-sidebar-background-sub p-3 pl-5 ">
            @foreach ($item['children'] as $child)
              <a
                target="_top"
                data-turbo="false"
                href="{{ $child['route'] }}"
                class="flex flex-row my-3 items-center hover:text-sidebar-active text-sm"
              >
                <x-ui.icon name="arrow-turn-down-right" class="mr-5 text-lg text-center -mt-1" />
                <span class="whitespace-nowrap">
                  {{ $child['label'] }}
                </span>
              </a>
            @endforeach
          </div>
        @endif
      </div>
    @endif
  @endforeach
</div>
