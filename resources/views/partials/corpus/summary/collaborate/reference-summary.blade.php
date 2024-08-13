<x-ui.tabs>
  <x-slot name="nav">
    <x-ui.tab-nav name="live">{{ __('corpus.summary.live') }}</x-ui.tab-nav>

    @if($reference->summaryDraft ?? false)
      <x-ui.tab-nav name="draft">{{ __('corpus.summary.draft.draft') }}</x-ui.tab-nav>
    @endif
  </x-slot>

  <x-ui.tab-content name="live">
    @include('partials.corpus.summary.collaborate.live')
  </x-ui.tab-content>

  @if($reference->summaryDraft ?? false)
  <x-ui.tab-content name="draft">
    @include('partials.corpus.summary.collaborate.draft')
  </x-ui.tab-content>
  @endif
</x-ui.tabs>
