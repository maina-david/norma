@props(['name' => 'complexity', 'value' => null, 'label' => __('workflows.task.complexity'), 'multiple' => false, 'required' => false])

<x-ui.input
    type="select"
    :name="$name"
    :value="$value"
    :multiple="$multiple"
    :label="$label"
    :required="$required"
    :options="\App\Enums\Workflows\TaskComplexity::forSelector(true)"
    @change="$el.value ? $dispatch('changed', $el.value) : null"
/>
