@if(($row->catalogueDoc->work->id ?? false) && !($row->expression->id ?? false))
  <x-ui.form method="post" :action="route('collaborate.corpus.catalogue-docs.docs.create-expression', ['catalogueDoc' => $row->catalogueDoc->id, 'doc' => $row->id])">
    <x-ui.button type="submit" size="sm">
      {{ __('actions.force_create') }}
    </x-ui.button>
  </x-ui.form>
@endif

