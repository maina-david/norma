<nav {{ $attributes->merge(['class' => 'flex-1 px-2 bg-white space-y-1']) }}>
  @foreach ($items as $item)
    @if ($canSeeItem($item))
      <x-ui.collaborate.sub-nav-item class="w-full sm:w-auto"
                                     :item="$item">{{ $item['label'] }}

      </x-ui.collaborate.sub-nav-item>
    @endif
  @endforeach
</nav>
