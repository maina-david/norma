<div class="flex items-center mr-3 whitespace-nowrap">
  <div class="shrink-0">
    <img class="h-12 w-12 rounded-full mr-2" src="{{ $row->profile_photo_url }}" alt="{{ $row->fname }}">
  </div>

  <div>
    <div class="relative flex items-center">
      <div>{{ $row->full_name }}</div>

      <div class="{{ $row->active ? 'bg-green-500' : 'bg-red-500' }} px-2 rounded-full ml-2 text-white text-xs">
        {{ $row->active ? __('collaborators.active') : __('collaborators.inactive') }}
      </div>
    </div>

    <div class="sub-row">
      <div>{{ __('collaborators.nationality') }}: {{ $row->profile->nationality }}</div>
      <div>{{ __('collaborators.current_residency') }}: {{ $row->profile->current_residency }}</div>
    </div>

    <x-ui.rating-line :value="$row->score" class="ml-2 h-3 w-3" />
  </div>
</div>
