<x-layouts.collaborate>
  <div class="px-4 py-4 sm:px-0">
    <div class="overflow-hidden rounded-lg bg-white shadow mb-10">
      <div class="px-4 py-5 sm:p-6">

        <x-ui.form class="space-y-2" method="get" action="{{ route('collaborate.arachno.search-pages.search') }}">
          <div>
            <x-ui.input name="q" value="{{ $query ?? '' }}" placeholder="Search...." />
          </div>
          <div>
            <x-arachno.source.source-selector value="{{ $sourceId }}" />
          </div>
          <x-ui.button theme="primary" class="justify-center w-full" type="submit">Search</x-ui.button>
        </x-ui.form>
      </div>
    </div>

    @foreach ($results as $result)
      <div class="space-y-2  rounded-lg bg-white shadow p-2 mb-1">
        <div><a class="text-primary" href="{{ $result['page_url'] }}" target="_blank">{{ $result['title'] }}</a></div>
        <div class="text-xs text-norma-gray-500 italic">{{ $result['url_host'] }}</div>
        @if (!empty($result['documents']))
          <x-ui.collapse title-size="text-base" title="{{ $result['documents'][0]['text'] }}" flat
                         class="border-b border-norma-gray-200">
            @foreach ($result['documents'] as $doc)
              <div class="text-norma-gray-400 text-xs italic">{{ $doc['text'] }}</div>
            @endforeach
          </x-ui.collapse>
        @endif

      </div>
    @endforeach
  </div>
</x-layouts.collaborate>
