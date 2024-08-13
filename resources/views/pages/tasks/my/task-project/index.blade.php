<x-layouts.app>
  <x-slot name="header">
    <a href="{{ route('my.tasks.tasks.index') }}" class="text-primary mr-3">
      <x-ui.icon name="chevron-left" />
    </a>
    {{ __('tasks.manage_projects') }}
    <span class="text-xs text-libryo-gray-500 mt-1 ml-1 italic">{{ __('interface.beta') }}</span>
  </x-slot>
  <x-slot name="actions">
    <div>
      <x-ui.button type="link" :href="route('my.tasks.task-projects.create')" theme="primary">
        <x-ui.icon name="plus" size="3" class="mr-1" />
        {{ __('tasks.task_project.create_project') }}
      </x-ui.button>
    </div>
  </x-slot>

  <x-tasks.task-project.task-project-data-table :baseQuery="$query"
                                                :route="route('my.tasks.task-projects.index')"
                                                :fields="['showTasks', 'title', 'description', 'author', 'created_at', 'actions']"
                                                searchable
                                                :search-placeholder="__('tasks.task_project.search_projects') . '...'"
                                                filterable
                                                :filters="['archived']"
                                                :paginate="50" />

</x-layouts.app>
