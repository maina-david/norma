<x-compilation.context-question.collaborate.layout :context-question="$contextQuestion">
  <div>
    <x-ontology.category-selector name="ids[]" required :label="__('ontology.category.topics')" />

    <div class="flex justify-end">
      <x-ui.button type="submit" theme="primary" class="mt-10">{{ __('actions.add') }}</x-ui.button>
    </div>

  </div>
</x-compilation.context-question.collaborate.layout>
