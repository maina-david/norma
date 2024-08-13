@if ($row->team)
  <a class="text-primary cursor-pointer"
     href="{{ route('collaborate.collaborators.teams.payments.index', ['team' => $row->team->id]) }}">{{ $row->team->title }}</a>
@endif
