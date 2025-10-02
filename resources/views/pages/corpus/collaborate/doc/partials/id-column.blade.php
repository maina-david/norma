<div
  class="tippy"
  data-tippy-allowhtml="true"
  data-tippy-content="Created:<br/> {{ $row->created_at }} UTC <br/> Version Hash:<br/> {{ $row->version_hash }}"
>
  <div>{{ $row->id }}</div>
</div>

<div class="text-sm text-norma-gray-500 mt-1">
  {{ Carbon\Carbon::parse($row->created_at)->format('d M Y') }}
</div>
