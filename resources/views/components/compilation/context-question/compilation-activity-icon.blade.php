@props(['activityType' => null])
@php
use App\Enums\Compilation\ApplicabilityActivityType;
  $icon = match($activityType) {
    ApplicabilityActivityType::COMMENT => 'comment-lines',
    default => 'exchange',
  };
@endphp

<x-ui.icon {{ $attributes }} :name="$icon"/>
