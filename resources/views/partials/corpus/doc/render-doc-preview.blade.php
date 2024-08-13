@php
  $scrollTo = $scrollTo ?? null;
  $scrollOnLoad = $scrollTo ? "window.setTimeout(function() { window.scrollToItemWhenAvailable('#{$scrollTo}', '.libryo-legislation', 'smooth'); }, 200)" : '';
@endphp
<div x-init="{{ $scrollOnLoad }}"></div>
@if ($work && !empty($work->source->source_content ?? '') && '<p>&nbsp;</p>' !== ($work->source->source_content ?? ''))
  <div class="italic text-libryo-gray-600 px-5 py-3 text-xs">
    {!! $work->source->source_content !!}
  </div>
@endif
<x-corpus.doc.doc-preview :doc="$doc" />
