@php
  $show = request('show')
@endphp
<div
  class="relative bg-libryo-gray-100 rounded-lg pt-2 pb-1 px-4 reference-attach"
  x-data="{
    clearAndLoad: function (el) {
      el.tomselect.clear();el.tomselect.clearOptions();el.tomselect.load('')
    }
  }"
>
  @if(!($noClose ?? false))
    <button class="p-1 rounded-full text-libryo-gray-700 absolute -right-4 -top-4 bg-white" @click="$event.target.closest('.reference-attach').remove()">
      <x-ui.icon name="times-circle" size="8" />
    </button>
  @endif

  <x-ui.tabs :active="$relation">
    <x-slot name="nav">
        @can('collaborate.corpus.work-expression.attach-assessment-items')
          <x-ui.tab-nav name="assessmentItems">{{ __('nav.assessment_items') }}</x-ui.tab-nav>
        @endcan

        @can('collaborate.corpus.work-expression.attach-categories')
          <x-ui.tab-nav name="categories">{{ __('nav.topics') }}</x-ui.tab-nav>
        @endcan

        @can('collaborate.corpus.work-expression.attach-context-questions')
          <x-ui.tab-nav name="contextQuestions">{{ __('nav.context_questions') }}</x-ui.tab-nav>
        @endcan

        @can('collaborate.corpus.work-expression.attach-legal-domains')
          <x-ui.tab-nav name="legalDomains">{{ __('nav.legal_domains') }}</x-ui.tab-nav>
        @endcan

        @can('collaborate.corpus.work-expression.attach-locations')
          <x-ui.tab-nav name="locations">{{ __('nav.jurisdictions') }}</x-ui.tab-nav>
        @endcan

        @can('collaborate.corpus.work-expression.attach-tags')
          <x-ui.tab-nav name="tags">{{ __('nav.tags') }}</x-ui.tab-nav>
        @endcan

        @if($isBulkAction ?? false)
          @canany(['collaborate.corpus.reference.requirement.apply', 'collaborate.corpus.reference.requirement.delete'])
            <x-ui.tab-nav name="requirements">{{ __('corpus.reference.requirements') }}</x-ui.tab-nav>
          @endcanany

          @canany(['collaborate.corpus.reference.apply', 'collaborate.corpus.reference.delete', 'collaborate.corpus.reference.request-update'])
            <x-ui.tab-nav name="references">{{ __('corpus.reference.references') }}</x-ui.tab-nav>
          @endcanany

          @canany(['collaborate.requirements.summary.draft.create', 'collaborate.requirements.summary.draft.apply'])
            <x-ui.tab-nav name="summaries">{{ __('corpus.summary.index_title') }}</x-ui.tab-nav>
          @endcanany
        @endif

    </x-slot>

    <x-corpus.reference.add-meta-form :expression="$expression" :with-delete="$isBulkAction ?? false" :reference="$reference" permission="assessment-items" relation="assessmentItems" :label="__('nav.assessment_items')">
      <div x-data="{categories: {{ json_encode($categories ?? []) }}}" x-init="function () { if (this.categories.length > 0) { setTimeout(function () { clearAndLoad($refs.assess_{{ $reference->id }}_selector) }, 200) }  }">
        <x-ontology.category-selector
          x-model="categories"
          name="categories"
          label=""
          :value="null"
          multiple
          @change="clearAndLoad($refs.assess_{{ $reference->id }}_selector)"
        />
        <x-assess.assessment-item-selector
          x-ref="assess_{{ $reference->id }}_selector"
          name="related[]"
          multiple
          x-bind:data-load="'{{ route('collaborate.assessment-items.index.json') }}?location_id={{ $location }}&categories=' + categories.join(',')"
          data-load-search-field="no-filter"
        />
      </div>
    </x-corpus.reference.add-meta-form>

    <x-corpus.reference.add-meta-form :expression="$expression" :with-delete="$isBulkAction ?? false" :reference="$reference" permission="categories" relation="categories" :label="__('nav.topics')">
      <x-ontology.category-selector
        name="related[]"
        label=""
        :value="null"
        multiple
      />
    </x-corpus.reference.add-meta-form>

    <x-corpus.reference.add-meta-form :expression="$expression" :with-delete="$isBulkAction ?? false" :reference="$reference" permission="context-questions" relation="contextQuestions" :label="__('nav.context_questions')">
      <div x-data="{categories: {{ json_encode($categories ?? []) }}}" x-init="function () { if (this.categories.length > 0) { setTimeout(function () { clearAndLoad($refs.context_{{ $reference->id }}_selector) }, 200) }  }">
        <x-ontology.category-selector
          x-model="categories"
          name="categories"
          label=""
          :value="null"
          multiple
          @change="clearAndLoad($refs.context_{{ $reference->id }}_selector)"
        />
        <x-compilation.context-question.context-question-selector
          x-ref="context_{{ $reference->id }}_selector"
          name="related[]"
          multiple
          data-load-start-searching="0"
          label=""
          x-bind:data-load="'{{ route('collaborate.context-questions.json.index') }}?location_id={{ $location }}&categories=' + categories.join(',')"
          data-load-search-field="no-filter"
        />
      </div>
    </x-corpus.reference.add-meta-form>

    <x-corpus.reference.add-meta-form :expression="$expression" :with-delete="$isBulkAction ?? false" :reference="$reference" permission="legal-domains" relation="legalDomains" :label="__('nav.legal_domains')">
      <x-ontology.legal-domain-selector
        name="related[]"
        multiple
        value=""
        label=""
        :placeholder="__('nav.legal_domains')"
        annotations
      />
    </x-corpus.reference.add-meta-form>

    <x-corpus.reference.add-meta-form :expression="$expression" :with-delete="$isBulkAction ?? false" :reference="$reference" permission="locations" relation="locations" :label="__('nav.jurisdictions')">
      <x-geonames.location.location-selector
        :route="route('collaborate.locations.json.index')"
        name="related[]"
        multiple
        :placeholder="__('nav.jurisdictions')"
        label=""
      />
    </x-corpus.reference.add-meta-form>

    <x-corpus.reference.add-meta-form :expression="$expression" :with-delete="$isBulkAction ?? false" :reference="$reference" permission="tags" relation="tags" :label="__('nav.tags')">
      <x-ontology.tag.tag-selector name="related[]" class="mt-4" />
    </x-corpus.reference.add-meta-form>


    @if($isBulkAction ?? false)
      @canany(['collaborate.corpus.reference.requirement.create', 'collaborate.corpus.reference.requirement.apply', 'collaborate.corpus.reference.requirement.delete'])
        <x-ui.tab-content name="requirements" x-data="{selectedRefs: typeof selectedRefs === 'undefined' ? {} : selectedRefs}">
          <div class="flex items-center justify-between py-6">
            <div class="flex items-center space-x-4">
              @can('collaborate.corpus.reference.requirement.create')
                <x-corpus.reference.reference-bulk-action :expression-id="$expression->id" label-suffix="request_requirement" action="request-requirement" />
              @endcan
              @can('collaborate.corpus.reference.requirement.apply')
                <x-corpus.reference.reference-bulk-action check-first :expression-id="$expression->id" label-suffix="apply_requirement_drafts" action="apply-requirement-drafts" />
              @endcan
            </div>

            @can('collaborate.corpus.reference.requirement.delete')
              <div class="flex justify-center space-x-4">
                <x-corpus.reference.reference-bulk-action :expression-id="$expression->id" button-theme="secondary" danger label-suffix="delete_requirement" action="delete-requirement" />
              </div>
            @endcan
          </div>
        </x-ui.tab-content>
      @endcanany

      @canany(['collaborate.corpus.reference.apply', 'collaborate.corpus.reference.delete', 'collaborate.corpus.reference.request-update'])
        <x-ui.tab-content name="references" x-data="{selectedRefs: typeof selectedRefs === 'undefined' ? {} : selectedRefs}">
          <div class="flex items-center justify-between py-6">
            <div class="flex items-center space-x-4">
              @can('collaborate.corpus.reference.apply')
                <x-corpus.reference.reference-bulk-action :expression-id="$expression->id" label-suffix="apply_drafts" action="apply-drafts" />
              @endcan

              @can('collaborate.corpus.reference.request-update')
                <x-corpus.reference.reference-bulk-action :expression-id="$expression->id" label-suffix="request_changes" action="request-changes" />
              @endcan
            </div>

            @can('collaborate.corpus.reference.delete')
              <x-corpus.reference.reference-bulk-action :expression-id="$expression->id" button-theme="secondary" danger label-suffix="delete" action="delete-references" />
            @endcan
          </div>
        </x-ui.tab-content>
      @endcanany

      @canany(['collaborate.requirements.summary.draft.create', 'collaborate.requirements.summary.draft.apply'])
        <x-ui.tab-content name="summaries" x-data="{selectedRefs: typeof selectedRefs === 'undefined' ? {} : selectedRefs}">
          <div class="flex items-center justify-between py-6">
            <div class="flex items-center space-x-4">
              @can('collaborate.requirements.summary.draft.create')
                <x-corpus.reference.reference-bulk-action :expression-id="$expression->id" label-suffix="apply_summary_drafts" action="apply-summary-drafts" />
              @endcan

              @can('collaborate.requirements.summary.draft.apply')
                <x-corpus.reference.reference-bulk-action :expression-id="$expression->id" label-suffix="request_summary_changes" action="request-summary-changes" />
              @endcan
            </div>
          </div>
        </x-ui.tab-content>
      @endcanany
    @endif

  </x-ui.tabs>
</div>


@if($reference->id === 'bulk')
  <div
      class="meta-loader"
      x-init="function () {
        this.loading = true;
        var that = this;
        setTimeout(function () {
          that.loading = false;
          document.querySelectorAll('.meta-loader').forEach(function (loader) {
            loader.remove();
          });
       }, 2000);
      }"
  ></div>
@endif
