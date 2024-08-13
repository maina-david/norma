<div class="text-lg">
  {{ __('assess.assessment_item_response.response_justification') }}
</div>

@if(!$answered)
  <x-ui.alert-box type="negative" class="mt-3 mb-5">
    {{ __('assess.assessment_item_response.response_draft_warning') }}
  </x-ui.alert-box>
@endif

<x-ui.alert-box class="mt-3 mb-5">
  {{ __('assess.assessment_item_response.response_justification_info') }}
</x-ui.alert-box>

<x-ui.form id="assess-response-form-{{ $response->id }}-{{ $forAnswer }}" method="put"
           :action="route('my.assess.assessment-item-responses.answer', ['response' => $response->id])">
  <input type="hidden" name="answer" value="{{ $forAnswer }}" />
  <x-ui.input required type="textarea" name="notes" />

  <div class="mt-5 grid justify-items-end">
    <x-ui.button theme="primary" type="submit" form="assess-response-form-{{ $response->id }}-{{ $forAnswer }}">
      {{ __('actions.save') }}
    </x-ui.button>
  </div>
</x-ui.form>
