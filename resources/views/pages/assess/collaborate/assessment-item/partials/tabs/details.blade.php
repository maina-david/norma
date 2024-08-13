@php
  use App\Enums\Assess\RiskRating;
  use App\Enums\Assess\AssessmentItemType;
@endphp
<div>
  <x-ui.show-field :label="__('assess.assessment_item.description')" :value="$resource->description"/>

  <x-ui.show-field :label="__('tasks.type')"
                   :value="AssessmentItemType::tryFrom($resource->type)?->label() ?? ''"/>

  <x-ui.show-field :label="__('assess.assessment_item.frequency')"
                   :value="$resource->frequency"/>

  <x-ui.show-field :label="__('assess.assessment_item.risk_level')"
                   :value="RiskRating::lang()[$resource->risk_rating]"/>

  <x-ui.show-field :label="__('assess.assessment_item.legacy_category')"
                   :value="$domains[$resource->legal_domain_id] ?? '-'"/>

  <x-ui.show-field :label="__('assess.assessment_item.category')">
    @foreach ($resource->assessmentItemCategories as $cat)
      <div>{{ $categories[$cat->category_id] ?? '' }}</div>
    @endforeach
  </x-ui.show-field>


  <x-ui.show-field :label="__('collaborators.group.created_at')">
    <x-ui.timestamp :timestamp="$resource->created_at"/>
  </x-ui.show-field>
</div>
