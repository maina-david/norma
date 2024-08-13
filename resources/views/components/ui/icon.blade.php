@props(['name', 'type' => 'light', 'size' => 5, 'shrink' => false])

@php
$shrink = $shrink ? '' : ' shrink-0';
$type = match ($type) { 'duotone' => 'fad',  'regular' => 'far',  'solid' => 'fas',  default => 'fal' };

$fontSize = match ((int) $size) { 1 => '0.25rem',  1.5 => '0.375rem',  2 => '0.5rem',  2.5 => '0.625rem',  3 => '0.75rem',  3.5 => '0.875rem',  4 => '1rem',  5 => '1.25rem',  6 => '1.5rem',  7 => '1.75rem',  8 => '2rem',  9 => '2.25rem',  10 => '2.5rem',  11 => '2.75rem',  12 => '3rem',  14 => '3.5rem',  16 => '4rem',  20 => '5rem',  24 => '6rem',  28 => '7rem',  32 => '8rem',  36 => '9rem',  40 => '10rem',  44 => '11rem',  48 => '12rem',  52 => '13rem',  56 => '14rem',  60 => '15rem',  64 => '16rem',  72 => '18rem',  80 => '20rem',  96 => '24rem',  default => '1.25rem' };
@endphp
<i {{ $attributes->merge(['style' => "font-size: {$fontSize};", 'class' => "w-{$size} h-{$size} {$shrink} fa-{$name} {$type}"]) }}></i>

{{-- <svg {{ $attributes->merge(['class' => "w-{$size} h-{$size} {$shrink} fill-current inline"]) }}>
  <use xlink:href='{{ asset("icons/{$type}.svg#{$name}") }}'></use>
</svg> --}}
