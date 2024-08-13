@php
  $user = auth()->user();
@endphp

<x-layouts.collaborate fluid no-padding>
  <x-slot name="pageHead">
    @vite(['resources/js/collaborate/catalogue-expression.js'])
  </x-slot>

  <x-slot name="header">
    <div class="px-4 py-1">
      <div class="text-sm">{{ $expression->catalogueWork->title }}</div>
      <div class="text-xs">{{ $expression->catalogueWork->title_translation }}</div>
    </div>
  </x-slot>

  <div id="app" data-expression="{{ $expression->id }}"></div>
</x-layouts.collaborate>
