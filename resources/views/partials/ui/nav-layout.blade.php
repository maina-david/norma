<div>
  <x-ui.card>
    <div>
      <nav class="-mb-px flex space-x-8 w-full overflow-x-auto">
        @foreach ($navItems as $item)
          <a href="{{ $item['route'] }}"
             class="whitespace-nowrap py-4 px-2 border-b-2 font-medium text-sm transition-colors transition ease-in-out duration-200 cursor-pointer {{ $item['isCurrent'] ? 'border-primary text-primary' : 'border-transparent text-libryo-gray-500 hover:border-primary hover:text-primary' }}">
            {{ $item['label'] }}
          </a>
        @endforeach
      </nav>
    </div>

    {{ $slot }}
  </x-ui.card>
</div>
