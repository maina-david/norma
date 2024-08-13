<x-compilation.context-question.collaborate.layout :context-question="$resource">
  <x-ui.show-field :label="__('compilation.context_question.question')" :value="$resource->question" />

  <x-ui.show-field :label="__('compilation.context_question.category.category')"
                   :value="$resource->category_id
                       ? \App\Enums\Compilation\ContextQuestionCategory::lang()[$resource->category_id]
                       : '-'" />

  <x-ui.show-field :label="__('compilation.context_question.jurisdictions')">
    @foreach ($resource->locations as $location)
      <div>{{ $location->title }}</div>
    @endforeach
  </x-ui.show-field>


  <x-ui.show-field :label="__('collaborators.group.created_at')">
    <x-ui.timestamp :timestamp="$resource->created_at" />
  </x-ui.show-field>
</x-compilation.context-question.collaborate.layout>
