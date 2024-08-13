@props(['label' => __('arachno.source.source'), 'value' => null, 'name' => 'source_id', 'required' => false, 'allowEmpty' => false, 'multiple' => false])

@php
$sources = \App\Models\Arachno\Source::get(['id', 'title'])
    ->map(fn($s) => ['label' => $s->title, 'value' => $s->id])
    ->toArray();
$options = $allowEmpty ? array_merge([['label' => '--', 'value' => '']], $sources) : $sources;
@endphp
<x-ui.input
            :multiple="$multiple"
            :required="$required"
            :name="$name"
            :label="$label"
            type="select"
            :value="$value"
            :options="$options"
            {{ $attributes }} />
