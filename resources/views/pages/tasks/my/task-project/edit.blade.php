<x-layouts.app>
  <x-slot name="header">
    <a href="{{ route('my.tasks.task-projects.index') }}" class="text-primary mr-3">
      <x-ui.icon name="chevron-left" />
    </a>
    {{ __('tasks.task_project.edit_project') }}
  </x-slot>

  <div class="shadow bg-white p-5 max-w-screen-md mx-auto">
    <x-ui.form :action="route('my.tasks.task-projects.update', ['project' => $project->id])" method="put">
      @include('partials.tasks.task-project.form')
    </x-ui.form>
  </div>

  <div class="shadow bg-white p-5 max-w-screen-md mx-auto mt-20 flex flex-row justify-between">
    <div>
      <x-ui.confirm-modal :route="route('my.tasks.task-projects.destroy', ['project' => $project->id])"
                          data-turbo-frame="_top">
        <x-ui.button theme="negative" styling="outline">{{ __('actions.delete') }}</x-ui.button>
      </x-ui.confirm-modal>

    </div>
    <div>
      @if ($project->archived)
        <x-ui.confirm-modal :route="route('my.tasks.task-projects.unarchive', ['project' => $project->id])"
                            data-turbo-frame="_top"
                            method="post"
                            :confirmation="__('tasks.task_project.unarchive_confirmation')"
                            :label="__('tasks.task_project.unarchive_project')">
          <x-ui.button theme="tertiary" styling="outline">{{ __('tasks.task_project.unarchive_project') }}
          </x-ui.button>
        </x-ui.confirm-modal>
      @else
        <x-ui.confirm-modal :route="route('my.tasks.task-projects.archive', ['project' => $project->id])"
                            data-turbo-frame="_top"
                            method="post"
                            :confirmation="__('tasks.task_project.archive_confirmation')"
                            :label="__('tasks.task_project.archive_project')">
          <x-ui.button theme="tertiary" styling="outline">{{ __('tasks.task_project.archive_project') }}</x-ui.button>
        </x-ui.confirm-modal>
      @endif


    </div>
  </div>

</x-layouts.app>
