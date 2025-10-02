<div class="norma-legislation p-3">
  {!! $reference->htmlContent?->cached_content !!}

  @if (!$reference->childReferences->isEmpty())
    @foreach ($reference->childReferences as $childRef)
      {!! $childRef->htmlContent?->cached_content !!}
    @endforeach
  @endif
</div>
@if ($reference->work->source)
  <div class="italic text-norma-gray-600 p-5 text-xs">
    {!! $reference->work->source->source_content !!}
  </div>
@endif
