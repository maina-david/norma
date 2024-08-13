@if ($row->parent_id)
  <div>
    <a href="{{ route('collaborate.legal-domains.show', $row->parent_id) }}" class="text-primary">
      {{ $row->parent->title }}
    </a>
  </div>
@else
  <div>-</div>
@endif
