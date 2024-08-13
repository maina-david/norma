@props(['name', 'value', 'route', 'label' => '', 'options' => [], 'multiple' => false, 'dataLoadStartSearching' => 3, 'placeholder' => ''])
<x-ui.input {{ $attributes->merge(['class' => 'remote-select']) }} type="select"
            :value="$value"
            :name="$name"
            :label="$label ?? ''"
            :placeholder="$placeholder ?? ''"
            :options="$options ?? []"
            :multiple="$multiple ?? false"
            :data-load="$route"
            :data-load-start-searching="$dataLoadStartSearching ?? 3" />
