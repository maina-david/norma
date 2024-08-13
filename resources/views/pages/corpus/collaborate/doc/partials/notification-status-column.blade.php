@php
  use App\Enums\Notify\NotificationStatus;
@endphp
<div id="doc-notification-status-{{ $row->id }}" class="relative group">

  @if($row->updateCandidate)
    <div class="mb-4">{{ NotificationStatus::lang()[$row->updateCandidate->notification_status] ?? '-' }}</div>
    <div class="italic text-xs w-96">
      <x-doc.view-more>
        {!! $row->updateCandidate->notificationStatusReason->text ?? '' !!}
      </x-doc.view-more>
    </div>

    @can('collaborate.corpus.doc.update-candidate.set-notification-status')
      <x-ui.modal class="mt-2">
        <x-slot:trigger>
          <div>
            <x-ui.button styling="outline" theme="primary" @click="open = true">
              {{ __('corpus.doc.update') }}
            </x-ui.button>
          </div>
        </x-slot:trigger>

        <div class="w-screen-75 max-w-4xl">
          <x-turbo::frame
              loading="lazy"
              id="doc-notification-status-{{ $row->id }}-form"
              src="{{ route('collaborate.docs-for-update.notification-status.create', ['doc' => $row->id]) }}"
          >
            <x-ui.skeleton/>
          </x-turbo::frame>
        </div>
      </x-ui.modal>
    @endcan

  @endif

</div>

