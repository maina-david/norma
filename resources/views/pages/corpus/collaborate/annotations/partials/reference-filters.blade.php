@php
use App\Enums\Corpus\MetaItemStatus;
$states = collect(MetaItemStatus::cases())->map->value->toArray();
$stateColours = MetaItemStatus::stateColours();
@endphp

<div class="flex items-center justify-between flex-shrink-0 pr-8 bg-white space-x-1">
  <div class="flex items-center px-4">
    <x-corpus.reference.reference-detailed-filter />
  </div>
  <div class="flex justify-end items-center flex-grow">
    <x-corpus.reference.reference-filter-icon
        :expression-id="$expression->id"
        :tooltip="__('nav.assessment_items')"
        filter="assessments"
        icon="check"
        :states="$states"
        :state-colours="$stateColours"
    />

    <x-corpus.reference.reference-filter-icon
        :expression-id="$expression->id"
        :tooltip="__('nav.topics')"
        filter="topics"
        icon="hashtag"
        :states="$states"
        :state-colours="$stateColours"
    />

    <x-corpus.reference.reference-filter-icon
        :expression-id="$expression->id"
        :tooltip="__('nav.context_questions')"
        filter="context"
        icon="question"
        :states="$states"
        :state-colours="$stateColours"
    />

    <x-corpus.reference.reference-filter-icon
        :expression-id="$expression->id"
        :tooltip="__('nav.legal_domains')"
        filter="domains"
        icon="scale-balanced"
        :states="$states"
        :state-colours="$stateColours"
    />

    <x-corpus.reference.reference-filter-icon
        :expression-id="$expression->id"
        :tooltip="__('nav.jurisdictions')"
        filter="jurisdictions"
        icon="location-dot"
        :states="$states"
        :state-colours="$stateColours"
    />

    <x-corpus.reference.reference-filter-icon
        :expression-id="$expression->id"
        :tooltip="__('nav.tags')"
        filter="tags"
        icon="tags"
        :states="$states"
        :state-colours="$stateColours"
    />

    <x-corpus.reference.reference-filter-icon
        :expression-id="$expression->id"
        :tooltip="__('corpus.reference.summaries')"
        filter="summary"
        icon="file"
        :states="$states"
        :state-colours="$stateColours"
    />

    <x-corpus.reference.reference-filter-icon
        :expression-id="$expression->id"
        :tooltip="__('corpus.reference.requirements')"
        filter="requirement"
        icon="marker"
        :states="$states"
        :state-colours="$stateColours"
    />

    <x-corpus.reference.reference-filter-icon
        :task-id="$task->id ?? null"
        :expression-id="$expression->id"
        :tooltip="__('corpus.reference.linked')"
        filter="links"
        icon="paperclip"
    />

    <x-corpus.reference.reference-filter-icon
        :expression-id="$expression->id"
        :tooltip="__('comments.comments')"
        filter="comments"
        icon="comments"
        size="3"
    />

    @if($showBulkActions ?? false)
      <x-ui.input class="ml-3 mt-2 rounded-full" type="checkbox" name="action_check_all" @click="selectAll" x-bind:checked="Object.keys(selectedRefs).length > 0" x-bind:value="Object.keys(selectedRefs).length > 0" />
    @endif
  </div>
</div>
