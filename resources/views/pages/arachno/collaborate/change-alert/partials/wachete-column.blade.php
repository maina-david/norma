@if ($row->payload['alertUrl'] ?? null)
  <div class="flex flex-col justify-center mr-3 whitespace-nowrap">
    <a class="text-primary" target="_blank"
       href="{{ $row->payload['alertUrl'] ?? '' }}">{{ __('arachno.change_alert.view_in_wachete') }}</a>
  </div>
@endif
