{{-- <div class="ml-5">
  <div class="flex flex-row justify-between items-center mb-3">
    @if (!isset($allowFullTextView) || $allowFullTextView === true)
      <div>
        <a href="{{ route('my.works.full-text.show', ['work' => $work->id]) }}">
          <x-ui.button theme="tertiary" styling="flat">
            {{ __('corpus.work.view_requirements_document') }}
            <x-ui.icon name="chevron-right" size="3" class="ml-3" />
          </x-ui.button>
        </a>

      </div>
    @else
      <div></div>
    @endif
    <div>
      <a target="_blank" href="{{ route('my.corpus.works.show', ['work' => $work]) }}">
        <x-ui.icon name="link" size="5" class="text-primary tippy mr-2"
                   data-tippy-content="{{ __('corpus.link_to_this') }}" />
      </a>
    </div>
  </div>

  <div class="text-lg font-bold mb-5">
    {{ __('corpus.reference.requirements') }}
  </div> --}}


<x-corpus.reference.reference-data-table :base-query="$baseQuery"
                                         :route="route('my.references.for.requirements', ['work' => $work->id])"
                                         :fields="['my_link']"
                                         :headings="false" />

{{-- @foreach ($references as $reference)
    <div class="mb-3 ">
      <a data-turbo-frame="_top"
         href="{{ route('my.corpus.references.show', ['reference' => $reference->id]) }}"
         class="text-primary cursor-pointer hover:text-primary-darker">
        {{ $reference->refPlainText?->plain_text }}
      </a>
    </div>
  @endforeach

  <div>
    {{ $references->links() }}
  </div> --}}
</div>
