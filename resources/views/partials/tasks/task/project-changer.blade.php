@if($canUpdate)
<x-ui.dropdown>
  <x-slot:trigger>
    <div class="text-primary underline cursor-pointer">
      {{ $task->project->title ?? '-' }}
    </div>
  </x-slot:trigger>

  <div class="px-4 py-2 w-screen-75 max-w-xs">
    <x-ui.form method="PUT" action="{{ route('my.tasks.tasks.update', ['task' => $task->id]) }}">

      <x-tasks.task-project.project-selector
        name="task_project_id"
        :value="old('task_project_id', $task->task_project_id ?? null)"
        :label="__('tasks.project')"
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
  {{ $task->project->title ?? '-' }}
</span>
@endif
