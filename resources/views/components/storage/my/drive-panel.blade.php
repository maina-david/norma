<a href="{{ $route }}"
   {{ $attributes->merge(['class' => 'block p-5 bg-white flex flex-row items-center hover:bg-libryo-gray-100 rounded shadow']) }}>
  <div class="px-3 mr-5">
    <x-ui.icon name="hdd" size="10" class="text-{{ $color ?? 'gray' }}" />
  </div>
  <div>
    <div class="font-bold text-lg">{{ $slot }}</div>
    <div class="text-libryo-gray-600 text-sm">{{ $subtext ?? '' }}</div>
  </div>
</a>
