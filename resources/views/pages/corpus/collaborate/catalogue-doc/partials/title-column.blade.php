<div>

  <div>
    @if (!is_null($row->fetch_started_at))
      <x-ui.icon name="spinner" class="text-primary" size="4" class="fa-spin mr-3" />
    @endif
    @if (!$row->crawler?->can_fetch)
      <x-ui.icon name="square-exclamation" class="text-primary" size="4" class="mr-3 tippy"
                 data-tippy-content="{{ __('corpus.catalogue_doc.fetch_not_available') }}" />
    @endif

    <a class="text-primary"
       href="{{ route('collaborate.corpus.catalogue-docs.docs.index', ['catalogueDoc' => $row->id]) }}">
      {{ $row->title }}
    </a>
  </div>
  @if ($row->title_translation)
    <div class="text-norma-gray-400 text-xs">
      ({{ $row->title_translation }})
    </div>
  @endif
  <div class="text-norma-gray-400 text-xs">
    ({{ $row->source_unique_id }})
  </div>
  @if ($row->summary)
    <div class="text-norma-gray-600 text-xs italic">
      {{ $row->summary }}
    </div>
  @endif

</div>
