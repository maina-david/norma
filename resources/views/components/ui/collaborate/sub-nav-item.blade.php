@if (isset($route))
  <a href="{{ route($route, $routeParams) }}" {{ $attributes->merge(['class' => $classes]) }}
     aria-current="page">
    @if (isset($icon))
      <span class="w-5 mr-5">
        <x-ui.icon :name="$icon" />
      </span>
    @endif

    {{ $slot }}
  </a>
@else
  <span {{ $attributes->merge(['class' => $classes]) }} aria-current="page">
    @if (isset($icon))
      <span class="w-5 mr-5">
        <x-ui.icon :name="$icon" />
      </span>
    @endif
    {{ $slot }}
  </span>
@endif
