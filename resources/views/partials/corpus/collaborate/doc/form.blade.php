@php
  use App\Models\Geonames\Location;$selectedLocation = old('primary_location_id', $resource->primary_location_id ?? null);
@endphp
<div>
  <div>
    <x-ui.file-selector
        :required="!($resource->id ?? false)"
        name="content_resource_file"
        :label="__('notify.legal_update.upload_related_document')"
    />

    @error('content_resource_file')
    <div class="text-sm text-red-400">{{ $message }}</div>
    @enderror
  </div>

  <x-ui.input
      required
      :value="old('title', $resource->title ?? null)"
      :label="__('interface.title')"
      name="title"
      maxlength="1000"
  />

  <x-ui.input
      :value="old('title_translation', $resource->docMeta->title_translation ?? null)"
      :label="__('corpus.work.title_translation')"
      name="title_translation"
      maxlength="1000"
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
      :value="old('source_url', $resource->docMeta->source_url ?? null)"
      :label="__('corpus.work.source_url')"
      name="source_url"
      maxlength="1000"
  />



  @if(request()->routeIs('collaborate.corpus.catalogue-docs.docs.create'))
    @include('partials.corpus.collaborate.doc.catalogue-doc-form')
  @else
    {{--  <x-ui.input--}}
    {{--      required--}}
    {{--      type="select"--}}
    {{--      :options="$locations + [null => '']"--}}
    {{--      :value="old('primary_location_id', $resource->primary_location_id ?? (count($locations) === 1 ? array_key_first($locations) : null))"--}}
    {{--      :label="__('notify.legal_update.primary_jurisdiction')"--}}
    {{--      name="primary_location_id"--}}
    {{--  />--}}

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
        :value="old('language_code', $resource->docMeta->language_code ?? 'eng')"
        :label="__('notify.legal_update.language')"
        name="language_code"
      />
    </div>

    <div>
      <x-ontology.legal-domain-selector
        annotations
        name="domains[]"
        :label="__('ontology.legal_domain.index_title')"
        multiple
        :value="isset($resource->legalDomains)  ? $resource->legalDomains->pluck('id')->toArray() : old('domains')"
      />
    </div>
    <div>
      <x-ui.input
        type="date"
        :value="old('document_date', (isset($resource) ? $resource->docMeta->work_date : null) ?? now()->format('Y-m-d'))"
        :label="__('corpus.work.publication_date')"
        name="document_date"
      />
    </div>
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
