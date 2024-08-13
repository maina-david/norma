@php
  use Illuminate\View\ComponentAttributeBag;
@endphp
<x-ui.remote-select
    :name="$name"
    :value="$value"
    :options="$options ?? []"
    :placeholder="$placeholder ?? ''"
    :multiple="$multiple"
    :route="$route"
    @change="$dispatch('changed', {{ $multiple ? 'Array.from($el.selectedOptions).map(function (it) { return it.value })' : '$el.value' }});"
    {{ $attributes ?? new ComponentAttributeBag() }}
/>
