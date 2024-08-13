<x-layouts.app>
  <x-slot name="header">
    {{-- <a href="{{ route('my.tasks.tasks.index') }}" class="text-primary mr-3">
      <x-ui.icon name="chevron-left" />
    </a> --}}
    {{ __('tasks.edit_task') }}
  </x-slot>

  <turbo-frame id="tasks-details-{{ $task->id }}">
    <div class="shadow bg-white p-5 max-w-screen-md mx-auto">
      @include('partials.tasks.task.edit-details', ['task' => $task])
    </div>
  </turbo-frame>

</x-layouts.app>
