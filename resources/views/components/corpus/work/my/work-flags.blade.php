<div class="flex flex-row items-center">
  @if (!$showMultipleFlags)
    <div class="h-{{ $size }} w-{{ $size }}">
      <x-ui.country-flag :country-code="$getLocation()?->flag"
                         class="h-{{ $size }} w-{{ $size }} rounded-full tippy"
                         data-tippy-content="{{ $getLocation()?->title }}" />
    </div>
  @endif

  @if ($showMultipleFlags)
    <div class="w-full flex items-center space-x-1">
      @foreach ($getLocations as $index => $location)
        <div class="h-{{ $size }} w-{{ $size }}">
          <x-ui.country-flag
                             :country-code="$location->flag"
                             class="h-{{ $size }} w-{{ $size }} rounded-full tippy"
                             data-tippy-content="{{ $location?->title }}" />
        </div>
      @endforeach
    </div>
  @endif

</div>
