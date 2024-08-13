@php use App\Models\Assess\AssessmentItem; @endphp
@php use App\Models\Ontology\Category; @endphp
@php use App\Models\Compilation\ContextQuestion; @endphp
@php use App\Models\Ontology\LegalDomain; @endphp
@php use App\Models\Geonames\Location; @endphp
@php use App\Models\Ontology\Tag; @endphp
@props(['permission', 'relation', 'label', 'reference', 'withDelete', 'expression'])
@php
  $map = [
      'assessmentItems' => AssessmentItem::class,
      'categories' => Category::class,
      'contextQuestions' => ContextQuestion::class,
      'legalDomains' => LegalDomain::class,
      'locations' => Location::class,
      'tags' => Tag::class,
  ];
  $mapLabels = [
      'assessmentItems' => 'description',
      'categories' => 'display_label',
      'contextQuestions' => 'question',
      'legalDomains' => 'title',
      'locations' => 'title',
      'tags' => 'title',
  ];

  $related = collect();
  if (isset($map[$relation])) {
      $related = $map[$relation]::whereHas('references', fn($builder) => $builder->where('work_id', $expression->work_id));

      if ($related->hasNamedScope('active')) {
          $related->active();
      }

      $related = $related->get();
  }
@endphp

@can("collaborate.corpus.work-expression.attach-{$permission}")
  <x-ui.tab-content class="mb-12" :name="$relation">
    <x-ui.form method="POST"
               :action="route('collaborate.corpus.references.meta.store', [
                   'expression' => $expression->id,
                   'reference' => $reference->id,
                   'relation' => $relation,
               ])">
      @if ($reference->id === 'bulk')
        <template x-for="selRef in Object.keys(selectedRefs)" :key="selRef">
          <input type="hidden" x-bind:name="'reference_' + selRef" value="true">
        </template>
      @endif

      <div class="flex justify-between items-center my-3">
        <div class="font-light text-lg">{{ __('actions.add') }} {{ $label }}</div>
        <div class="flex items-center space-x-4">
          <x-ui.button type="submit" styling="outline" theme="primary">
            {{ __('corpus.reference.add_draft_changes') }}
          </x-ui.button>

          @if ($withDelete)
            @if ($reference->id === 'bulk')
              <x-ui.confirm-button
                                   method="DELETE"
                                   :route="route('collaborate.corpus.references.meta.store', [
                                       'expression' => $expression->id,
                                       'reference' => $reference->id,
                                       'relation' => $relation,
                                   ])"
                                   :label="__('actions.delete_all')"
                                   :confirmation="__('actions.delete_confirmation')"
                                   button-theme="secondary"
                                   styling="outline">
                <x-slot:formBody>
                  <input name="_delete_target" value="all" type="hidden">
                  <template x-for="selRef in Object.keys(selectedRefs)" :key="selRef">
                    <input type="hidden" x-bind:name="'reference_' + selRef" value="true">
                  </template>
                </x-slot:formBody>
                <span data-tippy-content="{{ __('corpus.reference.delete_all_meta_tooltip') }}" class="tippy">
                  {{ __('actions.delete_all') }}
                </span>
              </x-ui.confirm-button>
            @endif

            <x-ui.button type="submit" styling="outline" theme="secondary" name="_method" value="DELETE">
              {{ __('actions.delete') }}
            </x-ui.button>
          @endif

        </div>
      </div>

      {{ $slot }}
    </x-ui.form>

    <div class="mt-4 pt-2 text-sm overflow-y-auto custom-scroll" style="max-height: 20vh">
      @foreach ($related as $item)
        <div class="py-1">
          <x-ui.form method="POST"
                     :action="route('collaborate.corpus.references.meta.store', [
                         'expression' => $expression->id,
                         'reference' => $reference->id,
                         'relation' => $relation,
                     ])">
            @if ($reference->id === 'bulk')
              <template x-for="(val,selRef) in selectedRefs" :key="selRef">
                <input type="hidden" x-bind:name="'reference_' + selRef" x-bind:value="val">
              </template>
            @endif
            <input type="hidden" name="related[]" value="{{ $item->id }}">
            <button type="submit">
              <x-ui.badge>{{ $item->{$mapLabels[$relation]} }}</x-ui.badge>
            </button>
          </x-ui.form>
        </div>
      @endforeach
    </div>

    @can("collaborate.corpus.work-expression.apply-{$permission}")
      @if ($reference->id === 'bulk')
        <div class="flex justify-between mt-8">
          <div>
            <x-ui.form method="POST"
                       :action="route('collaborate.corpus.references.meta-drafts.bulk.apply', [
                           'expression' => $expression->id,
                           'relation' => $relation,
                       ])">
              <template x-for="selRef in Object.keys(selectedRefs)" :key="selRef">
                <input type="hidden" x-bind:name="'reference_' + selRef" value="true">
              </template>
              <x-ui.button type="submit" styling="outline" theme="primary"
                           data-tippy-content="{{ __('corpus.reference.apply_changes_tooltip') }}" class="tippy">
                {{ __('corpus.reference.apply_changes') }}
              </x-ui.button>
            </x-ui.form>
          </div>
          <div>
            <x-ui.confirm-button
                                 method="DELETE"
                                 :route="route('collaborate.corpus.references.meta-drafts.bulk.destroy', [
                                     'expression' => $expression->id,
                                     'relation' => $relation,
                                 ])"
                                 :label="__('corpus.reference.undo_changes')"
                                 :confirmation="__('corpus.reference.undo_changes_confirmation')"
                                 button-theme="secondary"
                                 styling="outline">
              <x-slot:formBody>
                <input name="_delete_target" value="all" type="hidden">
                <template x-for="selRef in Object.keys(selectedRefs)" :key="selRef">
                  <input type="hidden" x-bind:name="'reference_' + selRef" value="true">
                </template>
              </x-slot:formBody>
              <span data-tippy-content="{{ __('corpus.reference.undo_changes_tooltip') }}" class="tippy">
                {{ __('corpus.reference.undo_changes') }}
              </span>
            </x-ui.confirm-button>
          </div>
        </div>
      @endif
    @endcan
  </x-ui.tab-content>
@endcan
