<div>
  @foreach ($row->legalUpdates as $update)
    <a class="text-primary" href="{{ route('collaborate.legal-updates.show', ['legal_update' => $update->id]) }}">
    {{ __('corpus.doc.view_update_created_from') }}
    </a>
  @endforeach
</div>
