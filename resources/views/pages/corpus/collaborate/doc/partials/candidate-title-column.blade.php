<div style="min-width:30vw">
  <div class="flex">
    <a class="text-primary"
       href="{{ route('collaborate.docs-for-update.tasks.show', ['doc' => $row->id, 'task' => request()->route('task')]) }}">{{ $row->title }}</a>
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
      <div class="bg-libryo-gray-500 text-white rounded-md px-3 py-0.5 mr-2 inline-block text-xs">{{ $domain->title }}</div>
    @endforeach
  </div>

  @if ($row->docMeta->summary)
    <div class="italic text-sm text-libryo-gray-500">
      {{ $row->docMeta->summary }}
    </div>
  @endif

  @if ($row->categories->isNotEmpty())
    <div class="">
      @foreach ($row->categories as $category)
        <span
            class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium bg-libryo-gray-100 text-libryo-gray-800 mt-1">{{ $category->display_label }}</span>
      @endforeach
    </div>
  @endif
  @if ($row->keywords->isNotEmpty())
    <div class="italic text-xs text-libryo-gray-700 mt-1">
      {{ __('corpus.keyword.keywords') }}: {{ $row->keywords->implode('label', ', ') }}
    </div>
  @endif

  <div class="text-sm text-libryo-gray-500 mt-2">
    {{ \Carbon\Carbon::parse($row->created_at)->format('d M Y') }}
  </div>
</div>
