<div class="ml-5 h-full flex flex-col">
  <div class="flex flex-row justify-between items-center mb-3">
    {{-- <div>
      <a href="{{ route('my.references.for.requirements', ['work' => $work->id]) }}">
        <x-ui.button theme="tertiary" styling="flat">
          <x-ui.icon name="chevron-left" size="3" class="mr-3" />
          {{ __('corpus.reference.requirements') }}
        </x-ui.button>
      </a>

    </div> --}}
    <div></div>
    <div class="mt-2">
      <a target="_blank" href="{{ route('my.corpus.works.show', ['work' => $work, 'view' => 'text']) }}">
        <x-ui.icon name="link" size="5" class="text-primary tippy"
                   data-tippy-content="{{ __('corpus.link_to_this') }}" />
      </a>
      <a target="_blank" href="{{ route('my.corpus.works.print-preview', ['work' => $work, 'volume' => $volume]) }}">
        <x-ui.icon name="print" size="5" class="text-primary tippy mx-3"
                   data-tippy-content="{{ __('actions.print_page') }}" />
      </a>
    </div>
  </div>

  <div style="max-height: {{ ($fluid ?? false) ? '100%' : 'calc(100vh - 300px)' }};" class="overflow-hidden h-screen">
    @include('partials.corpus.work.full-text', [
        'work' => $work,
        'references' => $references,
    ])
  </div>

</div>
