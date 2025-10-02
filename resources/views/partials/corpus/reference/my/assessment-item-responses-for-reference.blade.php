@php
  use App\Enums\Assess\RiskRating;
@endphp

<div>
  @foreach($reference->assessmentItems as $item)
    <div class="flex items-center border-b border-norma-gray-300 py-4">
      <div class="px-4 flex-shrink-0">
        <span
          class="tippy rounded-full inline-block mr-1 w-3 h-3 bg-{{ RiskRating::colors()[$item->risk_rating] }}"
          data-tippy-content="{{ __('assess.assessment_item.risk_rating.' . $item->risk_rating) }}">
        </span>
      </div>
      <div class="flex-grow">
        <div class="text-primary font-semibold text-sm">
          <a href="{{ route('my.assess.assessment-item-responses.show', ['aiResponse' => $item->assessmentResponses->first()->hash_id]) }}">
            {{ $item->description }}
          </a>
        </div>
        <div>
          @include('partials.assess.assessment-item.domain-breadcrumbs', ['assessmentItem' => $item])
        </div>
      </div>
      @if($item->assessmentResponses->first()->next_due_at)
        <div class="px-4 flex-shrink-0 text-norma-gray-600 text-xs">
          <span class="mr-1">{{ __('workflows.task.due') }}</span>
          <span>{{ $item->assessmentResponses->first()->next_due_at?->format('d M Y') }}</span>
        </div>
      @endif
    </div>
  @endforeach
</div>
