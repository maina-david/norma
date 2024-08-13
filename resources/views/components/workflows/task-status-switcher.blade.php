@php
use Illuminate\Support\Facades\Auth;
use App\Enums\Workflows\TaskStatus;

$statuses = $task->availableStatuses();
@endphp


@if(empty($statuses))
<div class="inline-flex shadow-sm rounded-md">
  <x-workflows.tasks.task-status-badge :status="$task->task_status" />
</div>
@else

  <div class="flex flex-col items-end">
    <div class="inline-flex">
      @foreach($statuses as $status)
        <x-ui.form method="post" :action="route('collaborate.tasks.status', ['task' => $task->id, 'status' => $status->value])" @submit="if (reload !== undefined) {reload = true;}">
          <x-ui.button
              styling="{{ $status->is($task->task_status) ? 'default' : 'outline' }}"
              theme="{{ $status->is($task->task_status) ? 'primary' : 'gray' }}"
              type="{{ $status->is($task->task_status) ? 'link' : 'submit' }}"
              class="{{ $status->is($task->task_status) ? 'border-primary ' : '' }}{{ $loop->first ? 'rounded-r-none' : ($loop->last ? 'rounded-l-none' : 'rounded-r-none rounded-l-none') }}"
          >
            {{ $status->label()  }}
          </x-ui.button>
        </x-ui.form>
      @endforeach
    </div>

    @error('new_task_status')
    <div class="mt-1 text-sm rounded-lg bg-red-300 px-4 py-2 flex items-center space-x-2 font-normal">
      <x-ui.icon name="exclamation-circle" />
      <span>{{ $message }}</span>
    </div>
    @enderror

  </div>

@endif



