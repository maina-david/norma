<div>
  <div class="{{ ($bordered ?? false) ? 'border-b border-libryo-gray-200 mb-4' : '' }}">
    <nav class="flex space-x-8 w-full overflow-x-auto mb-2">
      @foreach ($navItems as $item)
        <a
          data-tippy-content="{{ $item['tooltip'] ?? '' }}"
          target="_top"
          href="{{ $item['route'] }}"
          class="{{ isset($item['tooltip']) ? 'tippy ' : '' }}flex items-center whitespace-nowrap py-4 px-2 border-b-2 font-medium text-sm transition-colors ease-in-out duration-200 cursor-pointer {{ $item['isCurrent'] ? 'border-primary text-primary' : 'border-transparent text-libryo-gray-500 hover:border-primary hover:text-primary' }}"
        >
          @isset($item['icon'])
            <span class="mr-2">
              <x-ui.icon :name="$item['icon']" />
            </span>
          @endisset
          <span>{{ $item['label'] }}</span>

          @isset($item['beta'])
              <span class="ml-2 rounded-full bg-libryo-gray-600 text-xs px-2 pb-0.5 text-white tippy" data-tippy-content="{{ __('interface.alpha_tooltip') }}">
                {{ __('interface.beta') }}
              </span>
            @endisset
        </a>
      @endforeach
    </nav>
  </div>

  {{ $slot }}
</div>
