<x-layouts.settings>
  <x-slot name="header">
    <div class="{{ $action === 'compile' ? 'text-positive' : 'text-negative' }}">
      <x-ui.icon name="books" size="10" class="mr-5 text-norma-gray-600" /> {{ __('settings.nav.generic_compilation') }}:
      {{ ucfirst($action) }}
      {{ $library->title }}
    </div>

  </x-slot>

  <x-ui.card>
    <div class="p-10">
      @include('partials.compilation.my.library.generic-compilation-form', [
      'route' => route('my.settings.compilation.generic.' . $action, ['library' => $library->id]),
      'library' => $library
      ])
    </div>
  </x-ui.card>

</x-layouts.settings>
