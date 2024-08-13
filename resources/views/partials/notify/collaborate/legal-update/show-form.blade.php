<div>
  <x-ui.show-field :value="$resource->title" :label="__('interface.title')" />

  <x-ui.show-field :value="$resource->title_translation" :label="__('corpus.work.title_translation')" />

  <x-ui.show-field :value="$resource->notifiable->title ?? null" :label="__('notify.legal_update.published_in_terms_of')" />

  <x-ui.show-field :value="$resource->notifyReference?->refPlainText?->plain_text" :label="__('notify.legal_update.notify_against')" />

  <x-ui.show-field :value="$resource->language_code" :label="__('notify.legal_update.language')" />

  <x-ui.show-field :value="$resource->source->title ?? null" :label="__('corpus.work.source')" />

  <x-ui.show-field :label="__('notify.legal_update.primary_jurisdiction')">
    @if ($resource->primaryLocation)
      <div class="flex">
        <div class="h-8 w-8 mr-5">
          <x-ui.country-flag :country-code="$resource->primaryLocation->flag"
                             class="h-8 w-8 rounded-full tippy"
                             data-tippy-content="{{ $resource->primaryLocation->title }}" />
        </div>
        {{ $resource->primaryLocation?->title }}
      </div>
    @else
      -
    @endif
  </x-ui.show-field>
  <x-ui.show-field :value="$resource->work_number" :label="__('notify.legal_update.work_number')" />
  <x-ui.show-field :value="$resource->publication_number" :label="__('notify.legal_update.gazette_number')" />
  <x-ui.show-field :value="$resource->publication_document_number" :label="__('notify.legal_update.notice_number')" />
  <x-ui.show-field type="date" :value="$resource->publication_date" :label="__('notify.legal_update.publication_date')" />
  <x-ui.show-field type="date" :value="$resource->effective_date" :label="__('notify.legal_update.effective_date')" />

</div>
