<div>
  <div class="flex">
    <a class="text-primary"
       href="{{ $row->for_update ? route('collaborate.corpus.docs.for-update.show', ['doc' => $row->id]) : route('collaborate.corpus.docs.show', ['doc' => $row->id]) }}">{{ $row->title }}</a>
  </div>
  @if ($row->docMeta->title_translation)
    <div>[{{ $row->docMeta->title_translation }}]</div>
  @endif

  <div class="flex mt-1">
    @if ($row->primaryLocation)
      <x-ui.country-flag
                         :country-code="$row->primaryLocation->flag"
                         class="h-5 w-5 rounded-full tippy mr-3"
                         data-tippy-content="{{ $row->primaryLocation->title }}" />
    @endif

    @foreach ($row->legalDomains as $domain)
      <div class="bg-norma-gray-500 text-white rounded-md px-3 py-0.5 mr-2 inline-block text-xs">{{ $domain->title }}</div>
    @endforeach
  </div>

  @if ($row->docMeta->summary)
    <div class="italic text-sm text-norma-gray-500">
      {{ $row->docMeta->summary }}
    </div>
  @endif

  @if ($row->categories->isNotEmpty())
    <div class="">
      @foreach ($row->categories as $category)
        <span
              class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium bg-norma-gray-100 text-norma-gray-800 mt-1">{{ $category->display_label }}</span>
      @endforeach
    </div>
  @endif
  @if ($row->keywords->isNotEmpty())
    <div class="italic text-xs text-norma-gray-700 mt-1">
      {{ __('corpus.keyword.keywords') }}: {{ $row->keywords->implode('label', ', ') }}
    </div>
  @endif
</div>
