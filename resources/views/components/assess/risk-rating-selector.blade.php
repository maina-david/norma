@props(['value', 'name', 'label', 'allowEmpty' => false])
@php
    $options = collect(\App\Enums\Assess\RiskRating::lang())->reverse()->toArray();
    if ($allowEmpty) {
        $options = ['' => '--'] + $options;
    }
@endphp

<x-ui.input {{ $attributes }} type="select" :value="$value"
            :name="$name"
            :label="$label"
            :options="$options" />
