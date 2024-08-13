@props(['label' => __('arachno.source_category.source_category'), 'value' => null, 'name' => 'source_category_id', 'required' => false, 'allowEmpty' => false, 'multiple' => true])

@php
  $sources = \App\Models\Arachno\SourceCategory::with(['source:id,title'])
      ->get(['id', 'title', 'source_id'])
      ->map(fn($s) => ['label' => $s->source?->title . ' - ' . $s->title, 'value' => $s->id])
      ->toArray();
  $options = $allowEmpty ? array_merge([['label' => '--', 'value' => '']], $sources) : $sources;
@endphp
<x-ui.input
            :required="$required"
            :name="$name"
            :label="$label"
            type="select"
            :value="$value"
            :options="$options"
            :multiple="$multiple"
            {{ $attributes }} />
