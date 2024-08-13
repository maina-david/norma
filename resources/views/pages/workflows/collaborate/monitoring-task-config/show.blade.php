@php
  use App\Enums\Workflows\MonitoringTaskFrequency;use App\Enums\Workflows\TaskPriority;
  use App\Enums\Workflows\TaskStartDay;use App\Enums\Workflows\WizardStep;
  use App\Models\Geonames\Location;
  use App\Models\Ontology\LegalDomain;
  $order = [];

  if (($resource->board->taskTypes ?? false) && !empty($resource->board->task_type_order ?? '')) {
      $types = $resource->board->taskTypes->keyBy('id');
      $order = collect(explode(',', $resource->board->task_type_order))->map(fn ($type) => $types->get($type));
  }

  $canSetDefaults = auth()->user()->can('collaborate.workflows.monitoring-task-config.create');

  $domains = LegalDomain::whereIn('id', $resource->legal_domains ?? [])->get(['id', 'title']);
  $locations = Location::with(['locationType'])->whereIn('id', $resource->locations ?? [])->get(['id', 'location_type_id', 'title']);

  $frequency = MonitoringTaskFrequency::tryFrom($resource->frequency)?->label() ?? '';
  $frequency = $frequency . ($resource->frequency_quantity > 1 ? 's' : '');
  $frequency = "{$resource->frequency_quantity} {$frequency}";

@endphp

<div>
  <x-ui.show-field :label="__('workflows.board.title')" :value="$resource->title"/>

  <x-ui.show-field :label="__('workflows.monitoring_task_config.board')" :value="$resource->board->title ?? null"/>

  <x-ui.show-field :label="__('workflows.task.group')" :value="$resource->group->title ?? null"/>

  <x-ui.show-field :label="__('tasks.priority')" :value="TaskPriority::fromValue($resource->priority)->label()"/>

  <x-ui.show-field :label="__('workflows.monitoring_task_config.start_day')" :value="TaskStartDay::tryFrom($resource->start_day)->label()"/>

  <x-ui.show-field :label="__('workflows.monitoring_task_config.frequency')" :value="$frequency"/>

  <x-ui.show-field :label="__('workflows.monitoring_task_config.last_run')" :value="$resource->last_run?->format('d M Y') ?? '-'"/>

  <x-ui.show-field :label="__('nav.jurisdictions')">
    <div class="flex">
      @foreach($locations as $location)
        <span class="mr-1">
          {{$location->title}} ({{ $location->locationType->title }})
          @if($loop->index > 0)
            ,
          @endif
        </span>

      @endforeach
    </div>
  </x-ui.show-field>

  <x-ui.show-field :label="__('nav.legal_domains')">
    <div class="flex">
      @foreach($domains as $domain)
        <span class="mr-1">
          {{  $domain->title }}
          @if($loop->index > 0)
            ,
          @endif
        </span>
      @endforeach
    </div>
  </x-ui.show-field>


  <div class="mt-4">
    <x-ui.show-field
        type="checkbox"
        :label="__('workflows.monitoring_task_config.enabled')"
        :value="$resource->enabled ?? false"
    />
  </div>

  <x-ui.show-field :label="__('workflows.board.task_type_order')">
    @if(empty($resource->board->task_type_order ?? ''))
      -
    @endif
    <ol class="list-decimal list-inside divide-y">
      @foreach($order as $type)
        <li class="py-1 flex justify-between">
          <span>{{ $type->name }}</span>
          @if($canSetDefaults)
            <x-ui.button
              size="sm"
              styling="outline"
              type="link"
              :href="route('collaborate.monitoring-task-configs.task-types.defaults', ['config' => $resource->id, 'task_type' => $type->id])"
            >
              {{ __('workflows.board_task_type_defaults.set_defaults') }}
            </x-ui.button>
          @endif
        </li>
      @endforeach
    </ol>
  </x-ui.show-field>
</div>
