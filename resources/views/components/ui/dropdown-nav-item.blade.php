@props(['route'])

<x-ui.dropdown class="sm:mt-0">
  <x-slot name="trigger">
    <x-ui.nav-item class="h-full w-full">
      {{ $nav }}
    </x-ui.nav-item>
  </x-slot>

    {{ $slot }}
</x-ui.dropdown>
