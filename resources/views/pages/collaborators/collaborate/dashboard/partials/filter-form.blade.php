@php use App\Enums\Workflows\TaskStatus; @endphp
@php use App\Models\Geonames\Location; @endphp
<form class="p-4 rounded-lg bg-white shadow" method="get" action="{{ route('collaborate.dashboard') }}"
      x-data="{assignment: {{ request('assignment', 0) }} }">
  <div class="font-semibold">{{ __('workflows.task.search_for_tasks') }}</div>

  <x-ui.input :label="__('interface.search')" name="search" value="{{ request('search') }}"/>

  <x-ui.input
      type="select"
      name="assignment"
      :tomselect="false"
      :value="request('assignment', 0)"
      :label="__('tasks.assignment')"
      :options="[['value' => '0', 'label' => __('tasks.assigned_to_me')], ['value' => '1', 'label' => __('tasks.unassigned')]]"
      x-model="assignment"
  />

  <div x-show="assignment != 1">
    <x-workflows.tasks.task-status-selector name="status[]"
                                            :value="request('status', [TaskStatus::todo()->value, TaskStatus::inProgress()->value])"
                                            :label="__('workflows.task.task_status.label')" multiple no-pending
                                            no-archive/>
  </div>

  <x-workflows.tasks.task-priority-selector name="priority[]" :value="request('priority')" multiple/>

  <x-workflows.task-type.task-type-selector name="types[]" multiple permissioned :value="request('types')"/>

  <x-geonames.location.location-selector
      name="jurisdiction"
      :value="request('jurisdiction')"
      :location="request('jurisdiction') ? Location::find(request('jurisdiction')) : null"
  />

  <x-ontology.legal-domain-selector annotations placeholder="" name="domains" :value="request('domains', '')"
                                    :label="__('nav.legal_domains')"/>

  <x-workflows.tasks.task-sort-selector :value="request('sort', '-id')" name="sort" :label="__('interface.sort')"/>

  <div class="flex flex-col mt-2">
    <x-ui.button type="submit" styling="outline" theme="primary">
      <span class="text-center w-full">{{ __('interface.search') }}</span>
    </x-ui.button>
  </div>
</form>
