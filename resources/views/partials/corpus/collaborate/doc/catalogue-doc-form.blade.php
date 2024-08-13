
<div>
  <x-ui.input
    :value="old('download_url', $resource->docMeta->download_url ?? null)"
    :label="__('Download Url')"
    name="download_url"
    maxlength="1000"
  />

  <x-ui.input
    :value="old('work_number', $resource->docMeta->work_number ?? null)"
    :label="__('corpus.work.work_number')"
    name="work_number"
    maxlength="1000"
  />

  <x-corpus.work.work-type-id-selector
    :value="old('work_type_id', $resource->docMeta->work_type_id ?? null)"
    :label="__('corpus.work.work_type')"
    name="work_type_id"
  />

  <x-ui.input
    :value="old('publication_number', $resource->docMeta->publication_number ?? null)"
    :label="__('Publication Number')"
    name="publication_number"
    maxlength="1000"
  />
</div>

<div>
  <x-ui.input
    :value="old('publication_document_number', $resource->docMeta->publication_document_number ?? null)"
    :label="__('Publication Document Number')"
    name="publication_document_number"
    maxlength="1000"
  />
</div>

<div>
  <x-ui.input
    type="date"
    :value="old('work_date', (isset($resource) ? $resource->docMeta->work_date : null) ?? now()->format('Y-m-d'))"
    :label="__('Work Date')"
    name="work_date"
  />
</div>

<div>
  <x-ui.input
    type="date"
    :value="old('effective_date', (isset($resource) ? $resource->docMeta->effective_date : null) ?? now()->format('Y-m-d'))"
    :label="__('corpus.work.effective_date')"
    name="effective_date"
  />
</div>
