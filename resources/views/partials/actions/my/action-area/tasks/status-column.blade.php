<td class="text-center rounded-tr-md pr-4" {!! $attributes !!}>
  <div class="flex justify-center">
    <livewire:tasks.task-status-switcher :wire:key="Str::random()" :task-id="$row->id" :status="$row->task_status" />
  </div>
</td>
