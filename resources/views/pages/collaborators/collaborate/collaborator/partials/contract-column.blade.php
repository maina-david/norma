@php use App\Enums\Collaborators\ContractType; @endphp
<div class="flex flex-col justify-center mr-3 whitespace-nowrap">
  <div>{{ ContractType::lang()[$row->profile->contract->subtype ?? '-'] ?? '-' }}</div>

  <div class="sub-row">
    @if ($row->profile->restricted_visa)
      <div>{{ $row->profile->restriction_type }}</div>
      <div class="{{ ($row->week_hours ?? 0) >= ($row->profile->visa_available_hours ?? 1) ? 'text-secondary' : '' }}">
        <span>{{ $row->week_hours ?? 0 }} / </span>
        <span>{{ __('workflows.task_application.hours_per_week', ['hours' => $row->profile->visa_available_hours]) }}</span>
      </div>
    @else
      <span>{{ __('workflows.task_application.not_restricted') }}</span>
    @endif
  </div>
</div>
