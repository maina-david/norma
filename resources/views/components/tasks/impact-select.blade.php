@props(['value', 'label' => ''])
@php
  use App\Enums\Tasks\TaskPriority;
  $values = collect(range(1, 10))->mapWithKeys(fn ($item) => [$item => $item])->all();
@endphp

<x-ui.input
  @change="$dispatch('changed', $el.value)"
  name="impact"
  type="select"
  :options="['' => '--'] + $values"
  :value="$value"
  :label="$label ?? ''"
  class="tomselected"
  {{ $attributes }}
/>
