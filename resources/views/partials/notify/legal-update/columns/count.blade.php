<div class="text-right">
  @if($row->libraries_count > 0 || $row->normas_count > 0)
  <span class="text-green-600">
    {{ $count }}
  </span>
  @else
    <span class="text-secondary">
    {{ $count }}
  </span>
  @endif
</div>
