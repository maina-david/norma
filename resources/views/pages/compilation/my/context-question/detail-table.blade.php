@php
  use App\Enums\Compilation\ContextQuestionAnswer;
@endphp

<table class="w-full sm:-m-6">
  <tr class="border-b">
    <x-ui.td>{{ __('assess.assessment_item_response.current_answer') }}</x-ui.td>
    <x-ui.td class="text-norma-gray-600">
      <x-ui.form method="PUT" action="{{ route('my.context-questions.norma.answer', ['norma' => $norma->id, 'question' => $question->id]) }}">
        <div class="flex space-x-8 items-center">
          @foreach(ContextQuestionAnswer::forSelector() as $status)
            <div class="flex items-center">
              <div class="pb-1">
                @include('pages.compilation.my.context-question.answer-turbo-frame', [
                  'forAnswer' => $status['value'],
                  'question' => $question,
                  'checked' => $answer->answer == $status['value'],
                ])
              </div>

              <x-ui.label for="answer_{{ $question->id }}_{{ $status['value'] }}" class="ml-2">
                <span class="text-norma-gray-500">{{ $status['label'] }}</span>
              </x-ui.label>
            </div>
          @endforeach
        </div>
      </x-ui.form>
    </x-ui.td>
  </tr>

  <tr class="border-b">
    <x-ui.td>{{ __('assess.assessment_item.last_answered') }}</x-ui.td>
    <x-ui.td>
      @if($answer->last_answered_by)
        <span class="text-norma-gray-500">{{ $answer->lastAnsweredBy->full_name ?? '-' }},</span>
      @else
        -
      @endif

      @if($answer->last_answered_at)
        <span class="text-norma-gray-500">
          <x-ui.timestamp :timestamp="$answer->last_answered_at->timestamp" />
        </span>
      @endif
    </x-ui.td>
  </tr>
</table>
