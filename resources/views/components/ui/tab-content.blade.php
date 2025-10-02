@props(['name'])

<div {{ $attributes->merge(['class' => 'divide-y divide-norma-gray-200']) }} style="display:none;"
     x-show="tab === '{{ $name }}'">
  {{ $slot }}
</div>
