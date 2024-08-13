@php use App\Models\Geonames\Location; @endphp
<x-ui.input
    required
    :value="old('title', $resource->title ?? null)"
    :label="__('interface.title')"
    name="title"
    maxlength="1000"
/>

<x-ui.input
    :value="old('title_translation', $resource->title_translation ?? null)"
    :label="__('corpus.work.title_translation')"
    name="title_translation"
    maxlength="1000"
/>

<div>
  <x-ontology.legal-domain-selector
      data-tomselect-no-reinitialise
      name="domains[]"
      :label="__('ontology.legal_domain.index_title')"
      multiple
      required
      annotations
      :location-id="$resource->primary_location_id ?? null"
      :value="old('domains', isset($resource->legalDomains)  ? $resource->legalDomains->pluck('id')->toArray() : [])"
  />
</div>

<div class="mt-4">
  <x-ui.label for="in_terms_of_work_id">{{ __('notify.legal_update.published_in_terms_of') }}</x-ui.label>

  <div class="flex -ml-2 mt-1">
    <x-corpus.work.work-selector
        :app-type="App\Enums\Application\ApplicationType::collaborate()"
        :value="old('in_terms_of_work_id', $resource->in_terms_of_work_id ?? null)"
        :work="$resource->notifiable ?? null"
        name="in_terms_of_work_id"
        frame="notifiable_for_legal_update"
    />
  </div>
</div>

<div class="mt-4">
  <x-ui.label for="in_terms_of_work_id">{{ __('notify.legal_update.notify_against') }}</x-ui.label>

  <div class="-ml-2 mt-1 flex">
    <x-corpus.reference.multi-reference-selector
        :app-type="App\Enums\Application\ApplicationType::collaborate()"
        :value="old('notify_reference_id', $resource->notify_reference_id ?? null)"
        :work-id="$resource->notifyReference->work_id ?? 0"
        :references="isset($resource->notifyReference) ? [$resource->notifyReference] : []"
        name="notify_reference_id"
        work-field-name="notify_reference_id_work"
        :single="true"
        :route-params="['has-requirement' => 2]"
    />
  </div>
</div>

<div>
  @if($user->can('collaborate.notify.legal-update.set-release-date'))
    <x-ui.input
        type="date"
        :value="old('release_at', isset($resource) ? ($resource->release_at?->format('Y-m-d') ?? null) : null)"
        :label="__('notify.legal_update.release_date')"
        name="release_at"
        class="w-44"
    />

  @else
    <x-ui.show-field type="date" :value="isset($resource) ? ($resource->release_at?->format('Y-m-d') ?? null) : null"
                     :label="__('notify.legal_update.release_date')"></x-ui.show-field>
  @endif
</div>

<div class="grid grid-cols-3 gap-4">
  <div>
    <x-arachno.source.source-selector
        required
        data-tomselect-no-reinitialise
        :value="old('source_id', $resource->source_id ?? 'eng')"
        :label="__('corpus.work.source')"
        name="source_id"
    />
  </div>
  <div>
    <x-ui.language-selector
        required
        data-tomselect-no-reinitialise
        :value="old('language_code', $resource->language_code ?? 'eng')"
        :label="__('notify.legal_update.language')"
        name="language_code"
        class="w-44"
    />
  </div>
  <div>
    <x-geonames.location.location-selector
        data-tomselect-no-reinitialise
        required
        :location="old('primary_location_id', $resource->primary_location_id ?? null) ? Location::find(old('primary_location_id', $resource->primary_location_id ?? null)) : null"
        :value="old('primary_location_id', $resource->primary_location_id ?? null)"
        :label="__('notify.legal_update.primary_jurisdiction')"
        name="primary_location_id"
        class="w-44"
    />
  </div>
  <div>
    <x-ui.input
        :value="old('work_number', $resource->work_number ?? null)"
        :label="__('notify.legal_update.work_number')"
        name="work_number"
        class="w-44"
    />
  </div>
  <div>
    <x-ui.input
        :value="old('publication_number', $resource->publication_number ?? null)"
        :label="__('notify.legal_update.gazette_number')"
        name="publication_number"
        class="w-44"
    />
  </div>
  <div>
    <x-ui.input
        :value="old('publication_document_number', $resource->publication_document_number ?? null)"
        :label="__('notify.legal_update.notice_number')"
        name="publication_document_number"
        class="w-44"
    />
  </div>

  <div>
    <x-ui.input
        type="date"
        :value="old('effective_date', isset($resource) ? ($resource->effective_date?->format('Y-m-d') ?? null) : null)"
        :label="__('notify.legal_update.effective_date')"
        name="effective_date"
        class="w-44"
    />
  </div>
  <div>
    <x-ui.input
        type="date"
        :value="old('publication_date',  isset($resource) ? ($resource->publication_date?->format('Y-m-d') ?? null) : null)"
        :label="__('notify.legal_update.publication_date')"
        name="publication_date"
        class="w-44"
    />
  </div>
</div>
