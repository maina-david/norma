<x-layouts.app>
  <x-slot name="header">
    <a href="{{ route('my.tasks.task-projects.index') }}" class="text-primary mr-3">
      <x-ui.icon name="chevron-left" />
    </a>
    {{ __('tasks.task_project.create_project') }}
  </x-slot>

  <div class="shadow bg-white p-5 max-w-screen-md mx-auto">
    <x-ui.form :action="route('my.tasks.task-projects.store')" method="post">
      @include('partials.tasks.task-project.form')
    </x-ui.form>
  </div>

</x-layouts.app>
