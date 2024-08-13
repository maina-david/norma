@php use App\Enums\Tasks\TaskPriority; @endphp
<td class="text-center" {!! $attributes !!}>
  <div class="flex justify-center">
    <livewire:tasks.task-priority-switcher :wire:key="Str::random()" :task-id="$row->id" :priority="$row->priority" />
  </div>
</td>
