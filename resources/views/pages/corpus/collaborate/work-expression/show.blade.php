<div>
  <x-ui.show-field :label="__('corpus.work_expression.start_date')" :value="$resource->start_date ?? null" name="start_date" />

  <div class="break-all">
    <x-ui.show-field :label="__('corpus.work_expression.source_url')" :value="$resource->source_url ?? null" name="source_url" />
  </div>
  <div class="mt-4">
    <x-ui.show-field type="checkbox" :label="__('corpus.work_expression.show_source_document')" :value="$resource->show_source_document ?? false" name="show_source_document" />
  </div>
</div>


<x-slot name="footer">
  <div></div>
  <div>
    <x-ui.back-button :fallback="route('collaborate.works.index')" />
    <x-ui.button type="submit" theme="primary">{{ __('actions.save') }}</x-ui.button>
  </div>
</x-slot>
