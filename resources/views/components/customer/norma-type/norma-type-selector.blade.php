@php use App\Models\Customer\NormaType; @endphp
@props(['name', 'required', 'label', 'value', 'nullable'])
@php
  $types = NormaType::pluck('title', 'id')->all();
@endphp

<x-ui.input
    @change="$dispatch('changed', $el.value)"
    :name="$name ?? 'place_type_id'"
    type="select"
    :required="$required ?? false"
    :label="$label ?? __('customer.norma.norma_type')"
    :value="$value ?? null"
    :options="isset($nullable) && $nullable ? ['' => '--'] + $types : $types"
    {{ $attributes }}
/>
