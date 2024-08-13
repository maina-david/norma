@foreach ($references as $reference)
  <div id="ref-{{ $reference->id }}"></div>
  @if($reference->children_count > 0)
    <div class="font-semibold">
      {!! $reference->refPlainText?->plain_text !!}
    </div>
  @else
    {!! $reference->htmlContent?->cached_content !!}
  @endif
@endforeach
