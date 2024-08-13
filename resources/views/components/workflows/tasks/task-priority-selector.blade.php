@props(['name' => 'priority', 'value' => null, 'label' => __('workflows.task.priority'), 'multiple' => false, 'required' => false])

<x-ui.input
  type="select"
  :name="$name"
  :value="$value"
  :multiple="$multiple"
  :label="$label"
  :required="$required"
  :options="\App\Enums\Workflows\TaskPriority::forSelector(true)"
  @tom-changed="$dispatch('changed', $event.detail)"
/>
