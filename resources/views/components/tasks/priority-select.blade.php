@props(['value', 'label' => ''])
@php
use App\Enums\Tasks\TaskPriority;
@endphp

<x-ui.input
  @change="$dispatch('changed', $el.value)"
  name="priority"
  type="select"
  :options="['' => '--'] + TaskPriority::lang()"
  :value="$value"
  :label="$label ?? ''"
  class="tomselected"
/>
