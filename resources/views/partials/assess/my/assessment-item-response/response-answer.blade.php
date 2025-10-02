@if(!($response->assessmentItem->deleted_at ?? false))
  <div x-data="">
    <x-ui.modal>
      <x-slot name="trigger">
        @if (isset($markUnchanged) && $markUnchanged)
          <x-ui.button @click="open = true;" size="xs" theme="primary" styling="outline">
            {{ __('assess.assessment_item_response.mark_unchanged') }}
          </x-ui.button>
        @else
          <input {!! isset($checked) && $checked ? '@click="$event.preventDefault();"' : '@click="$event.preventDefault(); open = true;"' !!}
                 class="norma-radio text-primary focus:ring-primary cursor-pointer"
                 type="radio"
                 name="answer_{{ $response->id }}"
                 id="answer_{{ $response->id }}_{{ $forAnswer }}"
                 value="{{ $forAnswer }}"
              {{ isset($checked) && $checked ? 'checked' : '' }} />
        @endif
      </x-slot>

      <div class="max-w-screen-90 md:max-w-screen-50">
        <turbo-frame id="assessment-item-response-{{ $response->id }}-answer-form-{{ $forAnswer }}" loading="lazy"
                     src="{{ route('my.assess.assessment-item-responses.answer.form', ['response' => $response->id,'forAnswer' => $forAnswer]) }}">
          <div class="w-96">
            <x-ui.skeleton class="h-96 mt-5" />
          </div>
        </turbo-frame>
      </div>
    </x-ui.modal>
  </div>

@endif
