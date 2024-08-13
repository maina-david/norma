
<x-ui.input
  :value="old('text', $resource->text ?? '')"
  name="text"
  label="{{ __('corpus.reference.text') }}"
/>

<x-slot name="footer">
  <div></div>
  <div>
    <x-ui.back-button :fallback="route('collaborate.notification-actions.index')" />
    <x-ui.button type="submit" theme="primary">{{ __('actions.save') }}</x-ui.button>
  </div>
</x-slot>
