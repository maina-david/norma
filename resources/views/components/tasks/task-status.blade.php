@if ($changeable)
  <turbo-frame id="tasks-task-status-{{ $task->id }}">
@endif

<div x-data="{ changing: false }" {{ $attributes->merge(['class' => 'flex']) }}>
  <div {!! $changeable ? 'x-show="!changing"' : '' !!} @click="changing = !changing"
       class="px-3 py-0.5 border border-{{ $color }} text-{{ $color }} text-center {{ $changeable ? 'cursor-pointer' : '' }}">
    {{ $title }}
  </div>

  @if ($changeable)
    @can('update', $task)
      <div x-cloak x-show="changing">
        <x-ui.form :action="route('my.tasks.tasks.task-status.update', ['task' => $task->id])" method="patch">
          <div @changed="$el.closest('form').requestSubmit()">
            <x-tasks.status-select :tom-selected='false' name="task_status" :value="$status" />
          </div>
        </x-ui.form>
      </div>
    @endcan
  @endif
</div>

@if ($changeable)
  </turbo-frame>
@endif
