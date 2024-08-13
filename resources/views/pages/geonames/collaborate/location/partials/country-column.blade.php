<div class="flex flex-col justify-center mr-3 whitespace-nowrap">
  <div>
    @if ($row->location_country_id)
      <a href="{{ route('collaborate.jurisdictions.show', $row->location_country_id) }}"
         class="text-primary">
        {{ $row->country->title ?? '-' }}
      </a>
    @else
      {{ $row->country->title ?? '-' }}
    @endif
  </div>
  <div class="sub-row">
    <div>{{ $row->currency ?? '-' }}</div>
  </div>
</div>
