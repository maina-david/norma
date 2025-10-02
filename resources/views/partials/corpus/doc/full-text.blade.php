@if (!$resourceLink)
  <div x-init="window.Split(['#split-left-{{ $doc->id }}', '#split-right-{{ $doc->id }}'], { sizes: [30, 70] })" class="flex flex-row splitter h-full">
    <div id="split-left-{{ $doc->id }}">
      <div class="h-full relative flex flex-col">
        <nav class="min-h-0 flex-1 overflow-y-auto max-h-screen">
          <turbo-frame id="doc-toc-item-{{ $doc->id }}-root"
                       loading="lazy"
                       src="{{ $tocRoute ?? route('my.docs.toc', ['doc' => $doc->id, 'show' => 1]) }}">
            <x-ui.skeleton />
          </turbo-frame>
        </nav>
      </div>
    </div>

    <div id="split-right-{{ $doc->id }}">
      <div class="norma-legislation shadow bg-white ml-3 p-7 max-h-screen overflow-y-auto h-full">
        <turbo-frame id="content-full-text-{{ $doc->id }}">
          <div class="text-3xl text-center mt-40 px-10">
            {{ $doc->title }}
          </div>
          <div class="text-lg text-center mt-10 mb-40 px-10 text-norma-gray-400">
            {{ $doc->docMeta->title_translation }}
          </div>
        </turbo-frame>
      </div>

    </div>
  </div>
@else
  <div class="norma-legislation shadow bg-white p-3 max-h-screen overflow-y-auto">
    <div class="text-3xl text-center mt-3 px-10">
      {{ $doc->title }}
    </div>
    @if ($doc->docMeta->title_translation)
      <div class="text-lg text-center mt-10 mb-40 px-10 text-norma-gray-400">
        {{ $doc->docMeta->title_translation }}
      </div>
    @endif
    <turbo-frame id="content-full-text-{{ $doc->id }}" src="{{ $resourceLink }}">
    </turbo-frame>
  </div>
@endif
