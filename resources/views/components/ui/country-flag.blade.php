@props(['countryCode'])

@php
    $countryCode = strtolower($countryCode);
    $target = str_contains($countryCode, '.') ? '' : '.svg';
    $target = "/img/flags/{$countryCode}{$target}";
@endphp

<img {{ $attributes }} src="{{ asset($target) }}" />
