<x-layouts.collaborate fluid no-padding>

  <x-slot name="pageHead">
    @vite(['resources/js/collaborate/annotations.js'])
  </x-slot>

  <x-slot name="header">
    <x-turbo::frame target="_top" id="workflow-task-header" :src="route('collaborate.workflow.tasks.header', ['expression' => $expression->id])" />
  </x-slot>

  <div
    x-data="{
      showSource: false,
      volume: {{ request('volume', 1) }},
      contentRoute: '{{ route('collaborate.work-expressions.volume.show', ['expression' => $expression->id]) }}',
      evaluateSelector: function(el, selectors) {
        var volume = el.getAttribute('data-volume');
        this.volume = volume;

        if (window.annotations) {
          window.annotations.evaluateSelector(el, selectors, this.contentRoute);
        }
      },
    }"
    class="grid grid-cols-2 gap-1 h-full overflow-hidden"
    id="annotations"
  >
    <div class="flex flex-col overflow-hidden">
      <x-ui.form x-ref="contentForm" method="GET" x-bind:action="'{{ route('collaborate.work-expressions.volume.show', ['expression' => $expression->id]) }}?volume='+volume"/>

      <div class="flex items-center flex-shrink-0 pl-4 py-1 bg-white space-x-2 border-b border-norma-gray-100">
        <span class="flex items-center space-x-2 text-norma-gray-500">
          <x-ui.icon name="eye"/>
          <span>{{ __('actions.show') }}</span>
        </span>

        <button
            data-tippy-content="{{ __('corpus.work_expression.source_document') }}"
            x-bind:class="showSource ? 'border-primary text-primary' : 'border-transparent'"
            class="tippy h-8 w-8 hover:text-primary flex items-center justify-center border-t-2"
            @click="showSource = !showSource"
        >
          <x-ui.icon name="file-pdf"/>
        </button>
      </div>

      <div class="flex-grow grid gap-1 overflow-hidden grid-cols-2">
        <div x-show="showSource" class="flex-grow w-full overflow-hidden col-span-2">
          <embed class="w-full h-full" src="{{ route('collaborate.work-expressions.source.show', ['expression' => $expression->id]) }}">
        </div>

        <x-turbo::frame x-show="!showSource" class="flex-grow w-full overflow-hidden col-span-2" id="work_expression_content" src="{{ route('collaborate.work-expressions.volume.show', ['expression' => $expression->id, 'volume' => request('volume', 1)]) }}">
          <x-ui.skeleton />
        </x-turbo::frame>
      </div>
    </div>

    <x-turbo::frame class="flex flex-col overflow-hidden" id="work_expression_references" src="{{ route('collaborate.work-expressions.creation.references.index', ['expression' => $expression->id, 'volume' => request('volume', 1)]) }}">
      <x-ui.skeleton />
    </x-turbo::frame>
  </div>

</x-layouts.collaborate>
