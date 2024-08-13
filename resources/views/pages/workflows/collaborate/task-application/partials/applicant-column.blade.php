@can('collaborate.collaborators.collaborator.view')
  <a class="text-primary" href="{{ route('collaborate.collaborators.show', $row->user_id) }}">
    <x-collaborators.collaborator-summary :collaborator="$row->applicant" />
  </a>
@else
  <x-collaborators.collaborator-summary :collaborator="$row->applicant" />
@endcan
