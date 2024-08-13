<div class="ml-2 flex flex-col whitespace-normal">
  <a href="{{ route('collaborate.tasks.show', ['task' => $row->task->id]) }}"
     class="text-primary font-semibold overflow-hidden">
    {{ $row->task->title }}
  </a>

  <div class="sub-row">
    @if($row->task->due_date)
      <div class="flex">
        <span class="{{ now()->gt($row->task->due_date) ? 'bg-secondary' : 'bg-primary' }} inline-flex rounded-full items-center py-0.5 px-2 text-xs font-medium text-white whitespace-nowrap">
          {{ \Carbon\Carbon::parse($row->task->due_date)->diffForHumans(now(), \Carbon\Carbon::DIFF_RELATIVE_TO_NOW) }}
        </span>
     </div>
    @endif

    <div>
      @if($row->task->complexity_minutes)
        {{ $row->task->complexity_minutes }} {{ __('workflows.task.minutes') }}
      @endif

      @if($row->task->complexity_minutes && $row->task->units)
        /
      @endif

      @if ($row->task->units)
        {{ $row->task->units }} {{ __('workflows.task.units') }}
      @endif
    </div>
  </div>
</div>
