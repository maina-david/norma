@php
use App\Models\Geonames\Location;

$selectedLocation = old('primary_location_id', $resource->primary_location_id ?? null);
@endphp
<div>
  <x-ui.input
    required
    :value="old('title', $resource->title ?? null)"
    :label="__('interface.title')"
    name="title"
    maxlength="3000"
  />

  <x-ui.input
    :value="old('title_translation', $resource->title_translation ?? null)"
    :label="__('corpus.work.title_translation')"
    name="title_translation"
    maxlength="3000"
  />

  <x-arachno.source.source-selector
    name="source_id"
    :label="__('corpus.work.source')"
    :value="old('source_id', $resource->source_id ?? null)"
    required
    allow-empty
  />

  <x-ui.input
    required
    :value="old('source_unique_id', $resource->source_unique_id ?? null)"
    :label="__('corpus.work.source_unique_id')"
    name="source_unique_id"
    maxlength="255"
  />

  <x-ui.input
    :value="old('start_url', $resource->start_url ?? null)"
    :label="__('system.processing_job.start_url')"
    name="start_url"
    maxlength="3000"
  />

  <x-ui.input
    :value="old('view_url', $resource->view_url ?? null)"
    :label="__('system.processing_job.view_url')"
    name="view_url"
    maxlength="3000"
  />

  @if(request()->routeIs('collaborate.corpus.catalogue-docs.docs.create'))
    @include('partials.corpus.collaborate.doc.catalogue-doc-form')
  @else
    <x-geonames.location.location-selector
      required
      name="primary_location_id"
      :value="$selectedLocation"
      :label="__('notify.legal_update.primary_jurisdiction')"
      :location="$selectedLocation ? Location::find($selectedLocation) : null"
    />

    <div>
      <x-ui.language-selector
        required
        :value="old('language_code', $resource->language_code ?? 'eng')"
        :label="__('notify.legal_update.language')"
        name="language_code"
      />
    </div>

    <x-ui.textarea
      :value="old('summary', $resource->summary ?? null)"
      :label="__('corpus.reference.summary')"
      name="summary"
      maxlength="1000"
    />

  @endif

</div>

@if(!($noActions ?? false))
  <x-slot name="footer">
    <div></div>
    <div>
      <x-ui.back-button :fallback="route('collaborate.works.index')"/>
      <x-ui.button type="submit" theme="primary">{{ __('actions.save') }}</x-ui.button>
    </div>
  </x-slot>

@endif
