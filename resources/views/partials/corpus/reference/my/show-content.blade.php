{!! $reference->htmlContent?->cached_content !!}

@if ($reference->childReferences->isNotEmpty())
  @foreach ($reference->childReferences as $child)
    <div>
      <a href="{{ route('my.references.partial.show.full-text', ['reference' => $child->id]) }}"
         class="block mb-1 text-primary cursor-pointer text-sm">
        {{ $child->refPlainText?->plain_text }}
      </a>
    </div>
  @endforeach
@endif
