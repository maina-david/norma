<turbo-stream action="{{ $action ?? 'update' }}" target="{{ $target }}">
  <template>
    <x-dynamic-component :component="$component" {{ $attributes }} />
  </template>
</turbo-stream>
