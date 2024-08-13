@if ($row->view_url)
  <a class="text-primary" href="{{ $row->view_url }}" target="_blank">
    <x-ui.icon name="arrow-up-right-from-square" class="text-primary" size="4" />
  </a>
@endif
