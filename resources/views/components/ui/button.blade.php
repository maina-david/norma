@props(['type', 'class', 'theme'])

@if ($type === 'link')
  <a {{ $attributes->merge(['class' => 'inline-block ' . $getClass()]) }}>
    {{ $slot }}
  </a>
@else
  <button {{ $attributes->merge(['class' => $getClass()]) }} type="{{ $type }}">
    {{ $slot }}
  </button>
@endif
