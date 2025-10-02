<a href="{{ $route }}"
   {{ $attributes->merge(['class' => 'block flex flex-row justify-between bg-white hover:bg-norma-gray-50 p-3 items-center mt-px shadow-sm']) }}>
  <div class=" pr-5">
    <x-ui.icon name="folder" size="8"
               class="text-{{ $color ?? 'gray' }} {{ isset($empty) && $empty ? 'opacity-50' : '' }}" />
  </div>
  <div class="grow {{ isset($empty) && $empty ? 'text-norma-gray-400 italic' : '' }}">
    <div>{{ $slot }}</div>
    @if (isset($empty) && $empty)
      {{ __('storage.drives.empty_folder') }}
    @endif
    <div></div>
  </div>
  <div class="">
    <x-ui.icon name="chevron-right" class="text-norma-gray" />
  </div>
</a>
