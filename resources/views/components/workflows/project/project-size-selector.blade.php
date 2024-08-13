@props(['name', 'value', 'label' => '', 'multiple' => false])

<x-ui.input
            {{ $attributes }}
            type="select"
            :value="$value"
            :name="$name"
            :label="$label"
            :options="\App\Enums\Workflows\ProjectSize::forSelector()"
            :multiple="$multiple"
            @change="$dispatch('changed', $el.value)" />
