<div class="w-full flex justify-center">
  <input
    class="libryo-radio text-primary focus:ring-primary cursor-pointer"
    type="radio"
    name="answer_{{ $id }}"
    id="answer_{{ $id }}"
    value="{{ $id }}"
    {{ $checked ? 'checked' : '' }}
    disabled
  />
</div>
