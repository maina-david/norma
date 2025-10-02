@php
$isPDF = $work->isPDFSource();
$scrollOnLoad = $scrollTo ? "setTimeout(function () { window.scrollToItem(document.getElementById('{$scrollTo}'), '.norma-legislation'); }, 500);" : '';
@endphp

<div
  x-init="{{ $scrollOnLoad }}if (document.documentElement.clientWidth > 640) { window.Split(['#split-left-{{ $work->id }}', '#split-right-{{ $work->id }}'], { sizes: [30, 70] }); }"
  class="block md:flex flex-row splitter h-full overflow-hidden"
>
  <div id="split-left-{{ $work->id }}" class="hidden md:block h-full overflow-hidden">
    <div class="h-full overflow-y-auto">

      @if($isPDF)
        @foreach ($references as $index => $reference)
          <span class="block mb-1 text-sm indent-{{ $reference->level }}">
            {{ $reference->refPlainText?->plain_text }}
          </span>
        @endforeach
      @else
        @foreach ($references as $index => $reference)
          @if ($volume === $reference->volume)
            <a
              @click="window.scrollToItem(document.getElementById('{{ $reference->id }}'), '.norma-legislation')"
              data-turbo-frame="_top"
              class="text-primary block mb-1 hover:text-primary cursor-pointer text-sm indent-{{ $reference->level }}"
            >
              {{ $reference->refPlainText?->plain_text }}
            </a>
          @else
            <a
              @click="setTimeout( function () { window.scrollToItem(document.querySelector('#split-left-{{ $work->id }} > a:nth-child({{ $index }})'), '#split-left-{{ $work->id }}', 'instant'); window.scrollToItem(document.getElementById('{{ $reference->id }}'), '.norma-legislation');  }, 1800)"
              href="{{ route('my.works.full-text.show', ['work' => $work->id, 'volume' => $reference->volume, 'page' => $page]) }}#{{ $reference->id }}"
              class="text-primary block mb-1 hover:text-primary cursor-pointer text-sm indent-{{ $reference->level }}"
            >
              {{ $reference->refPlainText?->plain_text }}
            </a>
          @endif
        @endforeach
      @endif

      <div class="mt-5 pt-4 mr-2 border-t border-norma-gray-200">
         {{ $references->links() }}
       </div>
    </div>
  </div>

  <div id="split-right-{{ $work->id }}" class="h-full overflow-hidden">
    <div class="overflow-auto h-full norma-legislation">
      @if (!empty($work->source->source_content ?? '') && '<p>&nbsp;</p>' !== ($work->source->source_content ?? ''))
        <div class="italic text-norma-gray-600 md:ml-6 py-5 text-xs">
          {!! $work->source->source_content !!}
        </div>
      @endif

      <div class=" bg-white md:ml-6 py-7 relative">


        @if($work->source->hide_content ?? false)
          @include('partials.corpus.work.copyright-restricted', ['source' => $work->source, 'target' => $work->getCurrentExpression()?->source_url])
        @else

          @if($isPDF)
            <iframe
                id="work-document-preview"
                class="w-full"
                style="height:80vh"
                src="{{ route('my.corpus.works.preview.source', ['work' => $work->id]) }}"
            ></iframe>
          @else
            <div>{!! $html !!}</div>

            @if ($previousVolumePage || $nextVolumePage)
              <div class="mt-5 flex justify-between ml-3">
                @if ($previousVolumePage)
                  <x-ui.button disabled="{{ $previousVolumePage ? 'false' : 'true' }}" type="link" styling="outline"
                               :href="$previousVolumePage">
                    {!! __('pagination.previous') !!}
                  </x-ui.button>
                @else
                  <div class="w-8 h-8"></div>
                @endif

                @if ($nextVolumePage)
                  <x-ui.button disabled="{{ $nextVolumePage ? 'false' : 'true' }}" type="link" styling="outline"
                               :href="$nextVolumePage">
                    {!! __('pagination.next') !!}
                  </x-ui.button>
                @else
                  <div class="w-8 h-8"></div>
                @endif
              </div>
            @endif
          @endif

        @endif


      </div>
    </div>

  </div>
</div>
