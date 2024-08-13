@php
  $options = [
    '-id' => __('interface.newest_first'),
    'id' => __('interface.oldest_first'),
    '-priority' => __('workflows.task.highest_priority'),
    'priority' => __('workflows.task.lowest_priority'),
  ];
@endphp


<x-ui.input type="select" :options="$options" {{ $attributes }} />
