<x-layouts.app>
  <x-slot name="header">
    <a href="{{ route('my.assess.assessment-item-responses.index') }}" class="text-primary mr-3">
      <x-ui.icon name="chevron-left" />
    </a>
    {{ __('assess.assessment_item_response.answer_in_bulk') }}:
    {{ __('assess.assessment_item_response.response_justification') }}
  </x-slot>

  <div class="max-w-screen-md mx-auto">
    <x-ui.alert-box type="warning" class="mb-5">
      {{ $answerText }}:
      {{ trans_choice('interface.items_selected', count($responseIds), ['value' => count($responseIds)]) }}
    </x-ui.alert-box>
    <x-ui.alert-box class="mb-5">
      {{ __('assess.assessment_item_response.response_justification_info') }}
    </x-ui.alert-box>

    <x-ui.form method="PUT"
               :action="route('my.assess.assessment-item-responses.bulk.answer.update')">
      <x-ui.input required type="textarea" name="notes" />

      <x-ui.button type="submit" theme="primary" class="mt-5 float-right">
        {{ __('actions.save') }}
      </x-ui.button>
    </x-ui.form>

  </div>

</x-layouts.app>
