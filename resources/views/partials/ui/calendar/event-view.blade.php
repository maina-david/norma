<div class="flex items-center text-xs rounded-xl text-white px-2 py-1 {{ $event->getCalendarEventBackground() }}">
  @if($event->isCalendarEventOverdue())
    <div class="rounded-full h-3 w-3 bg-negative flex-shrink-0"></div>
  @endif
  <a
    class="flex-grow ml-2 whitespace-nowrap w-full overflow-hidden text-ellipsis tippy"
    data-tippy-content="{{ $event->getCalendarEventTitle() }}"
    href="{{ $event->getCalendarEventTarget($eventRoute) }}"
  >
    {{ $event->getCalendarEventTitle() }}
  </a>
</div>
