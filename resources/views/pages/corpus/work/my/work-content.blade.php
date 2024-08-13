@if ($work->source->hide_content ?? false)
  @include('partials.corpus.work.copyright-restricted', ['source' => $work->source, 'target' => $work->getCurrentExpression()?->source_url])
@else
  @if ($preview || $showSource)
    <iframe
      id="work-document-preview"
      class="w-screen-90 h-full overflow-hidden"
      style="height: 80vh;"
      src="{{ route('my.corpus.works.preview.source', ['work' => $work->id]) }}"
    >
    </iframe>
  @else
{{--  h-[70vh] max-h-[70vh]  --}}
    <turbo-frame
      src="{{ route('my.works.full-text.show', ['work' => $work->id, 'fluid' => $fluid ?? false, 'scroll_to' => $scrollTo ?? null]) }}"
      loading="lazy"
      id="corpus-work-full-text-{{ $work->id }}"
      class="overflow-hidden block {{ $maxHeight ?? 'h-full' }}"
    >
      <x-ui.skeleton :rows="2" />
    </turbo-frame>
  @endif
@endif
