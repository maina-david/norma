@php
use App\Enums\Notifications\NotificationType;
@endphp

<li>
  <div class="relative pb-8">
    <span class="absolute top-5 left-5 -ml-px h-full w-0.5 bg-norma-gray-200" aria-hidden="true"></span>
    <div class="relative flex items-start space-x-3">
      @if ($showAvatar($notification))
        <div class="relative">
          <x-ui.user-avatar :user="$user" />
          <span class="absolute -bottom-0.5 -right-1 bg-white rounded-tl px-0.5 py-px">
            {{-- Notification Type Icon --}}
            <x-ui.icon :name="$getIcon($notification)" />
          </span>
        </div>
      @else
        <div>
          <div class="relative px-1">
            <div class="h-8 w-8 bg-norma-gray-100 rounded-full ring-8 ring-white flex items-center justify-center">
              <x-ui.icon :name="$getIcon($notification)" />
            </div>
          </div>
        </div>
      @endif
      <div class="min-w-0 flex-1 mb-8">
        <div>
          <div class="text-sm">
            @if ($link = $getLink($notification))
              <a data-turbo-frame="_top" href="{{ $link }}" class="font-medium text-primary">
                {{ $getTitle($notification) }}
              </a>
            @else
              <p class="font-medium text-norma-gray-900">
                {{ $getTitle($notification) }}
              </p>
            @endif

          </div>
          <p class="mt-0.5 text-xs text-norma-gray-500">
            <x-ui.timestamp type="diff" :timestamp="$notification->created_at" />
          </p>
        </div>
        <div class="mt-2 text-sm text-norma-gray-700">
          @if ($notification->isForTask())
            <p class="italic">
              {{ $notification->presentViewData['body']['task']->title ?? __('notifications.deleted_task') }}
            </p>
          @endif

          @if ($type && NotificationType::taskAssigneeChanged()->is($type))
            <p class="italic font-medium">
              {{ __('tasks.assignee_changed', ['name' => $notification->presentViewData['body']['changed_by'] ?? '','from' => $notification->presentViewData['body']['from_user'] ?? '','to' => $notification->presentViewData['body']['to_user'] ?? '']) }}
            </p>
          @elseif($type && NotificationType::taskStatusChanged()->is($type))
            <p class="italic font-medium">
              {{ __('tasks.status_changed', ['name' => $notification->presentViewData['body']['changed_by'] ?? '','from' => $notification->presentViewData['body']['task_status_from'] ?? '','to' => $notification->presentViewData['body']['task_status_to'] ?? '']) }}
            </p>
          @elseif($type && NotificationType::taskPriorityChanged()->is($type))
            <p class="italic font-medium">
              {{ __('tasks.priority_changed', ['name' => $notification->presentViewData['body']['changed_by'] ?? '','from' => $notification->presentViewData['body']['task_priority_from'] ?? '','to' => $notification->presentViewData['body']['task_priority_to'] ?? '']) }}
            </p>
          @elseif($type && NotificationType::taskCompleted()->is($type))
            <p class="italic font-medium">
              {{ __('tasks.task_marked_complete') }}
            </p>
          @elseif($type && NotificationType::taskTitleChanged()->is($type))
            <p class="italic font-medium">
              {{ __('tasks.title_changed', ['name' => $notification->presentViewData['body']['changed_by'] ?? '','from' => $notification->presentViewData['body']['from_title'] ?? '','to' => $notification->presentViewData['body']['to_title'] ?? '']) }}
            </p>
          @else
            <p></p>
          @endif
        </div>
      </div>
    </div>
  </div>
</li>
