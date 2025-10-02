<x-layouts.app>
  <x-slot name="header">
    <div class="flex items-center">
      <x-ui.icon name="tasks" class="mr-3 ml-5  text-norma-gray-400" size="8" />
      <div>
        {{ __('my.nav.tasks') }}
        <span class="text-xs text-norma-gray-500 ml-3 italic">{{ $subTitle }}</span>
      </div>
    </div>
  </x-slot>
  <x-slot name="actions">
    <div class="flex flex-row justify-between items-center">
      <a target="_blank" href="https://success.norma.com/en/knowledge/use-tasks">
        <x-ui.icon name="question-circle" />
      </a>
      <x-ui.button styling="flat" type="link" theme="tertiary" :href="route('my.tasks.task-projects.index')">
        {{ __('tasks.manage_projects') }}</x-ui.button>

      @if ($canCreate)
        <x-tasks.create-task-button taskable-type="{{ $taskableType }}" taskable-id="{{ $taskableId }}" />
      @else
        <x-ui.button theme="gray" styling="outline" class="ml-2 tippy"
                     data-tippy-content="{{ __('tasks.task.cannot_create') }}">
          <x-ui.icon name="plus" size="3" class="mr-1" />
          {{ __('tasks.create_task') }}
        </x-ui.button>
      @endif
    </div>
  </x-slot>

  <x-tasks.task.layout>
    <div class="no-shadow">
      <x-tasks.task.task-data-table
        :baseQuery="$query->clone()"
        :route="$calendarView ? route('my.tasks.tasks.calendar.index', ['show-month' => $showMonth, 'show-year' => $showYear]) : route('my.tasks.tasks.index')"
        :fields="['assignee_avatar', 'title_link', 'status', 'priority', 'due_on', 'completed_at', 'task_type']"
        searchable
        :search-placeholder="__('tasks.task.search_tasks') . '...'"
        filterable
        :filters="['assignee', 'statuses', 'type', 'priority', 'project', ...($calendarView ? [] : ['start_date', 'end_date', 'archived', 'overdue'])]"
        :paginate="100"
        side-filters
      >
        <x-slot name="actionButton">
          <x-ui.modal>
            <x-slot name="trigger">
              <x-ui.button @click="open = true" styling="flat" theme="tertiary"
                           data-tippy-content="{{ __('interface.export') }}"
                           class="tippy mt-1">
                <x-ui.icon name="download" />
              </x-ui.button>
            </x-slot>

            <div class=" w-screen-50">
              <turbo-frame id="download-progress" loading="lazy" src="{{ route('my.tasks.export.excel', $filters) }}">
              </turbo-frame>
            </div>
          </x-ui.modal>
        </x-slot>


        @if($calendarView)
          <x-slot name="tableBody">
            <x-tasks.task.task-calendar
              :initial-month="$showMonth"
              :initial-year="$showYear"
              :filters="$filters"
              :query="$query"
              route="my.tasks.tasks.calendar.index"
            />
          </x-slot>

          <x-slot name="customPagination">
            <div></div>
          </x-slot>
        @endif

      </x-tasks.task.task-data-table>
    </div>
  </x-tasks.task.layout>

</x-layouts.app>
