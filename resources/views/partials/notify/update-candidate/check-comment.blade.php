<x-ui.form method="post" action="{{ route('collaborate.docs-for-update.check.store', ['doc' => $resource->id]) }}" class="p-4">

  <x-ui.input
      name="check_sources_status"
      label="{{ __('corpus.doc.notification_required') }}"
      type="select"
      :options="[1 => __('interface.yes'), 0 => __('interface.no')]"
      :value="(int) old('check_sources_status', $resource->updateCandidate->check_sources_status ?? 0)"
  />

  <x-ui.textarea
      name="checked_comment"
      wysiwyg="basic"
      :label="__('corpus.doc.checked_comment')"
      :value="old('checked_comment', $resource->updateCandidate->checked_comment ?? null)"
  />

  <div>
    <x-lookups.canned-response-selector
        field="checked_comment"
        @change="tinymce.get('checked_comment').setContent('<p>' + $el.value + '</p>');"
    />
  </div>

  <x-slot name="footer">
    <div></div>
    <div>
      <x-ui.button type="button" theme="secondary" @click="open = false">{{ __('actions.cancel') }}</x-ui.button>
      <x-ui.button type="submit" theme="primary">{{ __('actions.save') }}</x-ui.button>
    </div>
  </x-slot>
</x-ui.form>

