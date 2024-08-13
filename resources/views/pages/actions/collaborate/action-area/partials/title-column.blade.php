@if($row->archived_at)
  <span class="text-negative">{{ $row->title }}</span>
@else
  {{ $row->title }}
@endif
