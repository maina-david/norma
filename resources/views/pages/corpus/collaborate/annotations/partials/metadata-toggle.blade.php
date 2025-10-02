@php
$shown = collect(explode('|', request()->query('show')))->filter();
$visible = $shown->mapWithKeys(fn($item) => [$item => $item]);

$query = ['page' => request('page'), 'expression' => $expression->id];

$toggle = function ($key) use ($shown, $query) {
    $index = $shown->search($key);

    if ($index !== false) {
        $shown->splice($index, 1);
    } else {
        $shown->push($key);
    }

    if ($shown->isNotEmpty()) {
      $query['show'] = $shown->join('|');
    }

    return route('collaborate.work-expressions.references.meta.index', $query);
}
@endphp

<div class="flex items-center flex-shrink-0 pl-4 py-1 bg-white space-x-2 border-b border-norma-gray-100">
  <span class="flex items-center space-x-2 text-norma-gray-500">
    <x-ui.icon name="eye"/>
    <span>{{ __('actions.show') }}</span>
  </span>

    <x-corpus.reference.metadata-icon
        :tooltip="__('nav.assessment_items')"
        :visible="$visible->has('assessments')"
        :target="$toggle('assessments')"
        icon="check"
    />

    <x-corpus.reference.metadata-icon
        :tooltip="__('nav.topics')"
        :visible="$visible->has('topics')"
        :target="$toggle('topics')"
        icon="hashtag"
    />

    <x-corpus.reference.metadata-icon
        :tooltip="__('nav.context_questions')"
        :visible="$visible->has('context')"
        :target="$toggle('context')"
        icon="question"
    />

    <x-corpus.reference.metadata-icon
        :tooltip="__('nav.legal_domains')"
        :visible="$visible->has('domains')"
        :target="$toggle('domains')"
        icon="scale-balanced"
    />

    <x-corpus.reference.metadata-icon
        :tooltip="__('nav.jurisdictions')"
        :visible="$visible->has('jurisdictions')"
        :target="$toggle('jurisdictions')"
        icon="location-dot"
    />

    <x-corpus.reference.metadata-icon
        :tooltip="__('nav.tags')"
        :visible="$visible->has('tags')"
        :target="$toggle('tags')"
        icon="tags"
    />

    <button
        data-tippy-content="{{ __('corpus.work_expression.show_toc') }}"
        x-bind:class="showToC ? 'border-primary text-primary' : 'border-transparent'"
        class="tippy h-8 w-8 hover:text-primary flex items-center justify-center border-t-2"
        @click="showNotes = false;showToC = !showToC"
    >
      <x-ui.icon name="list"/>
    </button>

    <button
        data-tippy-content="{{ __('corpus.work_expression.source_document') }}"
        x-bind:class="showSource ? 'border-primary text-primary' : 'border-transparent'"
        class="tippy h-8 w-8 hover:text-primary flex items-center justify-center border-t-2"
        @click="showSource = !showSource"
    >
      <x-ui.icon name="file-pdf"/>
    </button>

    @include('pages.corpus.collaborate.annotations.partials.has-note-metadata-toggle')
  </div>
