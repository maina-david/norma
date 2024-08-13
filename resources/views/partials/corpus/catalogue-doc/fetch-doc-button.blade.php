
@if($catalogueDoc->crawler?->can_fetch)
  @if(is_null($catalogueDoc->fetch_started_at))
    <x-ui.form method="POST" action="{{ route('collaborate.corpus.catalogue-docs.actions') }}">
      <input type="hidden" name="action" value="fetch">
      <input type="hidden" name="actions-checkbox-{{ request()->route('catalogueDoc') }}" value="true">

      <x-ui.button type="submit" theme="primary" styling="outline">
        {{ __('corpus.catalogue_doc.fetch_from_source') }}
      </x-ui.button>
    </x-ui.form>
  @else
    <x-ui.button type="link" theme="primary" styling="outline" href="#">
      <x-ui.icon name="spinner" class="text-primary" size="4" class="fa-spin mr-3" />
      <span>{{ __('corpus.catalogue_doc.fetching_from_source') }}</span>
    </x-ui.button>
  @endif
@endif
