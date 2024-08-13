<a @if(!empty($disableTracking) && $disableTracking) clicktracking=off @endif href="{{ $url }}" class="full-button-container" target="_blank">
    <p  class="button-full button-full-{{ $color ?? 'brand' }}" >{{ $slot }}</p>
</a>

