@props(['name' => 'board', 'value' => null, 'label' => null, 'multiple' => false, 'required' => false])

<x-ui.input
    type="select"
    :name="$name"
    :value="$value"
    :multiple="$multiple"
    :label="$label"
    :required="$required"
    :options="(new \App\Cache\Workflows\Collaborate\BoardSelectorCache())->get()"
    @change="$dispatch('changed', $el.value)"
/>
