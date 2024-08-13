<div>
  @if($row->ratingGroup)
    @can('collaborate.workflows.rating-group.view')
      <a class="whitespace-nowrap text-primary" href="{{ route('collaborate.rating-groups.show', $row->ratingGroup->id) }}">
        {{ $row->ratingGroup->name }}
      </a>
    @else
      {{ $row->ratingGroup->name }}
    @endcan
  @else
    -
  @endif
</div>
