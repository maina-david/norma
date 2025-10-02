@props(['for'])

<label {{ $attributes->merge(['class' => 'text-sm font-medium text-norma-gray-700 block']) }} for="{{ $for }}">
    {{ $slot }}
</label>
