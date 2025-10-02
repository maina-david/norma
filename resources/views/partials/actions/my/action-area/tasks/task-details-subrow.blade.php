@php
  use App\Enums\Tasks\TaskPriority;
  use App\Enums\Tasks\TaskStatus;
@endphp

<div>
  <div class="px-6 py-4">
    <div class="mb-4 text-norma-gray-700 font-semibold">{{ __('workflows.task.title') }}</div>
    <div>
      <livewire:data.inline-input-editor
        name="title"
        :wire:key="Str::random()"
        :value="$row->title"
        :model="$row"
        rules="required|string|max:255"
        @field-updated="$refresh"
      />
    </div>
  </div>

  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 2xl:grid-cols-6 gap-8 border-b border-norma-gray-100 px-6 py-4">
    <div>
      <div class="mb-4 text-norma-gray-700 font-semibold">{{ __('tasks.status') }}</div>
      <livewire:tasks.task-status-switcher key="{{ Str::random() }}" :task-id="$row->id" :status="$row->task_status" />
    </div>

    <div>
      <div class="mb-4 text-norma-gray-700 font-semibold">{{ __('tasks.impact') }}</div>

      <livewire:data.inline-input-editor
          name="impact"
          :key="Str::random()"
          :value="$row->impact"
          :model="$row"
          type="select"
          :options="range(1, 10)"
          @field-updated="$refresh"
      />
    </div>

    <div>
      <div class="mb-1 text-norma-gray-700 font-semibold">{{ __('tasks.assigned_to') }}</div>
      <livewire:tasks.task-assignee-switcher wire:key="{{ Str::random() }}" :task-id="$row->id" :assignee="$row->assignee" />
    </div>

    <div>
      <div class="mb-4 text-norma-gray-700 font-semibold">{{ __('tasks.due_on') }}</div>
      <div>{{ $row->due_on?->format('d F, Y') }}</div>
    </div>

    <div>
      <div class="mb-4 text-norma-gray-700 font-semibold">{{ __('workflows.task.priority') }}</div>
      <div>
        <livewire:tasks.task-priority-switcher wire:key="{{ Str::random() }}" :task-id="$row->id" :priority="$row->priority" />
      </div>
    </div>

    <div>
      <div class="mb-1 text-norma-gray-700 font-semibold">{{ __('tasks.followers') }}</div>
      <div>
        <livewire:tasks.task-watchers-switcher :key="Str::random()" :task-id="$row->id" :watchers="$row->watchers->keyBy('id')->all()" />
      </div>
    </div>
  </div>
</div>

@if($row->description)
<div class="px-6 pb-4 pt-8">
  <div class="mb-4 text-norma-gray-800 font-semibold">{{ __('workflows.task.description') }}</div>
  <div>
    <x-ui.collaborate.wysiwyg-content :content="$row->description" />
  </div>
</div>
@endif

<div class="px-6 pb-4 pt-8">
  <div class="mb-4 text-norma-gray-800 font-semibold">{{ __('storage.attachment.index_title') }}</div>
  <div class="px-4 py-8">
    <turbo-frame
      wire:key="{{ Str::random() }}"
      loading="lazy"
      src="{{ route('my.drives.files.for.task.index', ['task' => $row]) }}"
      id="files-for-task-{{ $row->id }}"
    >
      <x-ui.skeleton/>
    </turbo-frame>
  </div>
</div>




