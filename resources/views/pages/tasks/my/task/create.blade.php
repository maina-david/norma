<x-layouts.app>
  <x-slot name="header">
    <a href="{{ route('my.tasks.tasks.index') }}" class="text-primary mr-3">
      <x-ui.icon name="chevron-left" />
    </a>
    {{ __('tasks.create_task') }}
  </x-slot>

  <div class="shadow bg-white p-5 max-w-screen-md mx-auto">
    <x-ui.form :action="route('my.tasks.tasks.store')" method="post">
      @include('partials.tasks.task.form', [
      'isCreateForm' => true,
      'taskableType' => $taskableType,
      'taskableId' => $taskableId])
    </x-ui.form>
  </div>

</x-layouts.app>
