<x-ui.input
    {{ $attributes }}
    type="select"
    :value="$value"
    :name="$name"
    :label="$label"
    :options="$options"
    :multiple="$multiple"
    @change="$dispatch('changed', {{ $multiple ? 'Array.from($el.selectedOptions).map(function (it) { return it.value })' : '$el.value' }});"
/>
