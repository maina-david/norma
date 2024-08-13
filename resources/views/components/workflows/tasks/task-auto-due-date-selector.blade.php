@props(['name' => 'set_dependent_task_duration', 'value' => null, 'label' => '', 'required' => false])
@php
$options = collect([
    'workflows.task.auto_due_date_options.hour' => 'PT%dH',
    'workflows.task.auto_due_date_options.day' => 'P%dD',
    'workflows.task.auto_due_date_options.week' => 'P%dW',
    'workflows.task.auto_due_date_options.month' => 'P%dM',
])
  ->map(function ($format, $trans) {
      return collect([1,2,3,4,5,6,7,8,9,10])
        ->map(fn ($item) => ['value' => sprintf($format, $item),  'label' => __($trans . ($item > 1 ? 's' : ''), ['amount' => $item])])
        ->toArray();
  })
  ->reduce(fn ($a, $b) => $a->merge($b), collect())
  ->toArray();

@endphp

<x-ui.input
    type="select"
    :name="$name"
    :value="$value"
    :label="$label"
    :required="$required"
    :options="$options"
    @change="$el.value ? $dispatch('changed', $el.value) : null"
/>
