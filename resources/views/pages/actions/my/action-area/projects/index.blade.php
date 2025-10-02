<x-layouts.app>
    <x-slot name="header">
        {{ __('tasks.manage_projects') }}
        <span class="text-xs text-norma-gray-500 mt-1 ml-1 italic">{{ __('interface.beta') }}</span>
    </x-slot>
    <x-slot name="actions">
        <div>
            <x-ui.button type="link" :href="route('my.projects.create')" theme="primary">
                <x-ui.icon name="plus" size="3" class="mr-1" />
                {{ __('tasks.task_project.create_project') }}
            </x-ui.button>
        </div>
    </x-slot>

    <x-tasks.task-project.task-project-data-table :baseQuery="$query"
                                                  :route="route('my.projects.index')"
                                                  :fields="['showTasks', 'title', 'description', 'author', 'created_at', 'actions']"
                                                  searchable
                                                  :search-placeholder="__('tasks.task_project.search_projects') . '...'"
                                                  filterable
                                                  :filters="['archived']"
                                                  :paginate="50" />

</x-layouts.app>
