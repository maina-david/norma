<x-ui.show-field :label="__('arachno.change_alert.title')" :value="$resource->title" />

<x-ui.show-field :label="__('arachno.change_alert.url')">
  @if ($resource->url)
    <a href="{{ $resource->url }}" target="_blank" class="text-primary">{{ $resource->url }}</a>
  @endif
</x-ui.show-field>

<x-ui.show-field :label="__('arachno.change_alert.changes')">{!! $resource->current !!}</x-ui.show-field>
<x-ui.show-field :label="__('arachno.change_alert.previous')">{!! $resource->previous !!}</x-ui.show-field>


<x-ui.show-field :label="__('arachno.change_alert.wachete')">
  @if ($resource->payload['alertUrl'] ?? null)
    <a href="{{ $resource->payload['alertUrl'] }}" target="_blank"
       class="text-primary">{{ __('arachno.change_alert.view_in_wachete') }}</a>
  @endif
</x-ui.show-field>

<x-ui.show-field :label="__('arachno.change_alert.date')">
  <x-ui.timestamp :timestamp="$resource->created_at" />
</x-ui.show-field>
