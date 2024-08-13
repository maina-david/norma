@props(['value', 'name' => 'answer', 'label' => '', 'allowEmpty' => true, 'tomselected' => true, 'exclude' => [], 'withNotApplicable' => false])
@php
use App\Enums\Assess\ResponseStatus;

$options = $allowEmpty ? ['' => '--'] + ResponseStatus::lang() : ResponseStatus::forSelector();
$options = array_filter($options, fn ($val, $key) => !in_array($val, $exclude) && !in_array($key, $exclude), ARRAY_FILTER_USE_BOTH);
@endphp

<x-ui.input @change="$dispatch('changed', $el.value)" :name="$name" type="select"
            :options="$options"
            :value="$value"
            :label="$label ?? ''"
            class="{{ $tomselected ? '' : 'tomselected' }}" />

@if($withNotApplicable)

  <div class="mt-4">

    <x-ui.label @click="$refs.check.click()" for="">
      <span class="flex items-center">
        <x-ui.input
          @change="$dispatch('changed', $el.value)"
          :name="$name"
          type="checkbox"
          checkbox-value="{{ ResponseStatus::notApplicable()->value }}"
          :value="ResponseStatus::notApplicable()->value == $value"
          label=""
          x-ref="check"
        />
        <span>{{ ResponseStatus::notApplicable()->label() }}</span>
      </span>
    </x-ui.label>
  </div>

@endif
