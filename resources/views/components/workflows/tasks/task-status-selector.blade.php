@props(['name' => 'task_status', 'value' => null, 'label' => null, 'multiple' => false, 'required' => false, 'noPending' => false, 'noArchive' => false])
@php
use App\Enums\Workflows\TaskStatus;

  $statuses = collect(TaskStatus::forSelector(true));

  if ($noPending) {
      $statuses = $statuses->filter(fn ($item) => !TaskStatus::pending()->is($item['value']));
  }
  if ($noArchive) {
      $statuses = $statuses->filter(fn ($item) => !TaskStatus::archive()->is($item['value']));
  }

  $statuses = $statuses->toArray();
@endphp

<x-ui.input
    type="select"
    :name="$name"
    :value="$value"
    :multiple="$multiple"
    :label="$label"
    :required="$required"
    :options="$statuses"
    @tom-changed="$dispatch('changed', $event.detail)"
/>
