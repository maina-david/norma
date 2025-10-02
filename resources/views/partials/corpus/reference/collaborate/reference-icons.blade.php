@php
  use App\Enums\Corpus\ReferenceType;
  if (!isset($showBulkActions)) {
      $showBulkActions = auth()->user()->can('collaborate.corpus.work-expression.use-bulk-actions');
  }

  $showBulkActions = !($previewing ?? false) && $showBulkActions;
@endphp
<span class="flex-shrink-0 flex items-center space-x-4" id="reference_{{ $reference->id }}_icons">
    @if($reference->type === ReferenceType::citation()->value)
    <x-ui.icon
      size="4"
      name="{{ strlen($reference->summaryDraft?->summary_body ?? '') > 10 || strlen($reference->summary?->summary_body ?? '') > 10 ? 'file-alt' : 'file' }}"
      class="{{ ($reference->summaryDraft ?? false) ? 'text-negative' : ($reference->summary ? 'text-primary' : 'text-norma-gray-200') }}"
    />

    <x-ui.icon
      type="solid"
      size="4"
      name="marker"
      class="{{ $reference->requirementDraft ? 'text-negative' : ($reference->refRequirement ? 'text-primary' : 'text-norma-gray-200') }}"
    />
  @endif

  <x-ui.icon
      name="paperclip"
      size="4"
      class="{{ $reference->linked_parents_count > 0 || $reference->linked_children_count > 0 ? 'text-primary' : 'text-norma-gray-200' }}"
  />

  <x-ui.icon
      name="comments"
      size="4"
      class="{{ $reference->collaborate_comments_count > 0 ? 'text-primary' : 'text-norma-gray-200' }}"
  />

  @if($showBulkActions ?? false)
  <x-ui.input @click.stop="" @change="toggleRef({{ $reference->id }});" type="checkbox" x-bind:value="selectedRefs[{{ $reference->id }}]" name="actions-checkbox-{{ $reference->id }}" data-id="{{ $reference->id }}" class="rounded-full reference_action" />
{{--  <x-ui.input @click.stop="" @change="toggleRef({{ $reference->id }});window.annotations.check({{ $reference->id }})" type="checkbox" x-bind:value="selectedRefs[{{ $reference->id }}]" name="actions-checkbox-{{ $reference->id }}" data-id="{{ $reference->id }}" class="rounded-full reference_action" />--}}
  @endif
</span>
