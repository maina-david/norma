<x-layouts.app>
  <x-slot name="header">
    <div class="flex items-center">
      <div class="mr-2">
        <x-ui.back-referrer-button fallback="{{ route('my.tasks.tasks.index') }}" />
      </div>

      <div class="pt-2">
        {{ __('my.nav.tasks') }}
      </div>
    </div>
  </x-slot>

  <turbo-frame id="tasks-details-{{ $task->id }}">
    @include('partials.tasks.task.show-details', [
        'task' => $task,
        'user' => $user,
        'isActionsModule' => false
    ])
  </turbo-frame>

</x-layouts.app>
