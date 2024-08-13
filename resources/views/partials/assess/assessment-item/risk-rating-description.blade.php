@php
use App\Enums\Assess\RiskRating;
@endphp
<span
      class="tippy rounded-full inline-block mr-1 w-2 h-2 bg-{{ RiskRating::colors()[$assessmentItem->risk_rating] }}"
      data-tippy-content="{{ __('assess.assessment_item.risk_rating.' . $assessmentItem->risk_rating) }}">
</span>
<span>{{ $assessmentItem->toDescription() }}</span>
