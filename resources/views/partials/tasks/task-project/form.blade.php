<x-ui.input :value="old('title', $project->title ?? '')" name="title" label="{{ __('interface.title') }}"
            required />

<x-ui.input type="textarea" :value="old('description', $project->description ?? '')" name="description"
            label="{{ __('interface.description') }}" />


<x-slot name="footer">
  <div></div>
  <div>

    <x-ui.button type="submit" theme="primary">{{ __('actions.save') }}</x-ui.button>
  </div>
</x-slot>
