<div id="doc-affected-{{ $row->id }}-handover-button">
@if(!$row->updateCandidate->legislation->isEmpty())
  @can('collaborate.corpus.doc.update-candidate.set-handover-updates')
    <x-ui.modal class="mt-2" @closed="document.querySelector('#doc-handover-update-{{ $row->id }}-form').src='#'">
      <x-slot:trigger>
        <div>
          <x-ui.button styling="outline" theme="primary" @click="open = true;document.querySelector('#doc-handover-update-{{ $row->id }}-form').src = document.querySelector('#doc-handover-update-{{ $row->id }}-form').getAttribute('data-src')">
            {{ __('corpus.doc.update_handover') }}
          </x-ui.button>
        </div>
      </x-slot:trigger>

      <div class="w-screen-75 max-w-4xl">
        <x-turbo::frame loading="lazy" id="doc-handover-update-{{ $row->id }}-form" data-src="{{ route('collaborate.docs-for-update.handover-updates.create', ['doc' => $row->id]) }}">
          <x-ui.skeleton />
        </x-turbo::frame>
      </div>
    </x-ui.modal>
  @endcan
@endif
</div>
