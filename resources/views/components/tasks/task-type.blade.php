@if ($linkable && $href !== '')
  <a data-turbo-frame="_top" href="{{ $href }}">
@endif
<x-ui.icon class="text-{{ $color }} tippy" :name="$icon" data-tippy-content="{{ $tooltip }}" />
@if ($linkable && $href !== '')
  @if ($text !== '')
    <span class="ml-1 text-{{ $color }}">{{ $text }}</span>
  @endif
  </a>
@endif
