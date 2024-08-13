@php
  use App\Enums\Corpus\UpdateCandidateActionStatus;$options = UpdateCandidateActionStatus::lang();
@endphp

<div id="doc-additional-actions-{{ $row->id }}" class="relative group w-72">
  @if($row->updateCandidate)
    <div class="grid grid-cols-3 gap-2 text-xs">
      @foreach(($row->updateCandidate->notificationActions ?? []) as $action)
        <div class="col-span-2 {{ UpdateCandidateActionStatus::DONE->value == $action->pivot->status ? 'text-primary' : 'text-secondary' }}">
          {{ $action->text }}
        </div>

        <div
          class="{{ UpdateCandidateActionStatus::DONE->value == $action->pivot->status ? 'text-primary' : 'text-secondary' }}"
        >
          {{ $options[$action->pivot->status] ?? '-' }}
        </div>
      @endforeach

    </div>

    @can('collaborate.corpus.doc.update-candidate.set-additional-actions')
      <x-ui.modal class="mt-2">
        <x-slot:trigger>
          <div>
            <x-ui.button styling="outline" theme="primary" @click="open = true">
              {{ __('corpus.doc.update') }}
            </x-ui.button>
          </div>
        </x-slot:trigger>

        <div class="w-screen-75 max-w-4xl">
          <x-turbo::frame loading="lazy" id="doc-additional-actions-{{ $row->id }}-form"
                         src="{{ route('collaborate.docs-for-update.additional-actions.create', ['doc' => $row->id]) }}">
            <x-ui.skeleton/>
          </x-turbo::frame>
        </div>
      </x-ui.modal>
    @endcan
  @endif

</div>
