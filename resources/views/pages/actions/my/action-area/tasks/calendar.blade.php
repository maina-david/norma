<x-layouts.app>
  <x-slot name="header">
    <div class="flex items-center">
      <x-ui.icon name="clipboard-list" class="mr-3 ml-2  text-libryo-gray-400" size="8" />
      <div>
        {{ __('actions.action_area.tasks') }}
      </div>
    </div>
  </x-slot>


  <div class="px-2">

    <x-actions.task.task-layout bordered>
      <div class="w-full">
        <x-tasks.task.task-calendar
          :initial-month="$showMonth"
          :initial-year="$showYear"
          :filters="$filters"
          :query="$query"
          route="my.actions.tasks.index"
          :route-params="['view' => 'calendar']"
          event-route="my.actions.tasks.show"
        />
      </div>
    </x-actions.task.task-layout>
  </div>

</x-layouts.app>
