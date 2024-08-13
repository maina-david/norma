<x-layouts.collaborate fluid>
  @can('collaborate.workflows.task.create')
    <x-slot:actions>
      <x-workflows.collaborate.task-create-wizard-button :project-id="request()->query('project')" />
    </x-slot:actions>
  @endcan

  <div class="-my-8 flex flex-col w-full overflow-hidden" style="height:calc(100vh - 7rem);" x-data="{ selected: {} }">
    <div class="flex-shrink-0">
      @include('pages.crud.index-table')
    </div>
    <x-ui.form id="data-table-actions-form-" x-ref="data-table-actions-form-" method="POST" :action="route('collaborate.tasks.actions')"
               class="w-full overflow-x-auto bg-white custom-scroll">
      <input type="hidden" x-ref="data-table-actions-action-" value="" name="action" />
      <div class="flex flex-grow overflow-y-hidden overflow-x-auto pb-4 items-start custom-scroll">

        @foreach ($taskTypes as $type)
          <div class="shadow w-80 flex-shrink-0 bg-white mr-4 max-h-full flex flex-col rounded-lg">

            <div class="flex-shrink-0 font-semibold text-center p-2 border-b border-t-4 border-libryo-gray-200 rounded-t"
                 style="border-color:{{ $type['colour'] }};">
              {{ $type['name'] }}
            </div>

            <div class="overflow-y-auto custom-scroll">
              <x-turbo::frame id="task-type-tasks-{{ $type['id'] }}" loading="lazy" class="space-y-1"
                             :src="route('collaborate.task-type.tasks.index', [
                                 ...$taskFilters,
                                 'type' => $type['id'],
                             ])">
              </x-turbo::frame>
              <x-turbo::frame id="task-type-tasks-{{ $type['id'] }}-pagination">
              </x-turbo::frame>
            </div>
          </div>
        @endforeach
      </div>
    </x-ui.form>

  </div>
</x-layouts.collaborate>
