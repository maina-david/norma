<div>
  <a
    href="{{ route('collaborate.legal-domains.show', $row->id) }}"
    class="{{ $row->archived_at ? 'text-negative' : 'text-primary' }}"
  >
    {{ $row->title }}
  </a>
</div>


