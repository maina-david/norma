<div>
  <div class="whitespace-nowrap">
    <span>{{ $row->name }}</span>
    @if($row->is_parent)
      <x-ui.badge small>{{ __('workflows.task_type.parent') }}</x-ui.badge>
    @endif
  </div>
  <div class="whitespace-nowrap mt-1">
    {{ $row->course_link }}
  </div>
</div>
