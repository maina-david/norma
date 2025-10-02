@php
  $multiple = $multiple ?? false;
@endphp

<x-ontology.my.norma-legal-domain-selector
  :multiple="$multiple"
  @change="$dispatch('changed', {{ $multiple ? 'Array.from($el.selectedOptions).map(function (it) { return it.value })' : '$el.value' }});"
  :name="$name"
  :value="$value"
    {{ $attributes ?? new \Illuminate\View\ComponentAttributeBag() }}
/>

