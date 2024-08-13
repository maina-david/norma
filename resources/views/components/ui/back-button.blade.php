@props(['fallback'])

<x-ui.button data-turbo="false" target="_top" type="link" href="#" x-show="history.length > 0" {{ $attributes }} onclick="window.history.back()">
  {{ __('actions.back') }}
</x-ui.button>
