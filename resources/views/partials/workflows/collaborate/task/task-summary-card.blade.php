<a href="{{ route('collaborate.tasks.show', $task->id) }}" class="group relative flex flex-col w-full px-6 py-6 border-b border-libryo-gray-200 hover:bg-libryo-gray-100 overflow-x-hidden" target="_top">

  <div class="absolute top-1 left-2 group-hover:block" :class="selected[{{ $task->id }}] ? '' : 'hidden'">
    <x-ui.input x-model="selected[{{ $task->id }}]" type="checkbox" name="actions-checkbox-{{ $task->id }}" class="rounded-full" />
  </div>

  <span class="w-full mb-2">
    <span class="inline-flex rounded-full items-center text-sm font-medium whitespace-nowrap">#{{ $task->id }}</span>
    <span class="text-sm text-primary font-semibold">
      <span>{{ $task->title }}</span>
    </span>
  </span>

  <span class="flex justify-between items-center">

    <span class="flex flex-wrap items-center space-x-2">
      <x-workflows.tasks.task-status-badge :status="$task->task_status" />
      <x-workflows.tasks.task-priority-badge :priority="$task->priority" />

      @if($task->outstanding_payment_requests_count > 0)
      <x-ui.icon name="file-invoice-dollar" class="text-negative tippy" data-tippy-content="{{ __('payments.payment_request.outstanding') }}" size="6" />
      @endif
      @if($task->paid_payment_requests_count > 0)
      <x-ui.icon name="sack-dollar" class="text-primary tippy" data-tippy-content="{{ __('payments.payment_request.paid') }}" />
      @endif
    </span>

    <div class="flex-shrink-0 h-8">
    @if($task->assignee)
      <x-ui.user-avatar :user="$task->assignee" />
    @endif
    </div>

  </span>
  @if($task->due_date && !\App\Enums\Workflows\TaskStatus::done()->is($task->task_status) && !\App\Enums\Workflows\TaskStatus::archive()->is($task->task_status))
   <span class="flex">
      <span class="{{ now()->gt($task->due_date) ? 'bg-secondary' : 'bg-primary' }} inline-flex rounded-full items-center py-0.5 px-2 text-xs font-medium text-white whitespace-nowrap">
        {{ \Carbon\Carbon::parse($task->due_date)->diffForHumans(now(), \Carbon\Carbon::DIFF_RELATIVE_TO_NOW) }}
      </span>
   </span>
  @endif
</a>
