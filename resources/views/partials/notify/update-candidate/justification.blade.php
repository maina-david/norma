<x-ui.form method="post" action="{{ route('collaborate.docs-for-update.justification.store', ['doc' => $resource->id]) }}" class="p-4">
  <x-ui.input
    name="justification_status"
    label="{{ __('corpus.doc.notification_required') }}"
    type="select"
    :options="[1 => __('interface.yes'), 0 => __('interface.no')]"
    :value="(int) old('justification_status', $resource->updateCandidate->justification_status ?? 0)"
  />

  <x-ui.textarea
      name="justification"
      wysiwyg="basic"
      :label="__('corpus.doc.justification')"
      :value="old('justification', $resource->updateCandidate->justification ?? null)"
  />

  <div>
    <x-lookups.canned-response-selector
        field="justification"
        @change="tinymce.get('justification').setContent('<p>' + $el.value + '</p>');"
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

