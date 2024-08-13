@props(['name', 'label' => null, 'required' => false, 'value' => null, 'group' => null])

<x-ui.input
    :name="$name"
    :required="$required"
    label="{{ $label }}"
    :value="old($name, $value ?? '')"
    type="select"
    :options="isset($group)? [$group->id => $group->title] : []"
    :data-load="route('collaborate.groups.json.index')"
    :data-load-start-searching="4"
    @change="$dispatch('changed', $el.value)"
    {{ $attributes->merge(['class' => 'remote-select']) }}
/>
