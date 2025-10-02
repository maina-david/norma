<!-- bg-norma-gray-600 bg-positive bg-warning -->
<div class="h-full" x-data="{}">
  <div class="flex flex-col">
    <div class="flex-shrink-0 py-4 px-4 font-semibold text-2xl flex flex-col md:flex-row justify-between">
      <div class="text-center md:text-left hidden md:block">{{ $startsAt->format('F Y') }}</div>
      <form action="{{ route($route, $routeParams) }}" class="flex justify-center">
          <div class="flex flex-col md:flex-row items-center md:space-x-4 max-w-sm w-screen">
            @foreach($filters as $key => $value)
              @if(!in_array($key, ['show-month', 'show-year']))
                @if(is_array($value))
                  @foreach($value as $item)
                    <input type="hidden" name="{{ $key }}[]" value="{{ $item }}">
                  @endforeach
                @else
                  <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                @endif
              @endif
            @endforeach
            <div class="md:flex-grow w-full md:w-auto">
              <x-ui.input
                name="show-month"
                type="select"
                :value="$startsAt->month"
                :options="$monthOptions"
                :tomselect="false"
                @change="$event.target.closest('form').requestSubmit()"
              />
            </div>

            <div class="md:flex-grow w-full md:w-auto">
              <x-ui.input
                name="show-year"
                type="select"
                :value="$startsAt->year"
                :options="$yearOptions"
                :tomselect="false"
                @change="$event.target.closest('form').requestSubmit()"
              />
            </div>
          </div>
        </form>
    </div>

    <div class="flex-grow overflow-x-auto w-full">
      <div class="inline-block min-w-full overflow-hidden">
        <div class="w-full grid grid-cols-7">
          @foreach($monthGrid->take(7) as $day)
            <div class="text-sm font-semibold py-2 border -ml-px text-center bg-white text-norma-gray-900">
              {{ $day->format('l') }}
            </div>
          @endforeach
        </div>

        <div class="w-full grid grid-cols-7">
          @foreach($monthGrid as $day)
            @include('partials.ui.calendar.day-view', [
              'day' => $day,
              'dayInMonth' => $day->isSameMonth($startsAt),
              'isToday' => $day->isToday(),
              'events' => $getEventsForDay($day, $events),
            ])
          @endforeach
        </div>
      </div>
    </div>
  </div>
</div>
