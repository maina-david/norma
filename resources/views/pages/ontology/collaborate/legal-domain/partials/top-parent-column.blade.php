@if ($row->top_parent_id && $row->id !== $row->top_parent_id)
  <div>
    <a href="{{ route('collaborate.legal-domains.show', $row->top_parent_id) }}" class="text-primary">
      {{ $row->topParent->title }}
    </a>
  </div>
@else
  <div>-</div>
@endif
