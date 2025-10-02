@php
  use App\Enums\Tasks\TaskStatus;
  $task->load('norma.organisation');
  $organisation = $task->norma ?? null;
  $canChangeStatus = ($task->norma ?? false) && $user->hasNormaAccess($task->norma);
@endphp

@if($canChangeStatus)
<x-ui.dropdown>
  <x-slot:trigger>
    <div class="text-primary underline cursor-pointer">
      <span class="text-primary">{{ TaskStatus::fromValue($task->task_status)->label() }}</span>
    </div>
  </x-slot:trigger>

  <div class="px-4 py-2 w-screen-75 max-w-xs">
    <x-ui.form method="PUT" action="{{ route('my.tasks.tasks.update', ['task' => $task->id]) }}">
      <x-tasks.status-select
        name="task_status"
        :value="old('task_status', $task->task_status ?? TaskStatus::notStarted()->value)"
        :label="__('tasks.status')"
        :allow-empty="false"
      />

      <div class="mt-4">
        <x-ui.button theme="primary" type="submit">
          {{ __('actions.save') }}
        </x-ui.button>
      </div>
    </x-ui.form>
  </div>
</x-ui.dropdown>
@else
  <span class="text-norma-gray-500">
    {{ TaskStatus::fromValue($task->task_status)->label() }}
  </span>
@endif
