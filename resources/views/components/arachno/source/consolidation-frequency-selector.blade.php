@props(['label' => __('arachno.source.consolidation_frequency.title'), 'value' => null, 'name' => 'consolidation_frequency', 'required' => false, 'allowEmpty' => false])

@php
use App\Enums\Arachno\ConsolidationFrequency;
$options = ConsolidationFrequency::forSelector($allowEmpty);
@endphp

<x-ui.input
    :required="$required"
    :name="$name"
    :label="$label"
    type="select"
    :value="$value"
    :options="$options"
    {{ $attributes }}
/>
