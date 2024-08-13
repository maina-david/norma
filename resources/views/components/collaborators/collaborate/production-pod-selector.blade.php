@props(['name', 'label' => null, 'required' => false, 'value' => null])

<x-ui.input
            :name="$name"
            :required="$required"
            label="{{ $label }}"
            :value="old($name, $value ?? '')"
            type="select"
            :options="$pods"
            @change="$dispatch('changed', $el.value)"
            {{ $attributes }} />
