<div>
  <div class="flex items-center justify-start my-1">
    @if ($task->assignee)
      <x-ui.user-avatar :user="$task->assignee" />
    @endif
    <div class="ml-2">
      <x-tasks.task.task-link :task="$task" :single-mode="$singleMode ?? true" />
    </div>
  </div>
  <div class="flex items-baseline justify-between mt-3">
    <div>
      <x-tasks.task-status :status="$task->task_status" />
    </div>
    @if ($task->due_on)
      <div class="text-norma-gray-400 ">
        <x-ui.timestamp :timestamp="$task->due_on->format('Y-m-d')" />
      </div>
    @endif
  </div>

</div>
