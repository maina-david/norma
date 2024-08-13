@php
  use App\Enums\Arachno\ConsolidationFrequency;use App\Support\Languages;$locations = $resource->locations()->pluck('title')->toArray() ?? [];
@endphp

<div class="grid md:grid-cols-2 gap-4">
  <div>
    <x-ui.show-field :label="__('arachno.source.title')" :value="$resource->title"/>

    <x-ui.show-field :label="__('arachno.source.source_url')">
      @if ($resource->source_url)
        <a href="{{ $resource->source_url }}" target="_blank" class="text-primary">{{ $resource->source_url }}</a>
      @endif
    </x-ui.show-field>

    <x-ui.show-field :label="__('arachno.source.primary_language')"
                     :value="Languages::forSelector()[$resource->primary_language] ?? '-'"/>

    <x-ui.show-field :label="__('arachno.source.rights')">{!! $resource->rights !!}</x-ui.show-field>

    <div class="mt-4">
      <x-ui.show-field type="checkbox" :label="__('arachno.source.permission_obtained')"
                       :value="$resource->permission_obtained"/>
    </div>

    <div class="mt-4">
      <x-ui.show-field type="checkbox" :label="__('arachno.source.hide_content')" :value="$resource->hide_content"/>
    </div>

    <div class="mt-4">
      <x-ui.show-field type="checkbox" :label="__('arachno.source.monitoring')" :value="$resource->monitoring"/>
    </div>

    <div class="mt-4">
      <x-ui.show-field type="checkbox" :label="__('arachno.source.ingestion')" :value="$resource->ingestion"/>
    </div>

    <div class="mt-4">
      <x-ui.show-field type="checkbox" :label="__('arachno.source.has_script')" :value="$resource->has_script"/>
    </div>

  </div>

  <div>

    <x-ui.show-field :label="__('arachno.source.owner')" :value="$resource->owner->full_name ?? '-'"/>

    <x-ui.show-field :label="__('arachno.source.manager')" :value="$resource->manager->full_name ?? '-'"/>

    <x-ui.show-field
      :label="__('arachno.source.consolidation_frequency.title')"
      :value="ConsolidationFrequency::tryFrom($resource->consolidation_frequency)?->label() ?? '-'"
    />

    <x-ui.show-field :label="__('arachno.source.source_content')">{!! $resource->source_content !!}</x-ui.show-field>

    <x-ui.show-field :label="__('arachno.source.permission_notes')">{!! $resource->permission_note !!}</x-ui.show-field>

  </div>
</div>
