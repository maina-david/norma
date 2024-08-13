<div>
  <x-ui.input type="date" :label="__('corpus.work_expression.start_date')" :value="old('start_date', $resource->start_date ?? null)" name="start_date" />

  <x-ui.input :label="__('corpus.work_expression.source_url')" :value="old('source_url', $resource->source_url ?? null)" name="source_url" />

  <x-ui.file-selector name="file" />

  <div class="mt-4">
    <x-ui.input type="checkbox" :label="__('corpus.work_expression.show_source_document')" :value="old('show_source_document', $resource->show_source_document ?? null)" name="show_source_document" />
  </div>
</div>


<x-slot name="footer">
  <div></div>
  <div>
    <x-ui.back-button :fallback="route('collaborate.works.index')" />
    <x-ui.button type="submit" theme="primary">{{ __('actions.save') }}</x-ui.button>
  </div>
</x-slot>
