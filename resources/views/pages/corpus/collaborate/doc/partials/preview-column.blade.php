<div class="text-xs">
  <a
    class="text-primary"
    href="{{ route('collaborate.corpus.docs.preview', ['doc' => $row->id]) }}"
    target="_blank"
  >
    {{ __('corpus.work.source_document') }}
  </a>
</div>

@if(($withText ?? false) && $row->firstContentResource?->textContentResource)
  <div class="mt-2 text-xs">
    <a
      class="text-primary"
      href="{{ $row->firstContentResource->textContentResource->path }}"
      target="_blank"
    >
      {{ __('corpus.doc.plain_text') }}
    </a>
  </div>
@endif
