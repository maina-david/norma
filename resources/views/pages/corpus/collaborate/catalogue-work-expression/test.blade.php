@php
  $user = auth()->user();
@endphp

<x-layouts.collaborate fluid no-padding>
  <x-slot name="pageHead">
    @vite(['resources/js/collaborate/catalogue-test.js'])
  </x-slot>

  <div id="app" class="h-full overflow-hidden"></div>
</x-layouts.collaborate>
