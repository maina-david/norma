@can('collaborate.arachno.sources.jurisdictions.viewAny')
<a href="{{ route('collaborate.sources.jurisdictions.index', ['source' => $row->id]) }}" class="text-primary">
  {{ __('nav.jurisdictions') }}
</a>
@endcan
