@if(isset($route))
  <a href="{{ route($route, $routeParams) }}" {{ $attributes->merge(['class' => $classes]) }} aria-current="page">
    {{ $slot }}
  </a>
@else
  <span {{ $attributes->merge(['class' => $classes]) }} aria-current="page">
    {{ $slot }}
  </span>
@endif
