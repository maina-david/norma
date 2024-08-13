<div id="doc-affected-{{ $row->id }}-view-buttons">
  @foreach(($row->updateCandidate->legislation ?? []) as $legislation)
    <div class="py-1">
      <div class="mb-1">
        @if($legislation->source_url)
          <a href="{{ $legislation->source_url }}" class="tippy text-primary" data-tippy-content="{{ $legislation->title }}{{ $legislation->title_translation ? "[{$legislation->title_translation}]" : '' }}">
            {{ __('interface.view') }}
          </a>
        @elseif($legislation->work_id)
          <a href="{{ route('collaborate.works.show', ['work' => $legislation->work_id]) }}" class="tippy text-primary" data-tippy-content="{{ $legislation->title }}">
            {{ __('interface.view') }}
          </a>
        @elseif($legislation->catalogue_doc_id)
          <a href="{{ route('collaborate.corpus.catalogue-docs.show', ['catalogueDoc' => $legislation->work_id]) }}" class="tippy text-primary" data-tippy-content="{{ $legislation->title }}">
            {{ __('interface.view') }}
          </a>
        @else()
          -
        @endif
      </div>

      <div class="pl-1">
        @include('pages.corpus.collaborate.doc.partials.handover-update-column', ['handovers' => $legislation->handovers, 'legislation' => $legislation])
      </div>
    </div>
  @endforeach
</div>
