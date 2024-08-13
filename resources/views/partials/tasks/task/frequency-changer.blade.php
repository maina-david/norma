@php
  use App\Enums\Tasks\TaskRepeatInterval;
@endphp
@if($canUpdate)
<x-ui.dropdown>
  <x-slot:trigger>
    <div class="text-primary underline cursor-pointer">
      @if($task->frequency)
        {{ $task->frequency }} {{ $task->frequency_interval->label() }}
      @else
        -
      @endif
    </div>
  </x-slot:trigger>

  <div class="px-4 py-2 w-screen-75 max-w-xs relative">
    <x-ui.form method="PUT" action="{{ route('my.tasks.tasks.update', ['task' => $task->id]) }}">
      <div class="flex">
        <div>
          <x-ui.input
              type="number"
              min="1"
              name="frequency"
              label="{{ __('assess.assessment_item.repeats_every') }}"
              value="{{ $task->frequency }}"
              class="w-24"
          />
        </div>

        <div class="flex-grow pl-2">
          <x-ui.input
              :tomselect="false"
              :options="TaskRepeatInterval::forSelector()"
              type="select"
              name="frequency_interval"
              label="&nbsp;"
              :value="old('frequency_interval', $task->frequency_interval->value ?? TaskRepeatInterval::MONTH->value)"
          />
        </div>
      </div>

      <div class="mt-4">

        <x-ui.button theme="primary" type="submit">
          {{ __('actions.save') }}
        </x-ui.button>
      </div>
    </x-ui.form>

    @if($task->frequency && $task->frequency > 0)
      <div class="mt-2 flex justify-end absolute bottom-2 right-4">
        <x-ui.form method="PUT" action="{{ route('my.tasks.tasks.update', ['task' => $task->id]) }}">
          <input type="hidden" name="frequency" value="">
          <x-ui.button theme="negative" type="submit">
            {{ __('actions.clear') }}
          </x-ui.button>
        </x-ui.form>
      </div>
    @endif
  </div>
</x-ui.dropdown>

@else
  <div>
    @if($task->frequency)
      {{ $task->frequency }} {{ $task->frequency_interval->label() }}
    @else
      -
    @endif
  </div>
@endif

