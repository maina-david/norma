<div>
  <a href="{{ route('collaborate.legal-updates.show', $row->id) }}">
    <div class="flex">
      <span>{{ $row->title }}</span>
    </div>
    @if ($row->title_translation)
      <div>[{{ $row->title_translation }}]</div>
    @endif
  </a>

  <div class="flex flex-wrap mt-1 space-y-1">
    @if ($row->primaryLocation)
      <x-ui.country-flag
                         :country-code="$row->primaryLocation->flag"
                         class="h-5 w-5 rounded-full tippy mr-3"
                         data-tippy-content="{{ $row->primaryLocation->title }}" />
    @endif

    @foreach ($row->legalDomains as $domain)
      <div class="bg-libryo-gray-500 whitespace-nowrap text-white rounded-md px-3 py-0.5 mr-2 inline-block text-xs">{{ $domain->title }}</div>
    @endforeach
  </div>
</div>
