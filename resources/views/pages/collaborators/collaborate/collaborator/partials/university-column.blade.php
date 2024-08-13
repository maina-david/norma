<div class="flex flex-col justify-center mr-3 whitespace-nowrap">
  <div>{{ $row->profile->university ?? '-' }}</div>
  <div class="sub-row">
    <div>{{ $row->profile->year_of_study ?? '-' }}</div>
  </div>
</div>
