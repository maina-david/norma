@if (isset($route))
  <a href="{{ route($route) }}" {{ $attributes->merge(['class' => $classes]) }} aria-current="page">
    <span class="w-5 mr-5">
      <x-ui.icon :name="$icon" />
    </span>
    {{ $slot }}
  </a>
@else
  <span {{ $attributes->merge(['class' => $classes]) }} aria-current="page">
    {{ $slot }}
  </span>
@endif
