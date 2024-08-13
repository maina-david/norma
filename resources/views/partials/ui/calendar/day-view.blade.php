
<div class="border border-libryo-gray-200 -mt-px -ml-px h-40">
  <div class="w-full h-full" id="calendar-{{ $day }}">
    <div class="w-full h-full p-2 {{ $dayInMonth ? ($isToday ? 'bg-primary' : ' bg-white ') : 'bg-libryo-gray-100' }} flex flex-col">

      <div class="flex items-center">
        <p class="text-sm {{ $dayInMonth ? ' font-medium ' : '' }}">
          {{ $day->format('j') }}
        </p>
        <p class="text-xs text-libryo-gray-600 ml-4">
          @if($events->isNotEmpty())
            {{ $events->count() }} {{ Str::plural($eventCountLabel, $events->count()) }}
          @endif
        </p>
      </div>

      {{-- Events --}}
      <div class="p-2 my-2 flex-1 overflow-y-auto">
        <div class="grid grid-cols-1 grid-flow-row gap-2">
          @foreach($events as $event)
            <div>
              @include('partials.ui.calendar.event-view', ['event' => $event])
            </div>
          @endforeach
        </div>
      </div>

    </div>
  </div>
</div>
