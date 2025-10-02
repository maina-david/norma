<x-ui.link
  data-turbo="{{ isset($turbo) && $turbo === false ? 'false' : 'true' }}"
  :href="$href"
  target="{{ $target ?? '' }}" data-turbo-frame="_top"
>
  <span class="flex flex-col">
    <span>{{ $title }}</span>
    @if(!empty($description))
      <span class="text-norma-gray-500 text-xs">{{ $description }}</span>
    @endif
  </span>
</x-ui.link>
