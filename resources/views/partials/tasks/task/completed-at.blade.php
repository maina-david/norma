@php
use App\Enums\Tasks\TaskStatus;
@endphp

<div class="text-libryo-gray-800">
  @if ($task->completed_at)
    <x-ui.timestamp :timestamp="$task->completed_at" class="text-libryo-gray-800 text-bold" />
  @elseif (TaskStatus::done()->is($task->task_status))
    <span class="text-libryo-gray-300 italic">{{ __('tasks.not_logged') }}</span>
  @else
    <span class="text-libryo-gray-300 italic">{{ __('tasks.not_completed') }}</span>
  @endif
</div>
