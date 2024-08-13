<x-ui.input :value="old('title', $resource->title ?? '')" name="title" label="{{ __('interface.title') }}" required />


<x-ui.input type="textarea" :value="old('description', $resource->description ?? '')" name="description"
            label="{{ __('interface.description') }}" />

<div class="my-5">
  <x-ui.input type="checkbox"
              :value="old('auto_compiled', isset($resource) ? $resource->auto_compiled : true)"
              name="auto_compiled"
              label="{{ __('compilation.library.auto_compiled') }}" />

</div>

<x-slot name="footer">
  <div></div>
  <div>
    <x-ui.back-button
                      :fallback="isset($resource) && $resource->id ? route('my.settings.libraries.show', ['library' => $resource->id]) : route('my.settings.libraries.index')" />
    <x-ui.button type="submit" theme="primary">{{ __('actions.save') }}</x-ui.button>
  </div>
</x-slot>
