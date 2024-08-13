@props(['content'])

<div {{ $attributes->merge(['class' => 'wysiwyg-content']) }}>
  {!! $content !!}
</div>
