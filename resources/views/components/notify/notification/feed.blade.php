<div>
  @if ($notifications->isEmpty())
    <x-ui.empty-state-icon icon="sunglasses"
                           :title="__('notify.notification.all_caught_up')" />
  @else
    @foreach ($notifications as $notification)
      <ul role="list" class="-mb-8">
        <x-notify.notification.feed-item :notification="$notification" :user="$user" />
      </ul>
    @endforeach

    @if ($unread)
      {{-- We mark the notifications as read, so a paginator doesn't work here --}}
      <a class="block mt-10 text-primary"
         href="{{ route('my.notify.notifications.index', ['readUnread' => 'unread']) }}">
        {{ __('interface.load_more') }}
      </a>
    @else
      <div class="mt-12">
        {{ $notifications->links() }}
      </div>
    @endif

  @endif

</div>
