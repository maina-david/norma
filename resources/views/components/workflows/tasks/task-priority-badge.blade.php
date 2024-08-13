@props(['priority'])
@php
  $priority = is_null($priority) ? null : \App\Enums\Workflows\TaskPriority::fromValue($priority);
@endphp
@if(!is_null($priority))
  <div class="inline-flex rounded-full items-center py-0.5 px-2 text-xs font-medium text-white whitespace-nowrap {{ $priority->colour() }}">
    {{ $priority->label() }}
  </div>
@endif

