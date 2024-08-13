<x-compilation.context-question.collaborate.layout :context-question="$contextQuestion">
  <div>
    <x-geonames.location.location-selector show-path name="ids[]" required :label="__('compilation.requirements_collection.select_collection')" />

    <div class="flex justify-end">
      <x-ui.button type="submit" theme="primary" class="mt-10">{{ __('actions.add') }}</x-ui.button>
    </div>

  </div>
</x-compilation.context-question.collaborate.layout>
