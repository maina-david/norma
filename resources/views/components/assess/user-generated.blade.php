@php
$options = [
    '' => '',
    'yes' => __('interface.yes'),
    'no' => __('interface.no'),
];
@endphp

<x-ui.input
    label=""
    name="user_generated"
    type="select"
    :options="$options"
    @change="$dispatch('changed', $el.value);"
/>
