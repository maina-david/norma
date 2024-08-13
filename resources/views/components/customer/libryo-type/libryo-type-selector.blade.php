@php use App\Models\Customer\LibryoType; @endphp
@props(['name', 'required', 'label', 'value', 'nullable'])
@php
  $types = LibryoType::pluck('title', 'id')->all();
@endphp

<x-ui.input
    @change="$dispatch('changed', $el.value)"
    :name="$name ?? 'place_type_id'"
    type="select"
    :required="$required ?? false"
    :label="$label ?? __('customer.libryo.libryo_type')"
    :value="$value ?? null"
    :options="isset($nullable) && $nullable ? ['' => '--'] + $types : $types"
    {{ $attributes }}
/>
