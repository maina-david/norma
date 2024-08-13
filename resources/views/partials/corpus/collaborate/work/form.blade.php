@php use App\Models\Geonames\Location; @endphp
<div x-data="">

  @if($withSourceDocument ?? false)
    <div>
      <x-ui.file-selector name="source_document"/>
    </div>
  @endif
  <x-ui.input maxlength="1000" required :label="__('corpus.work.title')"
              :value="old('title', $resource->title ?? request('title'))" name="title"/>

  <x-ui.input maxlength="1000" :label="__('corpus.work.title_translation')"
              :value="old('title_translation', $resource->title_translation ?? request('title_translation'))"
              name="title_translation"/>

  <div class="grid grid-cols-2 gap-2">
    <x-ui.input :label="__('corpus.work.short_title')" :value="old('short_title', $resource->short_title ?? null)"
                name="short_title"/>


    <div class="flex items-end">
      <div class="flex-grow">
        <x-ui.input :label="__('corpus.work.issuing_authority')"
                    :value="old('issuing_authority', $resource->issuing_authority ?? null)" name="issuing_authority"/>
      </div>
      <div class="flex-shrink-0">
        <x-ui.dropdown position="left">
          <x-slot:trigger>
            <button type="button" class="pl-4 py-2 hover:text-primary">
              <x-ui.icon name="box-archive" :size="6"/>
            </button>
          </x-slot:trigger>

          <div class="px-4 py-2 w-80 max-w-screen-90">
            <x-lookups.canned-response-selector
                field="issuing_authority"
                name="canned"
                @change="document.querySelector('#issuing_authority').value = $el.value;open = false;"
            />
          </div>
        </x-ui.dropdown>
      </div>
    </div>

    <x-corpus.work.work-type-selector
      required
      :label="__('corpus.work.work_type')"
      :value="old('work_type', $resource->work_type ?? null)"
      name="work_type"
    />

    <x-corpus.work.work-status-selector required :label="__('corpus.work.status')"
                                        :value="old('status', $resource->status ?? null)" name="status"/>

    <x-geonames.location.location-selector
        required
        name="primary_location_id"
        :value="old('primary_location_id', $resource->primary_location_id ?? null)"
        :label="__('corpus.work.primary_jurisdiction')"
        :location="old('primary_location_id', $resource->primary_location_id ?? null) ? Location::find(old('primary_location_id', $resource->primary_location_id ?? null)) : null"
        :route="route('collaborate.locations.json.index')"/>


    <x-arachno.source.source-selector :label="__('corpus.work.source')"
                                      :value="old('source_id', $resource->source_id ?? request('source_id'))" name="source_id"
                                      required/>

    @can('collaborate.corpus.work.set-organisation')
      <x-customer.organisation.organisation-selector
        :label="__('corpus.work.types.site_specific')"
        :title="$resource->organisation->title ?? null"
        :id="$resource->organisation->id ?? null"
        :required="false"
      />
    @endcan
  </div>

  <x-ui.input :label="__('corpus.work.source_url')"
              :value="old('source_url', $resource->source_url ?? request('source_url'))" name="source_url"/>

  <div class="grid grid-cols-2 gap-2">
    <x-ui.language-selector required name="language_code"
                            :value="old('language_code', $resource->language_code ?? null)"
                            :label="__('corpus.work.language')" required/>

    <x-ui.input :label="__('corpus.work.catalogue_work_id')"
                :value="old('catalogue_work_id', $resource->catalogue_work_id ?? null)" name="catalogue_work_id"/>

    <x-ui.input required :label="__('corpus.work.work_number')"
                :value="old('work_number', $resource->work_number ?? null)" name="work_number"/>

    <x-ui.input :label="__('corpus.work.gazette_number')"
                :value="old('gazette_number', $resource->gazette_number ?? null)" name="gazette_number"/>

    <x-ui.input :label="__('corpus.work.notice_number')" :value="old('notice_number', $resource->notice_number ?? null)"
                name="notice_number"/>

    <x-ui.input required type="date" :label="__('corpus.work.publication_date')"
                :value="old('work_date', $resource->work_date?->toDateString())" name="work_date"/>

    <x-ui.input type="date" :label="__('corpus.work.effective_date')"
                :value="old('effective_date', $resource->effective_date?->toDateString())" name="effective_date"/>

    <x-ui.input type="date" :label="__('corpus.work.repealed_date')"
                :value="old('repealed_date', $resource->repealed_date?->toDateString())" name="repealed_date"/>

    <x-ui.input type="date" :label="__('corpus.work.comment_date')"
                :value="old('comment_date', $resource->comment_date?->toDateString())" name="comment_date"/>
  </div>

  <x-ui.textarea name="highlights" wysiwyg="basic" :label="__('corpus.work.highlights')"
                 :value="old('highlights', $resource->highlights ?? null)"/>

  <x-ui.textarea name="update_report" wysiwyg="basic" :label="__('corpus.work.summary_of_highlights')"
                 :value="old('update_report', $resource->update_report ?? null)"/>

  <x-ui.textarea name="changes_to_register" wysiwyg="basic" :label="__('corpus.work.changes_to_register')"
                 :value="old('changes_to_register', $resource->changes_to_register ?? null)"/>

  <x-lookups.canned-response-selector field="changes_to_register"
                                      @change="tinymce.get('changes_to_register').setContent('<p>' + $el.value + '</p>');"/>
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
