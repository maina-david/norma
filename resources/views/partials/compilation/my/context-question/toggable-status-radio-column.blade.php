<div class="w-full flex justify-center" id="context-answer-{{ $question->id }}-{{ $value }}">
  <x-ui.form method="PUT" action="{{ route('my.context-questions.libryo.answer', ['libryo' => $answer->place_id, 'question' => $question->id]) }}">
    <div class="flex space-x-8 items-center">
      <div class="flex items-center">
        <div class="pb-1">
          @include('pages.compilation.my.context-question.answer-turbo-frame', [
            'forAnswer' => $value,
            'question' => $question,
            'checked' => $answer->answer == $value
          ])
        </div>
      </div>
    </div>
  </x-ui.form>
</div>
