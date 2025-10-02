@if ($doc)
  <x-corpus.doc.doc-preview :doc="$doc" />
@else
  <div class="h-full flex flex-col">
    <div class="flex-grow bg-white w-full py-4 pl-6 pr-2 custom-scroll overflow-auto norma-legislation relative">
      <div>
        {!! $content->first() !!}
      </div>
    </div>

    <div class="flex-shrink-0 px-2 pb-1 bg-white">
      {{ $content->withQueryString()->links() }}
    </div>
  </div>
@endif
