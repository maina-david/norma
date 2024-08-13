@php
use App\Enums\Assess\RiskRating;
use App\Enums\Assess\AssessmentItemType;

$types = collect(AssessmentItemType::forSelector())->filter(fn ($item) => $item['value'] != AssessmentItemType::USER_DEFINED->value)->values()->all();
@endphp

<div class="flex flex-col md:flex-row md:space-x-4">
  <div class="grow">
    <x-ui.input :value="old('component_1', $resource->component_1 ?? '')" name="component_1"
                label="{{ __('assess.assessment_item.component_1') }}" />
  </div>

  <div class="grow">
    <x-ui.input :value="old('component_2', $resource->component_2 ?? '')" name="component_2"
                label="{{ __('assess.assessment_item.component_2') }}" />
  </div>
</div>

<div class="flex flex-col md:flex-row md:space-x-4">
  <div class="grow">
    <x-ui.input :value="old('component_3', $resource->component_3 ?? '')" name="component_3"
                label="{{ __('assess.assessment_item.component_3') }}" />
  </div>

  <div class="grow">
    <x-ui.input :value="old('component_4', $resource->component_4 ?? '')" name="component_4"
                label="{{ __('assess.assessment_item.component_4') }}" />
  </div>
</div>

<div class="flex flex-col md:flex-row md:space-x-4">
  <div class="grow">
    <x-ui.input :value="old('component_5', $resource->component_5 ?? '')" name="component_5"
                label="{{ __('assess.assessment_item.component_5') }}" />
  </div>

  <div class="grow">
    <x-ui.input :value="old('component_6', $resource->component_6 ?? '')" name="component_6"
                label="{{ __('assess.assessment_item.component_6') }}" />
  </div>
</div>

<div class="flex flex-col md:flex-row md:space-x-4">
  <div class="grow">
    <x-ui.input :value="old('component_7', $resource->component_7 ?? '')" name="component_7"
                label="{{ __('assess.assessment_item.component_7') }}" />
  </div>

  <div class="grow">
    <x-ui.input :value="old('component_8', $resource->component_8 ?? '')" name="component_8"
                label="{{ __('assess.assessment_item.component_8') }}" />
  </div>
</div>


<div x-data="{
    updateStartDueDate: function(val) {
        var months = {};
        months[{{ RiskRating::high()->value }}] = {{ config('assess.default_start_due_offset.' . RiskRating::high()->value) }};
        months[{{ RiskRating::medium()->value }}] = {{ config('assess.default_start_due_offset.' . RiskRating::medium()->value) }};
        months[{{ RiskRating::low()->value }}] = {{ config('assess.default_start_due_offset.' . RiskRating::low()->value) }};
        months[{{ RiskRating::notRated()->value }}] = {{ config('assess.default_start_due_offset.' . RiskRating::notRated()->value) }};
        this.$refs.startDueInput.value = months[val];
    }
}" class="flex flex-col md:flex-row md:space-x-4">
  <div class="w-full md:w-1/3">
    <x-assess.risk-rating-selector
                                   :value="old('risk_rating', $resource->risk_rating ?? null)"
                                   name="risk_rating"
                                   label="{{ __('assess.assessment_item.risk_level') }}"
                                   @change="updateStartDueDate($el.value);" />
  </div>

  <div class="w-full md:w-1/3">
    @php
      $frequencyValue = old('frequency', isset($resource) ? $resource->frequency ?? '' : config('assess.default_assessment_item_frequency'));
    @endphp
    <x-ui.input :value="$frequencyValue" name="frequency"
                label="{{ __('assess.assessment_item.frequency') }}" />
  </div>
  <div class=" w-full md:w-1/3">
    @php
      $startDueValue = old('start_due_offset', isset($resource) ? $resource->start_due_offset ?? '' : config('assess.default_start_due_offset.' . RiskRating::notRated()->value));
    @endphp
    <x-ui.input x-ref="startDueInput" :value="$startDueValue"
                name="start_due_offset"
                label="{{ __('assess.assessment_item.start_due_offset') }}" />
  </div>
</div>


<div>
  <x-ui.input
    :value="old('type', $resource->type ?? AssessmentItemType::LEGACY->value)"
    type="select"
    :options="$types"
    name="type"
    label="{{ __('tasks.type') }}"
  />
</div>


<div class="flex flex-col md:flex-row md:space-x-4">
  <div class="w-full">
    <x-ontology.legal-domain-selector
        data-allow-empty
        name="legal_domain_id"
        :value="old('legal_domain_id', $resource->legal_domain_id ?? null)"
        label="{{ __('assess.assessment_item.legacy_category') }}"
    />
  </div>

</div>

<div class="flex flex-col md:flex-row md:space-x-4">

  <div class="w-full">
    <x-ontology.category-selector
                                  multiple
                                  :value="old(
                                      'categories',
                                      isset($resource)
                                          ? $resource->assessmentItemCategories->map->category_id->toArray()
                                          : null,
                                  )"
                                  name="categories[]"
                                  label="{{ __('assess.assessment_item.category') }}" />
  </div>
</div>



<x-slot name="footer">
  <div></div>
  <div>
    <x-ui.back-button :fallback="route('collaborate.assessment-items.index')" />
    <x-ui.button type="submit" theme="primary">{{ __('actions.save') }}</x-ui.button>
  </div>
</x-slot>
