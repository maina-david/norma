<div
    x-data="{
    handleChange: function(event) {
      var row = event.target.closest('tr');
      if (row) {
        Array.from(row.querySelectorAll('input[type=radio]:not([id$={{ '"' . $forAnswer . '"' }}])'))
          .forEach(function (input) {
            input.checked = false;
          });
      }
      event.target.closest('form').requestSubmit();
    }
  }"
>
  @if ($markUnchanged ?? false)
    <x-ui.button @click="open = true;" size="xs" theme="primary" styling="outline">
      {{ __('assess.assessment_item_response.mark_unchanged') }}
    </x-ui.button>
  @else
    <input
      {!! isset($checked) && $checked ? '@click="$event.preventDefault();"' : '@change="handleChange($event)"' !!}
      class="libryo-radio text-primary focus:ring-primary cursor-pointer"
      type="radio"
      name="answer"
      id="answer_{{ $question->id }}_{{ $forAnswer }}"
      value="{{ $forAnswer }}"
      {{ isset($checked) && $checked ? 'checked' : '' }}
    />
  @endif
</div>
