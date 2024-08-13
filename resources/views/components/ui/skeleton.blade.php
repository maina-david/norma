@props(['rows' => 1, 'delayed' => false, 'flat' => false, 'noCircle' => false])
@php
  $classes = $flat ? 'w-full mx-auto' : 'border border-libryo-gray-200 shadow rounded-md p-4'
@endphp
<div x-show="showing" x-data="{ showing: true }" {!! $delayed ? 'x-init="showing = false; setTimeout(function(){ showing = true}, 400); "' : '' !!}
     {{ $attributes->merge(['class' => $classes]) }}>
  @for ($i = 0; $i < $rows; $i++)
    <div class="animate-pulse flex space-x-4">
      @if(!$noCircle)
        <div class="rounded-full bg-libryo-gray-300 h-12 w-12"></div>
      @endif
      <div class="flex-1 space-y-4 py-1">
        <div class="h-4 bg-libryo-gray-300 rounded w-3/4"></div>
        <div class="space-y-2">
          <div class="h-4 bg-libryo-gray-300 rounded"></div>
          <div class="h-4 bg-libryo-gray-300 rounded w-5/6"></div>
        </div>
      </div>
    </div>
  @endfor
</div>
