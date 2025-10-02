@php
  use App\Enums\Tasks\TaskStatus;
@endphp

@if($canUpdate)
<x-ui.dropdown>
  <x-slot:trigger>
    <div class="text-primary underline cursor-pointer">
      @if ($task->assignee)
        <div class="flex flex-row items-center">
          <x-ui.user-avatar :user="$task->assignee"/>
          <div class="ml-3">{{ $task->assignee->fullName }}</div>
        </div>
      @else
        <div>-</div>
      @endif
    </div>
  </x-slot:trigger>

  <div class="px-4 py-2 w-screen-75 max-w-xs">
    <x-ui.form method="PUT" action="{{ route('my.tasks.tasks.update', ['task' => $task->id]) }}">
      <x-auth.user.my.user-selector
        :value="old('assigned_to_id', $task->assigned_to_id ?? $user->id)"
        name="assigned_to_id"
        :label="__('tasks.assigned_to')"
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
  @if ($task->assignee)
    <div class="flex flex-row items-center">
      <x-ui.user-avatar :user="$task->assignee"/>
      <div class="ml-3 text-norma-gray-500">{{ $task->assignee->fullName }}</div>
    </div>
  @else
    <div>-</div>
  @endif
@endif
