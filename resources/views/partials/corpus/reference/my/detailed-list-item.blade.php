
  <div class="flex items-center justify-between group hover:bg-norma-gray-50">
    <a data-turbo-frame="_top" href="{{ route('my.corpus.references.show', ['reference' => $reference->id]) }}" class="flex-grow py-1">
      <div class="min-w-0 flex-1 flex items-center max-w-screen-75">
      <div class="min-w-0 flex-1 px-4">
        <div>
          <p class="text-sm font-bold text-primary flex flex-row items-center">
            {{ $reference->refPlainText?->plain_text }}
          </p>
          <p class="text-sm items-center text-norma-gray-500">
            {{ $reference->work?->title ?? '' }}
            @if ($reference->work?->title_translation)
              <span class="ml-2 text-norma-gray-400">[{{ $reference->work?->title_translation }}]</span>
            @endif
          </p>
          <p class="mt-2 flex items-center text-sm text-norma-gray-400">
            <span class="truncate italic text-xs">
              {{ $reference->legalDomains?->implode('title', ', ') ?? '' }}
            </span>
          </p>

          @if (!empty($reference->_searchHighlights))
            <div class="font-light italic mt-3 mb-5">
              @foreach ($reference->_searchHighlights as $highlight)
                <div>
                  ... {!! $highlight !!} ...
                </div>
              @endforeach
            </div>
          @endif
        </div>
      </div>
    </div>
    </a>

    <div class="mr-5 ml-2 flex-shrink-0" style="min-width:5rem;">
      @if(($unlink ?? false) && ($response ?? false))
        <div class="hidden group-hover:block">
          <x-ui.confirm-button
              styling="flat"
              route="{{ route('my.references.for.assessment-item-response.destroy', ['reference' => $reference->id, 'response' => $response->id]) }}"
              method="DELETE"
              label="{{ __('corpus.reference.unlink_requirement') }}"
              confirmation="{{ __('corpus.reference.unlink_requirement_confirmation') }}"
          >
            {{ __('corpus.reference.unlink') }}
          </x-ui.confirm-button>
        </div>
      @endif

      <div class="flex justify-center -space-x-1 relative z-0 overflow-hidden relative group-hover:hidden">
        @foreach ($reference->locations as $location)
          <div class="relative z-10 inline-block h-6 w-6 rounded-full ring-2 ring-white">
            <x-ui.country-flag class="rounded-full ring-2 ring-white tippy"
                               data-tippy-content="{{ $location->title }}"
                               :country-code="$location->flag" />
          </div>
        @endforeach
      </div>
    </div>
    @if (isset($reference->normas_count))
      <div class="">
        <div class="tippy p-1 rounded-full text-norma-gray-800 bg-norma-gray-200"
             data-tippy-content="{{ __('customer.norma.applicable_norma_streams_this_many') }}">
          <div class="w-6 h-6 text-center flex justify-center flex-col justify-items-center">
            {{ $reference->normas_count }}
          </div>
        </div>
      </div>
    @endif
  </div>
