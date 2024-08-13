<x-corpus.reference.collaborate-nav :reference-id="$reference->id">
  <div class="mb-8">
    <div class="font-semibold">{{ $reference->title }}</div>
  </div>

  <div class="libryo-legislation">
    {!! $reference->htmlContent?->cached_content !!}
  </div>
</x-corpus.reference.collaborate-nav>
