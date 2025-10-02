@php
use App\Enums\System\SystemNotificationAlertType;
@endphp

@if ($shouldShow)
  @if ($systemNotification->type === SystemNotificationAlertType::modal()->value)
    <x-ui.modal x-init="open = true" :closable="false">
      <x-slot name="trigger"></x-slot>
      <div class="w-screen-50">
        <div class="text-xl font-semibold">{{ $systemNotification->title }}</div>
        <div class="text-baseline text-norma-gray-700 mt-10">{{ $systemNotification->content }}</div>
        <div class="flex justify-between mt-10">
          <div>
            @if ($systemNotification->user_action_link)
              <x-ui.button theme="primary">{{ $systemNotification->user_action_text }}</x-ui.button>
            @endif
          </div>
          <div>
            @include(
                'partials.system.my.system-notification.close-button',
                ['systemNotification' => $systemNotification]
            )
          </div>
        </div>
      </div>
    </x-ui.modal>
  @else
    <div class="fixed bottom-0 left-0 right-0 p-16 bg-secondary text-white z-40">
      <div class="text-xl font-semibold">{{ $systemNotification->title }}</div>
      <div class="flex justify-between mt-3">
        <div class="text-baseline">{{ $systemNotification->content }}</div>
        <div class="flex justify-between">
          @if ($systemNotification->user_action_link)
            <x-ui.button type="link" href="{{ $systemNotification->user_action_link }}" styling="outline">
              {{ $systemNotification->user_action_text }}</x-ui.button>
          @endif

          <div class="ml-5">
            @include(
                'partials.system.my.system-notification.close-button',
                ['systemNotification' => $systemNotification]
            )
          </div>
        </div>
      </div>
    </div>
  @endif
@endif
