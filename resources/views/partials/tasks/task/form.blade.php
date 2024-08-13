@php
use App\Enums\Tasks\TaskPriority;
use App\Enums\Tasks\TaskStatus;
@endphp

@isset($taskableType)
  <input type="hidden" name="taskable_type" value="{{ $taskableType }}" />
  <input type="hidden" name="taskable_id" value="{{ $taskableId }}" />
@endisset


<div x-data="{ addingReminder: false }" class="sm:rounded-lg">
  <div>
    <div>
      <x-ui.input :value="old('title', $task->title ?? '')" name="title" label="{{ __('interface.title') }}"
                  required />
    </div>
  </div>
  <div class="border-t border-libryo-gray-200 py-5 ">
    <dl class="grid grid-cols-1 gap-x-4 gap-y-4 sm:grid-cols-3">
      <div class="sm:col-span-3">
        <dd class="mt-1 text-sm text-libryo-gray-900">
          <x-ui.input type="textarea" :value="old('description', $task->description ?? '')" name="description"
                      label="{{ __('interface.description') }}" class="libryo-editor-minimal" />
        </dd>
      </div>
      <div class="sm:col-span-1">
        <dd class="mt-1 text-sm text-libryo-gray-900">
          <x-tasks.status-select :value="old('task_status', $task->task_status ?? TaskStatus::notStarted()->value)"
                                 name="task_status"
                                 :label="__('tasks.status')"
                                 :allow-empty="false" />
        </dd>
      </div>
      <div class="sm:col-span-1">
        <dd class="mt-1 text-sm text-libryo-gray-900">
          <x-ui.input type="date" :value="old('due_on', isset($task) ? ($task->due_on?->format('Y-m-d') ?? '') : '')"
                      name="due_on"
                      label="{{ __('tasks.due_on') }}" />
        </dd>
      </div>
      <div class="sm:col-span-1">
        <dd class="mt-1 text-sm text-libryo-gray-900">
          <x-tasks.priority-select :value="old('priority', $task->priority ?? TaskPriority::medium()->value)"
                                   :label="__('tasks.priority')" />
        </dd>
      </div>
      <div class="sm:col-span-1">

        <dd class="mt-1 text-sm text-libryo-gray-900">

          <x-auth.user.my.user-selector :value="old('assigned_to_id', $task->assigned_to_id ?? $user->id)"
                                        name="assigned_to_id"
                                        :label="__('tasks.assigned_to')" />
        </dd>
      </div>
      <div class="sm:col-span-1">
        <dd class="mt-1 text-sm text-libryo-gray-900">
          <x-auth.user.my.user-selector :value="old('followers', isset($task) ? ($task->watchers?->pluck('id')->toArray() ?? []) : [])"
                                        name="followers[]"
                                        multiple
                                        :label="__('tasks.followers')" />
        </dd>
      </div>
      <div class="sm:col-span-1">
        <dd class="mt-1 text-sm text-libryo-gray-900">
          <x-tasks.task-project.project-selector name="task_project_id"
                                                 :value="old('task_project_id', $task->task_project_id ?? null)"
                                                 :label="__('tasks.project')" />
        </dd>
      </div>

    </dl>

    @if ($isCreateForm ?? false)
      <div class="mt-10 mb-3">
        <x-ui.input x-model="addingReminder" type="checkbox"
                    :value="old('reminder', false)"
                    name="reminder"
                    label="{{ __('tasks.also_add_reminder') }}" />
      </div>

      <div x-show="addingReminder">
        @include('partials.notify.reminder.form', [
            'isCreateForm' => true,
            'remindableType' => 'task',
        ])
      </div>
    @endif
  </div>
</div>


<x-slot name="footer">
  <div></div>
  <div>
    @if (!isset($isCreateForm) || !$isCreateForm)
      <x-ui.button data-turbo-frame="_top" :href="route('my.tasks.tasks.show', ['task' => $task->hash_id])"
                   type="link" theme="tertiary"
                   styling="outline">{{ __('actions.back') }}
      </x-ui.button>
    @endif

    <x-ui.button data-turbo-frame="_top" type="submit" theme="primary">{{ __('actions.save') }}</x-ui.button>
  </div>
</x-slot>
