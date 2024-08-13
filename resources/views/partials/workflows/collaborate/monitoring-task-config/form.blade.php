@php
  use App\Enums\Workflows\TaskStartDay;
  use App\Enums\Workflows\MonitoringTaskFrequency;use App\Models\Geonames\Location;

  $locations = old('enabled', $resource->locations ?? false);
  $locationOptions = [];

  if (!empty($locations)) {
    $locationOptions = Location::whereKey($locations)->pluck('title', 'id')->all();
  }
@endphp



<div>
  <x-ui.input name="title" :value="old('title', $resource->title ?? null)" :label="__('workflows.board.title')" required />

  <x-workflows.board.board-selector name="board_id" :value="old('board_id', $resource->board_id ?? null)" :label="__('workflows.monitoring_task_config.board')" required />

  <x-collaborators.group-selector name="group_id" :value="old('group_id', $resource->group_id ?? null)" :label="__('workflows.task.group')" :group="$resource->group ?? null" required />

  <x-workflows.tasks.task-priority-selector name="priority" :value="old('priority', $resource->priority ?? null)" :label="__('tasks.priority')" required />

  <x-ui.input required type="select" name="start_day" :label="__('workflows.monitoring_task_config.start_day')" :options="TaskStartDay::forSelector()" :value="old('start_day', $resource->start_day ?? '')"/>

  <x-ui.input required type="date" name="last_run" :label="__('workflows.monitoring_task_config.last_run_with_description')" :value="old('last_run', $resource->last_run ?? '')"/>

  <div class="grid grid-cols-5 gap-4">
    <div class="col-span-3">
      <x-ui.input required type="number" min="1" name="frequency_quantity" :label="__('workflows.monitoring_task_config.frequency')" :value="old('frequency_quantity', $resource->frequency_quantity ?? '')"/>
    </div>

    <div class="col-span-2">
      <x-ui.input required type="select" name="frequency" label="&nbsp;" :options="MonitoringTaskFrequency::forSelector()" :value="old('frequency', $resource->frequency ?? '')" />
    </div>
  </div>

  <x-geonames.location.location-selector required multiple :value="old('locations', $resource->locations ?? [])" name="locations[]" :options="$locationOptions" />

  <x-ontology.legal-domain-selector required :label="__('nav.legal_domains')" multiple :value="old('legal_domains', $resource->legal_domains ?? [])" name="legal_domains[]" />

  <div class="mt-6">
    <x-ui.input type="checkbox" name="enabled" :value="old('enabled', $resource->enabled ?? false)" :label="__('workflows.monitoring_task_config.enabled')" />
  </div>
</div>


<x-slot name="footer">
  <div></div>
  <div>
    <x-ui.back-button :fallback="route('collaborate.boards.index')"/>
    <x-ui.button
        type="submit"
        theme="primary"
    >{{ __('actions.save') }}</x-ui.button>
  </div>
</x-slot>
