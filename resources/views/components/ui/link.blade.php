@props(['href'])

<a {{ $attributes->merge(['class' => 'text-primary']) }} href="{{ $href }}">
  {{ $slot }}
</a>
