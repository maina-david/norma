<div>
  <div class="norma-legislation py-4">
    <x-ui.collaborate.wysiwyg-content :content="$reference->summary->summary_body ?? __('corpus.summary.no_summary')" />
  </div>

  <div class="flex justify-between items-center">


    @if($reference->summary || $reference->summaryDraft)
      @if (!($previewing ?? false) && !($reference->summaryDraft ?? false))
          @can('collaborate.requirements.summary.draft.create')
            <x-ui.form method="post" :action="route('collaborate.corpus.summary.store', ['reference' => $reference->id])">
              <x-ui.button type="submit" styling="outline">
                {{ __('corpus.summary.request_update') }}
              </x-ui.button>
            </x-ui.form>
          @endcan
      @endif

    @if(!($previewing ?? false))
        @can('collaborate.requirements.summary.delete')
          <x-ui.delete-button :route="route('collaborate.corpus.summary.destroy', ['reference' => $reference->id])">
            {{ __('corpus.summary.delete_live_summary') }}
          </x-ui.delete-button>
        @endcan
    @endif

    @else

      @if(!($previewing ?? false))
        @can('collaborate.requirements.summary.create')
          <x-ui.form method="post" :action="route('collaborate.corpus.summary.store', ['reference' => $reference->id])">
            <x-ui.button type="submit" styling="outline">
              {{  __('corpus.summary.create_summary') }}
            </x-ui.button>
          </x-ui.form>
        @endcan
      @endif


    @endif
  </div>
</div>
