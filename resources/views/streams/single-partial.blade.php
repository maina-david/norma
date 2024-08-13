<turbo-stream action="{{ $action ?? 'update' }}" target="{{ $target }}">
  <template>
    @include($partialView)
  </template>
</turbo-stream>
