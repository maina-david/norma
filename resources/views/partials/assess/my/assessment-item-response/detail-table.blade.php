@php
  use App\Enums\Assess\ReassessInterval;use App\Enums\Assess\ResponseStatus;
  use App\Enums\Assess\RiskRating;
@endphp

<table class="w-full sm:-m-6">
  <tr class="border-b">
    <x-ui.td>{{ __('assess.assessment_item_response.current_answer') }}</x-ui.td>
    <x-ui.td class="text-libryo-gray-600">
      <div class="flex space-x-8 items-center">
        @foreach(ResponseStatus::forSelector() as $status)
          @if($status['value'] != ResponseStatus::draft()->value)

            <div class="flex items-center">
              <div class="pb-1">
                @include('partials.assess.my.assessment-item-response.response-turbo-frame', [
                  'forAnswer' => $status['value'],
                  'response' => $response,
                  'checked' => $response->answer == $status['value']
                ])
              </div>

              <x-ui.label for="answer_{{ $response->id }}_{{ $status['value'] }}" class="ml-2">
                <span class="text-libryo-gray-500">{{ $status['label'] }}</span>
              </x-ui.label>
            </div>

          @endif
        @endforeach

        @if(!ResponseStatus::draft()->is($response->answer))
          <div class="flex items-center">
            <div class="pb-1">
              @include('partials.assess.my.assessment-item-response.response-turbo-frame', [
                'response' => $response,
                'forAnswer' => 'unchanged',
                'markUnchanged' => true,
              ])
            </div>
          </div>
        @endif

      </div>
    </x-ui.td>
  </tr>

  <tr class="border-b">
    <x-ui.td>{{ __('assess.risk_rating') }}</x-ui.td>
    <x-ui.td>
      <span class="text-libryo-gray-500">{{ RiskRating::fromValue($response->assessmentItem->risk_rating)->label() }}</span>
    </x-ui.td>
  </tr>

  <tr class="border-b" id="response_{{ $response->hash_id }}_frequency">
    <x-ui.td>{{ __('assess.assessment_item.repeats_every') }}</x-ui.td>
    <x-ui.td>
      <x-ui.dropdown>
        <x-slot:trigger>
          <div class="text-primary underline cursor-pointer">
            @if($response->frequency)
              {{ $response->frequency }} {{ $response->frequency_interval->label() }}
            @else
              -
            @endif
          </div>
        </x-slot:trigger>

        <div class="px-4 py-2 w-screen-75 max-w-xs">
          <x-ui.form method="PUT"
                     action="{{ route('my.assess.assessment-item-responses.frequency', ['response' => $response->hash_id]) }}">
            <div class="flex">
              <div>
                <x-ui.input
                    type="number"
                    min="1"
                    name="frequency"
                    label="{{ __('assess.assessment_item.repeats_every') }}"
                    value="{{ $response->frequency }}"
                    class="w-24"
                />
              </div>

              <div class="flex-grow pl-2">
                <x-ui.input
                    :tomselect="false"
                    :options="ReassessInterval::forSelector()"
                    type="select"
                    name="frequency_interval"
                    label="&nbsp;"
                    :value="old('frequency_interval', $response->frequency_interval->value ?? ReassessInterval::MONTH->value)"
                />
              </div>
            </div>

            <div class="mt-4">
              <x-ui.button theme="primary" type="submit">
                {{ __('actions.save') }}
              </x-ui.button>
            </div>
          </x-ui.form>
        </div>
      </x-ui.dropdown>
    </x-ui.td>
  </tr>

  <tr class="border-b">
    <x-ui.td>{{ __('assess.assessment_item.next_due_date') }}</x-ui.td>
    <x-ui.td>

      <x-ui.dropdown>
        <x-slot:trigger>
          <div class="text-primary underline cursor-pointer">
            <span class="text-primary">{{ $response->next_due_at?->format('d M Y') ?? '-' }}</span>
          </div>
        </x-slot:trigger>

        <div class="px-4 py-2 w-screen-75 max-w-xs">
          <x-ui.form method="PUT"
                     action="{{ route('my.assess.assessment-item-responses.next-due', ['response' => $response->hash_id]) }}">
            <x-ui.input
                type="date"
                name="next_due_at"
                label="{{ __('assess.assessment_item.next_due_date') }}"
            />

            <div class="mt-4">
              <x-ui.button theme="primary" type="submit">
                {{ __('actions.save') }}
              </x-ui.button>
            </div>
          </x-ui.form>
        </div>
      </x-ui.dropdown>

    </x-ui.td>
  </tr>

  <tr class="border-b">
    <x-ui.td>{{ __('assess.assessment_item.last_answered') }}</x-ui.td>
    <x-ui.td>
      @if($response->answered_at)
        <span class="text-libryo-gray-500">{{ $response->lastAnsweredBy->full_name ?? '-' }},</span>
        <span class="text-libryo-gray-500">{{ $response->answered_at?->format('d M Y') ?? '' }}</span>
      @else
        -
      @endif
    </x-ui.td>
  </tr>

  <tr class="border-b">
    <x-ui.td>{{ __('tasks.created_by') }}</x-ui.td>
    <x-ui.td>
      <span class="text-libryo-gray-500">{{ $response->assessmentItem->author->full_name ?? '-' }}</span>
    </x-ui.td>
  </tr>
</table>
