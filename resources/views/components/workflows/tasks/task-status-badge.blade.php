@props(['status'])
@php
  $status = \App\Enums\Workflows\TaskStatus::fromValue($status);
@endphp
{{-- bg-red-800 bg-red-600 bg-red-400 bg-red-200--}}


<div class="inline-flex rounded-full items-center py-0.5 px-2 text-xs font-medium text-white whitespace-nowrap {{ $status->colour() }}">
  {{ $status->label() }}
</div>
