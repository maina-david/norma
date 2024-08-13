@props(['position' => 'right', 'openVariable' => 'open', 'triggerClass' => '', 'teleport' => false, 'isOpen' => false])
@php
$positionClass = match ($position) { 'right' => 'left-0',  'left' => 'right-0',  'center' => '-left-1/2' };
@endphp
<div class="relative" x-data="{ {{ $openVariable }}: {{ json_encode($isOpen) }} }">
  <div class="h-full {{ $triggerClass }}" @click.stop.prevent="{{ $openVariable }} = !{{ $openVariable }}">
    {{ $trigger }}
  </div>
@if($teleport)
  <template x-teleport="body">
@endif
  <div
     x-transition:enter="transition ease-out duration-100"
     x-transition:enter-start="transform opacity-0 scale-95"
     x-transition:enter-end="transform opacity-100 scale-100"
     x-transition:leave="transition ease-in duration-75"
     x-transition:leave-start="transform opacity-100 scale-100"
     x-transition:leave-end="transform opacity-0 scale-95"
     x-show="{{ $openVariable }}"
     @click.away="{{ $openVariable }} = false"
     style="display:none;"
     role="menu"
     aria-orientation="vertical"
     aria-labelledby="user-menu-button"
     tabindex="-1"
     {{ $attributes->merge(['class' => 'origin-top-right absolute ' . $positionClass . ' mt-2 rounded-md shadow-lg py-1 bg-white ring-1 ring-black ring-opacity-5 focus:outline-none z-40']) }}
  >
    {{ $slot }}
  </div>
@if($teleport)
  </template>
@endif
</div>
