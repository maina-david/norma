<x-ui.form method="post" action="{{ route('collaborate.docs-for-update.notification-status.store', ['doc' => $resource->id]) }}" class="p-4">
  <x-ui.input
      required
      type="select"
      :options="$statuses"
      :label="__('corpus.doc.notification_status')"
      :value="old('notification_status', $resource->updateCandidate->notification_status ?? null)"
      name="notification_status"
  ></x-ui.input>

  <x-ui.textarea
      name="notification_status_reason"
      wysiwyg="basic"
      :label="__('corpus.doc.reason')"
      :value="old('notification_status_reason', $resource->updateCandidate->notificationStatusReason->text ?? null)"
  />

  <div>
    <x-lookups.canned-response-selector
        field="notification_status_reason"
        @change="tinymce.get('notification_status_reason').setContent('<p>' + $el.value + '</p>');"
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

