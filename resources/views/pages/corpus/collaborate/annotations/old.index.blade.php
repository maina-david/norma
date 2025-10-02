@php
  $showBulkActions = auth()->user()->can('collaborate.corpus.work-expression.use-bulk-actions');
@endphp

<x-layouts.collaborate fluid no-padding>

  <x-slot name="pageHead">
    @vite(['resources/js/collaborate/annotations.js'])
  </x-slot>

  <x-slot name="header">
    <x-turbo::frame target="_top" id="workflow-task-header" :src="route('collaborate.workflow.tasks.header', ['expression' => $expression->id])" />
  </x-slot>

  <div
      x-data="{
      selectedRefs: {},
      loading: true,
      showToC: false,
      showSource: false,
      showNotes: false,
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
      x-init="function () {
      var _this = this;
      document.addEventListener('turbo:submit-start', function () {
        _this.loading = true;
      });
      document.addEventListener('turbo:submit-end', function () {
        _this.loading = false;
      });
    }"
      class="grid grid-cols-2 gap-1 h-full overflow-hidden"
      id="annotations"
  >
    <div x-show="loading" class="fixed inset-0 flex items-center justify-center z-50 bg-gray-400 bg-opacity-25">
      <x-ui.icon name="cog" class="fa-spin" size="8" />
    </div>
    <div class="flex flex-col overflow-hidden">
      <x-ui.form x-ref="contentForm" method="GET" x-bind:action="'{{ route('collaborate.work-expressions.volume.show', ['expression' => $expression->id]) }}?volume='+volume"/>

      <x-turbo::frame id="meta_togglers" :src="route('collaborate.work-expressions.references.meta.frame', ['expression' => $expression->id])" />

      <div class="flex-grow grid gap-1 overflow-hidden" x-bind:class="showToC || showNotes ? 'grid-cols-2': ''">
        <x-turbo::frame x-show="showToC" style="display: none;" loading="lazy" class="overflow-hidden" id="work_expression_toc" src="{!! route('collaborate.work-expressions.references.toc', ['expression' => $expression->id, ...request()->query()]) !!}">
          <x-ui.skeleton />
        </x-turbo::frame>

        @include('pages.corpus.collaborate.annotations.partials.notes-tab')

        <template x-if="showSource">
          <div class="flex-grow w-full overflow-hidden col-span-2">
            <embed class="w-full h-full" src="{{ route('collaborate.work-expressions.source.show', ['expression' => $expression->id]) }}">
          </div>
        </template>

        <x-turbo::frame x-show="!showSource" class="flex-grow w-full overflow-hidden" id="work_expression_content" src="{{ route('collaborate.work-expressions.volume.show', ['expression' => $expression->id, 'volume' => request('volume', 1)]) }}">
          <x-ui.skeleton />
        </x-turbo::frame>
      </div>
    </div>

    <div class="h-full overflow-hidden flex flex-col">

      @if($showBulkActions)
        <div
            x-show="Object.values(selectedRefs).filter(function (i) { return !!i }).length > 0"
            x-bind:class="showActions ? '' : 'h-10'"
            class="flex-shrink-0 flex flex-col overflow-hidden border-b border-l border-norma-gray-300 rounded-bl w-screen-50 bg-norma-gray-100"
            style="max-height:50vh"
            x-data="{showActions: false}"
        >
          <div @click="showActions = !showActions" class="flex-shrink-0 px-4 py-2 cursor-pointer flex items-center justify-center font-medium text-primary bg-norma-gray-200">
            <span class="mr-2">{{ __('corpus.work_expression.bulk_actions') }} - </span>
            <span class="mr-2" x-html="Object.keys(selectedRefs).length"></span>
            <x-ui.icon name="angle-down" x-show="!showActions" />
            <x-ui.icon name="angle-up" x-show="showActions" />
          </div>

          <div class="flex-grow max-h-full overflow-auto custom-scroll">
            <x-turbo::frame id="annotation-bulk-actions" :src="route('collaborate.corpus.references.meta.bulk.show', ['expression' => $expression->id])">
              <x-ui.skeleton />
            </x-turbo::frame>
          </div>
        </div>
      @endif

      <x-turbo::frame class="hidden" id="annotation-meta-template" :src="route('collaborate.corpus.references.meta.template', ['expression' => $expression->id])">
      </x-turbo::frame>

      <x-turbo::frame class="flex-grow flex flex-col overflow-hidden" id="work_expression_references" src="{!! route('collaborate.work-expressions.references.filters', ['expression' => $expression->id, ...request()->query()]) !!}">
        <x-ui.skeleton />
      </x-turbo::frame>

    </div>

  </div>

</x-layouts.collaborate>
