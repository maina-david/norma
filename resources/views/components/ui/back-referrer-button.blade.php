@props(['fallback' => null, 'exclude' => []])
@php
    $prefix = url('/');
    $referer = str_replace($prefix, '', request()->header('referer', ''));
    $current = str_replace($prefix, '', request()->fullUrl());
    $stack = collect(session('_ref_back', []));

    $location = $stack->search(fn ($value) => explode('?', $value)[0] === explode('?', $referer)[0]);

    if ($location !== false) {
        $stack->splice($location, count($stack));
    }

    if (!empty($referer) && !in_array($referer, $exclude)) {
        $stack->push($referer);
    }

    $location = $stack->search(fn ($value) => explode('?', $value)[0] === explode('?', $current)[0]);
    if ($location !== false) {
        $stack->splice($location, count($stack));
    }

    session()->put('_ref_back', $stack->toArray());
@endphp
@if($stack->isNotEmpty() || $fallback)
  <a
    data-turbo="false"
    target="_top"
    href="{{ $stack->isNotEmpty() ? url($stack->last()) : $fallback }}"
    class="inline-flex items-center rounded-full font-semibold text-xs text-center transition ease-in-out duration-150 tracking-widest p-2 bg-transparent border border-primary text-primary hover:bg-primary hover:text-white active:border-primary focus:outline-none focus:border-primary focus:ring-2 ring-primary"
  >
    <x-ui.icon name="chevron-left" :size="4" />
  </a>
@endif
