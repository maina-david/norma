@props(['type' => 'info'])

@php
$icon = match ($type) { 'positive' => 'check-circle',  'warning' => 'exclamation-circle',  'negative' => 'exclamation-circle',  default => 'info-circle' };
@endphp

<div
     {{ $attributes->merge(['class' => 'p-3 text-' . $type . '-darker border-' . $type . ' border border-dashed rounded-md italic ']) }}>

  <span class="float-left inline-block">
    <x-ui.icon :name="$icon" class="mx-5 text-xl" />
  </span>
  {{ $slot }}

</div>
