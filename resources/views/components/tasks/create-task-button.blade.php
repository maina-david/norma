@props(['taskableId' => null, 'taskableType' => null, 'normaId' => null, 'redirectTo' => url()->current()])

@php
  use App\Enums\Tasks\TaskPriority;
  use App\Enums\Tasks\TaskStatus;
  use App\Models\Corpus\Reference;
@endphp

<x-ui.dropdown position="left">
  <x-slot:trigger>
    <x-ui.button theme="primary" class="ml-2">
      <x-ui.icon name="plus" size="3" class="mr-1"/>
      {{ __('tasks.create_task') }}
    </x-ui.button>
  </x-slot:trigger>

  <div class="p-4 max-w-md w-screen">
    <div class="font-semibold text-lg">
      {{ __('tasks.create_task') }}
    </div>

    <turbo-frame target="_top">
      <x-ui.form method="POST" action="{{ route('my.tasks.tasks.store') }}">
        @if($taskableId && $taskableType)
          <input type="hidden" name="taskable_type" value="{{ $taskableType }}"/>
          <input type="hidden" name="taskable_id" value="{{ $taskableId }}"/>
        @endif
        @if($normaId)
          <input type="hidden" name="target_norma_id" value="{{ $normaId }}"/>
        @endif

        <input type="hidden" name="task_status" value="{{ TaskStatus::notStarted()->value }}"/>

        <div>
          <x-ui.input
              :value="old('title', $task->title ?? '')"
              name="title"
              label="{{ __('interface.title') }}"
              required
          />

          <x-ui.input
              type="date"
              :value="old('due_on', isset($task) ? ($task->due_on?->format('Y-m-d') ?? '') : '')"
              name="due_on"
              label="{{ __('tasks.due_on') }}"
          />

          <x-tasks.priority-select
              :value="old('priority', $task->priority ?? TaskPriority::medium()->value)"
              :label="__('tasks.priority')"
          />

          <x-ui.input
              type="textarea"
              :value="old('description', $task->description ?? '')"
              name="description"
              label="{{ __('interface.description') }}"
              class="norma-editor-minimal"
          />
        </div>

        @if(!isset($task) && $taskableType === (new Reference())->getMorphClass())
          <div class="mt-4">
            <x-ui.input type="checkbox" :value="old('copy')" name="copy" label="{{ __('tasks.create_copies') }}"/>
          </div>
        @endif

        <div class="flex justify-end mt-4 space-x-4">
          <x-ui.button theme="primary" type="submit" name="save_and_back" value="{{ $redirectTo }}">
            {{ __('actions.save') }}
          </x-ui.button>

          <x-ui.button theme="primary" type="submit">
            {{ __('actions.save_and_edit') }}
          </x-ui.button>
        </div>
      </x-ui.form>
    </turbo-frame>
  </div>
</x-ui.dropdown>

