@php
  use App\Enums\Workflows\WizardStep;$selected = [];

  if (($resource->taskTypes ?? false) && !empty($resource->task_type_order ?? '')) {
      $types = $resource->taskTypes->map(fn ($type) => ['l' => $type->name, 'v' => $type->id])->keyBy('v');
      $selected = collect(explode(',', $resource->task_type_order))->map(fn ($type) => $types->get($type))->toArray();
  }

  $selected = str_replace('"', "'", json_encode($selected));

  $selectedSteps = collect($resource->wizard_steps ?? [])
    ->map(function ($item) {
        $item = WizardStep::tryFrom($item);
        return $item && !empty($item) ? ['l' => $item->label(), 'v' => $item->value] : null;
    })
    ->filter()
    ->toJson();

  $selectedSteps = str_replace('"', "'", $selectedSteps);
@endphp

<div class="grid grid-cols-1 lg:grid-cols-2 gap-4"
     x-data="{selectedSteps:{{ $selectedSteps }}, selectedTypes:{{ $selected }}}">
  <div>
    <x-ui.input name="title" :value="$resource->title ?? null" :label="__('workflows.board.title')" required/>

    <x-workflows.task-type.task-type-selector :label="__('workflows.board.parent_task')" name="parent_task_type_id"
                                              :filters="['is_parent' => true]"
                                              :value="$resource->parent_task_type_id ?? null"/>

    <x-workflows.task-type.task-type-selector
        @changed="selectedTypes = Array.from($event.target.selectedOptions).map(function (op) { return { l: op.textContent, v: op.value } })"
        :filters="['is_parent' => false]"
        :value="isset($resource) ? $resource->taskTypes->pluck('id')->toArray() : []"
        name="task_types[]"
        multiple
    />

    <x-ui.input
        @tom-changed="selectedSteps = Array.from($event.target.selectedOptions).map(function (op) { return { l: op.textContent, v: op.value } })"
        type="select"
        name="wizard_steps_selector"
        :label="__('workflows.task.wizard_steps.label')"
        multiple
        :options="WizardStep::forSelector()"
        :value="old('wizard_steps', $resource->wizard_steps ?? '')"
    />

    <x-ui.input
        type="select"
        name="optional_wizard_steps[]"
        :label="__('workflows.task.wizard_steps.optional_steps')"
        multiple
        :options="WizardStep::forSelector()"
        :value="old('optional_wizard_steps', $resource->optional_wizard_steps ?? '')"
    />

    <div class="mt-6">
      <x-ui.input type="checkbox" name="for_legal_update" :value="$resource->for_legal_update ?? false"
                  :label="__('workflows.board.for_legal_update')"/>
    </div>

    <div class="mt-4">
      <x-ui.input type="checkbox" name="source_document_required" :value="$resource->source_document_required ?? false"
                  :label="__('workflows.board.source_document_required')"/>
    </div>
  </div>

  <div>
    <x-ui.input class="hidden" name="wizard_steps" x-bind:value="selectedSteps.map(function(t){return t.v}).join(',')"
                :label="__('workflows.task.wizard_steps.steps_order')"/>

    <ul class="sortable space-y-1" data-update-input="#wizard_steps">
      <template x-for="step in selectedSteps" :key="step.v">
        <li :data-value="step.v" class="w-full px-2 py-1 rounded border cursor-grab" x-text="step.l"></li>
      </template>
    </ul>


    <x-ui.input class="hidden" name="task_type_order"
                x-bind:value="selectedTypes.map(function(t){return t.v}).join(',')"
                :label="__('workflows.board.task_type_order')"/>

    <ul class="sortable space-y-1" data-update-input="#task_type_order">
      <template x-for="type in selectedTypes" :key="type.v">
        <li :data-value="type.v" class="w-full px-2 py-1 rounded border cursor-grab" x-text="type.l"></li>
      </template>
    </ul>
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
