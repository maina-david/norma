@props(['collaborator'])

<div class="flex items-center mr-3 whitespace-nowrap">
  <div class="shrink-0 overflow-hidden flex">
    <img class="h-12 w-12 rounded-full mr-2" src="{{ $collaborator->profile_photo_url }}"
         alt="{{ $collaborator->fname }}">
  </div>

  <div>
    <div class="flex items-center space-x-1">
      <span>{{ $collaborator->full_name }}</span>
        @if($collaborator->profile->documents->isNotEmpty())
          <x-ui.icon class="text-amber-600" name="triangle-exclamation"/>
        @endif
    </div>

    <div class="sub-row">
      @if ($collaborator->profile->restricted_visa)
        <div>{{ $collaborator->profile->restriction_type }}</div>
        <div class="{{ ($collaborator->week_hours ?? 0) >= ($collaborator->profile->visa_available_hours ?? 1) ? 'text-secondary' : '' }}">
          <span>{{ $collaborator->week_hours ?? 0 }} / </span>
          <span>{{ __('workflows.task_application.hours_per_week', ['hours' => $collaborator->profile->visa_available_hours]) }}</span>
        </div>
        @if ($collaborator->profile->visa)
          <div class="flex items-center justify-between space-x-2 {{ $collaborator->profile->visa->expired ? 'text-secondary' : '' }}">
            <span>{{ __('collaborators.visa_expiry') }}: </span>
            <x-ui.timestamp type="date" :timestamp="$collaborator->profile->visa->valid_to"/>
          </div>
        @endif

      @else
        <span>{{ __('workflows.task_application.not_restricted') }}</span>
      @endif

      @if ($collaborator->profile->identification && $collaborator->profile->identification->valid_to)
        <div class="flex items-center justify-between space-x-2 {{ $collaborator->profile->identification->expired ? 'text-secondary' : '' }}">
          <span>{{ __('collaborators.identification_expiry') }}: </span>
          <x-ui.timestamp type="date" :timestamp="$collaborator->profile->identification->valid_to"/>
        </div>
      @endif

      @if ($collaborator->profile->contract && $collaborator->profile->contract->valid_to)
        <div class="flex items-center justify-between space-x-2 {{ $collaborator->profile->contract->expired ? 'text-secondary' : '' }}">
          <span>{{ __('collaborators.contract_expiry') }}: </span>
          <x-ui.timestamp type="date" :timestamp="$collaborator->profile->contract->valid_to"/>
        </div>
      @endif


    </div>

    <x-ui.rating-line :value="$collaborator->score" class="ml-2 h-3 w-3"/>

  </div>
</div>
