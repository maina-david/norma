<x-ui.input
    type="select"
    :options="$references"
    name="reference_id"
    label="{{ __('assess.assessment_item_response.select_requirement') }}"
/>


<div class="flex justify-end mt-4">
  <x-ui.button type="submit" theme="primary" styling="outline">
    {{ __('actions.save') }}
  </x-ui.button>
</div>
