<td class="text-center" {!! $attributes !!}>
    <span class="flex justify-center">
      <livewire:tasks.task-assignee-switcher :wire:key="Str::random()" :task-id="$row->id" :assignee="$row->assignee" />
    </span>
</td>
