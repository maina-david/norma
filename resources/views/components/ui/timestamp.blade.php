@php use Carbon\Carbon; @endphp
@props(['timestamp', 'type' => 'date'])

@if(empty($timestamp))
  <span {{ $attributes }}>-</span>
@else
  <span
    data-type="{{ $type }}"
    data-tippy-content="{{ Carbon::parse($timestamp)->toFormattedDateString() }}"
    data-timestamp="{{ Carbon::parse($timestamp)->timestamp }}"
    {{ $attributes->merge(['class' => "tippy"]) }}
  ></span>
@endif
