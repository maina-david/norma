<x-ui.input :value="old('title', $resource->title ?? '')" name="title" label="{{ __('interface.title') }}" required />

<x-slot name="footer">
  <div></div>
  <div>
    <x-ui.back-button :fallback="route('collaborate.tags.index')" />
    <x-ui.button type="submit" theme="primary">{{ __('actions.save') }}</x-ui.button>
  </div>
</x-slot>
