@if ($row->docs_count > 0)
  <a class="text-primary"
     href="{{ route('collaborate.corpus.catalogue-docs.docs.index', ['catalogueDoc' => $row->id]) }}">
    {{ trans_choice('corpus.doc.docs_count', $row->docs_count, ['value' => $row->docs_count]) }}
  </a>
@endif
