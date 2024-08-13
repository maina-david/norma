@props(['name' => 'set_dependent_units_type', 'multipleName' => 'set_dependent_units_multiple', 'value' => '', 'multiplier' => null, 'label' => __('workflows.task.set_dependent_units'), 'required' => false])

<div class="flex space-x-2" x-data="{ {{ $name }}_dependent:'{{ $value }}'}">
  <div class="flex-grow">
    <x-ui.input
      type="select"
      :name="$name"
      :label="$label"
      :required="$required"
      :options="\App\Enums\Workflows\DependentUnitsOption::forSelector(true)"
      x-model="{{ $name }}_dependent"
    />
  </div>

  <div>
    <template x-if="{{ json_encode(\App\Enums\Workflows\DependentUnitsOption::withMultiple()) }}.includes({{ $name }}_dependent)">
      <div>
        <x-ui.input style="width:7rem;" name="{{ $multipleName }}" :value="$multiplier" :label="!empty($label) ? __('workflows.task.the_value') : ''" />
      </div>
    </template>
  </div>
</div>









