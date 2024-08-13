<x-layouts.settings>
  <x-slot name="header">
    <span class="flex items-center">
      <x-ui.icon name="map-marker" size="10" class="mr-5 text-libryo-gray-600" type="duotone" />
      <span>{{ $libryo->title }}</span>
    </span>
  </x-slot>

  <x-ui.card>

    <div>
      <nav class="-mb-px flex space-x-8 w-full overflow-x-auto">
        @foreach ($navItems as $item)
          <a href="{{ $item['route'] }}"
             {{ $attributes->merge(['class' => 'whitespace-nowrap py-4 px-2 border-b-2 font-medium text-sm transition-colors transition ease-in-out duration-200 cursor-pointer ' . ($item['isCurrent'] ? 'border-primary text-primary' : 'border-transparent text-libryo-gray-500 hover:border-primary hover:text-primary')]) }}>
            {{ $item['label'] }}
          </a>
        @endforeach
      </nav>
    </div>

    {{ $slot }}
  </x-ui.card>
</x-layouts.settings>
