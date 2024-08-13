@props(['value', 'name', 'label' => '', 'allowEmpty' => true, 'tomselected' => true, 'multiple' => false])
@php
use App\Enums\Tasks\TaskStatus;

$options = $allowEmpty ? ['' => '--'] + TaskStatus::lang() : TaskStatus::forSelector();
@endphp

<x-ui.input
  @change="$dispatch('changed', {{ $multiple ? 'Array.from($el.selectedOptions).map(function (it) { return it.value })' : '$el.value' }});"
  :name="$name"
  type="select"
  :options="$options"
  :value="$value"
  :multiple="$multiple"
  :label="$label ?? ''"
  class="{{ $tomselected ? '' : 'tomselected' }}"
/>
