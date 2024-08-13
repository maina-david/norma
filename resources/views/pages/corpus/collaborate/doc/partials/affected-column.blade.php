<div id="doc-affected-{{ $row->id }}" class="relative group">
  @if($row->updateCandidate)
    @include('pages.corpus.collaborate.doc.partials.affected-column-view-buttons')

    @can('collaborate.corpus.doc.update-candidate.set-affected-legislation')
      <x-ui.modal class="mt-2">
        <x-slot:trigger>
          <div>
            <x-ui.button styling="outline" theme="primary" @click="open = true">
              {{ __('corpus.doc.update_legislation') }}
            </x-ui.button>
          </div>
        </x-slot:trigger>

        <div class="w-screen-75 max-w-4xl">
          <x-turbo::frame loading="lazy" id="doc-affected-{{ $row->id }}-form" src="{{ route('collaborate.docs-for-update.affected.create', ['doc' => $row->id]) }}">
            <x-ui.skeleton />
          </x-turbo::frame>
        </div>
      </x-ui.modal>
    @endcan

    @include('pages.corpus.collaborate.doc.partials.affected-column-handover-button')
  @endif

</div>

