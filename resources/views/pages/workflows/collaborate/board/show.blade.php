@php
  use App\Enums\Workflows\WizardStep;$order = [];

  if (($resource->taskTypes ?? false) && !empty($resource->task_type_order ?? '')) {
      $types = $resource->taskTypes->keyBy('id');
      $order = collect(explode(',', $resource->task_type_order))->map(fn ($type) => $types->get($type));
  }

  $canSetDefaults = auth()->user()->can('collaborate.workflows.board.set-task-defaults');
@endphp


<div>
  <x-ui.show-field :label="__('workflows.board.title')" :value="$resource->title"/>

  <x-ui.show-field :label="__('workflows.board.parent_task')" :value="$resource->parentTaskType->name ?? null"/>

  @if($resource->wizard_steps)
  <x-ui.show-field :label="__('workflows.task.wizard_steps.label')">
    @foreach($resource->wizard_steps as $step)
      <x-ui.badge>{{ WizardStep::tryFrom($step)?->label() ?? '' }}</x-ui.badge>
    @endforeach
  </x-ui.show-field>
  @endif

  @if($resource->optional_wizard_steps)
  <x-ui.show-field :label="__('workflows.task.wizard_steps.optional_steps')">
    @foreach($resource->optional_wizard_steps as $step)
      <x-ui.badge>{{ WizardStep::tryFrom($step)?->label() ?? '' }}</x-ui.badge>
    @endforeach
  </x-ui.show-field>
  @endif

  <x-ui.show-field :label="__('workflows.board.task_type_order')">
    @if(empty($resource->task_type_order ?? ''))
      -
    @endif
    <ol class="list-decimal list-inside divide-y">
      @foreach($order as $type)
        <li class="py-1 flex justify-between">
          <span>{{ $type->name }}</span>
          @if($canSetDefaults)
            <x-ui.button size="sm" styling="outline" type="link"
                         :href="route('collaborate.boards.task-types.defaults', ['board' => $resource->id, 'task_type' => $type->id])">
              {{ __('workflows.board_task_type_defaults.set_defaults') }}
            </x-ui.button>
          @endif
        </li>
      @endforeach
    </ol>
  </x-ui.show-field>

  <div class="mt-4">
    <x-ui.show-field type="checkbox" :label="__('workflows.board.for_legal_update')" :value="$resource->for_legal_update ?? false"/>
  </div>

  <div class="mt-4">
    <x-ui.show-field type="checkbox" :label="__('workflows.board.source_document_required')" :value="$resource->source_document_required ?? false"/>
  </div>
</div>
