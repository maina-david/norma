@php
$editable = (!($row->updateCandidate->checked_at ?? false))|| auth()->user()->can('collaborate.corpus.doc.update-candidate.manage-without-task');
@endphp

<div id="doc-justification-{{ $row->id }}" class="relative group">

  @if($row->updateCandidate)
    <div class="whitespace-nowrap {{ $editable ? '' : 'text-norma-gray-400' }}">{!! $row->updateCandidate->justification_status ? __('corpus.doc.required') : __('corpus.doc.not_required') !!}</div>
    <div class="italic text-xs mt-2 w-96 {{ $editable ? '' : 'text-norma-gray-400' }}">
      <x-doc.view-more>
        {!! $row->updateCandidate->justification !!}
      </x-doc.view-more>
    </div>
  @endif

  @can('collaborate.corpus.doc.update-candidate.set-justification')
    @if($editable)

    <x-ui.modal class="mt-2">
      <x-slot:trigger>
        <div>
          <x-ui.button styling="outline" theme="primary" @click="open = true">
            {{ __('corpus.doc.update') }}
          </x-ui.button>
        </div>
      </x-slot:trigger>

      <div class="w-screen-75 max-w-4xl">
        <x-turbo::frame loading="lazy" id="doc-justification-{{ $row->id }}-form" src="{{ route('collaborate.docs-for-update.justification.create', ['doc' => $row->id]) }}">
          <x-ui.skeleton />
        </x-turbo::frame>
      </div>
    </x-ui.modal>
    @endif
  @endcan

</div>

