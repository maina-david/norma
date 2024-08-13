<div x-data="{ editing: false }">
  <div x-show="!editing" >
    <div class="libryo-legislation relative py-4 group">
      <x-ui.collaborate.wysiwyg-content :content="$reference->summaryDraft?->summary_body ?? __('corpus.summary.no_summary')" />

      @if(!($previewing ?? false))
        <x-ui.button @click="editing = true" styling="outline" class="hidden absolute top-2 right-2 group-hover:block">
          <x-ui.icon name="pencil" />
        </x-ui.button>
      @endif
    </div>

    @if(!($previewing ?? false))
      <div class="flex justify-between items-center">
        @can('collaborate.requirements.summary.draft.apply')
          <x-ui.form method="put" :action="route('collaborate.corpus.summary.draft.apply', ['reference' => $reference->id])">
            <x-ui.button type="submit" styling="outline">
              {{ __('corpus.summary.apply_draft_summary') }}
            </x-ui.button>
          </x-ui.form>
        @endcan

        @can('collaborate.requirements.summary.draft.delete')
          <x-ui.delete-button :route="route('collaborate.corpus.summary.draft.destroy', ['reference' => $reference->id])">
            {{ __('corpus.summary.delete_draft_summary') }}
          </x-ui.delete-button>
        @endcan
      </div>
    @endif

  </div>

  @if(!($previewing ?? false))

    @can('collaborate.requirements.summary.draft.update')
      <x-ui.form class="notranslate" x-show="editing" method="put" :action="route('collaborate.corpus.summary.draft.update', ['reference' => $reference->id])">

        <x-ui.textarea wysiwyg="basic" :value="old('body', $reference->summaryDraft?->summary_body ?? null)" name="body" />

        <div class="flex justify-end space-x-4 mt-4">
          <x-ui.button @click="editing = false" styling="outline">
            {{ __('actions.cancel') }}
          </x-ui.button>

          <x-ui.button type="submit">
            {{ __('actions.save') }}
          </x-ui.button>
        </div>
      </x-ui.form>
    @endcan

  @endif


</div>
