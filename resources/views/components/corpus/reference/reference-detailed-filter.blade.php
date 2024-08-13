@php
  use App\Models\Assess\AssessmentItem;use App\Models\Compilation\ContextQuestion;use App\Models\Geonames\Location;$assess = request('assessmentItems', []);
  $selectedAssess = [];
  if (!empty($assess)) {
      $selectedAssess = AssessmentItem::whereKey($assess)
        ->get()
        ->keyBy('id')
        ->map(fn ($item) => $item->description)
        ->toArray();
  }

  $questions = request('questions', []);
  $selectedQuestions = [];

  if (!empty($questions)) {
      $selectedQuestions = ContextQuestion::whereKey($questions)
        ->get()
        ->keyBy('id')
        ->map(fn ($item) => $item->question)
        ->toArray();
  }

  $locations = request('locations', []);
  $selectedLocations = [];

  if (!empty($locations)) {
      $selectedLocations = Location::whereKey($locations)
          ->with(['locationType'])
        ->get()
        ->keyBy('id')
        ->map(fn ($item) => sprintf('%s (%s)', $item->title, $item->locationType ? __('corpus.location_type.' . $item->locationType->adjective_key) : ''))
        ->toArray();
  }

  $topics = request('topics', []);
  $domains = request('domains', []);

  $active = !empty([...$assess, ...$questions, ...$topics, ...$locations, ...$domains]);
@endphp

<x-ui.dropdown>
  <x-slot:trigger>
    <button data-tippy-content="{{ __('interface.filters') }}"
            class="tippy h-10 w-8 hover:text-primary flex items-center justify-center pt-2{{ $active ? ' border-t-2 border-primary text-primary' : '' }}">
      <x-ui.icon name="filter"/>
    </button>
  </x-slot:trigger>

  <div class="px-4 pb-4 max-w-xs w-screen relative overflow-y-auto custom-scroll" style="max-height:50vh;">
    <form method="GET">

      <div class="mb-6 pt-4 flex justify-end">
        <x-ui.button type="submit" styling="outline" theme="primary">
          {{ __('interface.apply_filters') }}
        </x-ui.button>
      </div>

      <x-assess.assessment-item-selector multiple :options="$selectedAssess" :value="$assess" name="assessmentItemsWithDrafts[]"
                                         label="{{ __('nav.assessment_items') }}" placeholder=""/>

      <x-compilation.context-question.context-question-selector multiple :options="$selectedQuestions"
                                                                :value="$questions" name="questionsWithDrafts[]"
                                                                label="{{ __('nav.context_questions') }}"
                                                                placeholder=""/>

      <x-geonames.location.location-selector multiple :value="$locations" name="locationsWithDrafts[]"
                                             label="{{ __('nav.jurisdictions') }}" :options="$selectedLocations"/>

      <x-ontology.legal-domain-selector multiple :value="$domains" annotations name="domainsWithDrafts[]"
                                        label="{{ __('nav.legal_domains') }}"/>

      <x-ontology.category-selector multiple :value="$topics" name="topicsWithDrafts[]" label="{{ __('nav.topics') }}"
                                    placeholder=""/>
    </form>

    <form method="GET" class="absolute left-0 top-0 px-4 pt-3">
      <div class="mt-1 flex justify-end">
        <x-ui.button type="submit" styling="outline" theme="negative">
          {{ __('actions.clear') }}
        </x-ui.button>
      </div>
    </form>
  </div>
</x-ui.dropdown>
