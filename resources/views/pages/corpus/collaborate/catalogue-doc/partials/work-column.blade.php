@if ($row->work)
  <a class="text-primary" href="{{ route('collaborate.works.show', ['work' => $row->work->id]) }}">
    {{ __('corpus.catalogue_doc.associated_work') }}
  </a>
@endif
