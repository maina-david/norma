<x-ui.input
            :name="$name"
            :multiple="$multiple"
            :required="$required"
            label="{{ $label }}"
            :value="$inputValue"
            type="select"
            :options="$options"
            :data-load="route('collaborate.collaborators.json.index')"
            :data-load-start-searching="3"
            @change="$dispatch('changed', $el.value)"
            {{ $attributes->merge(['class' => 'remote-select']) }} />
