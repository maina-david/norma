<x-ui.form method="POST" action="{{ route('my.references.for.assessment-item-response.store', ['response' => $response->id]) }}" x-data="{}">
  <div class="text-lg font-semibold">{{ __('assess.assessment_item_response.link_requirement') }}</div>

  <x-ui.input
    data-tomselect-no-reinitialise
    type="select"
    :options="$works"
    name="work_id"
    label="{{ __('assess.assessment_item_response.select_requirement_document') }}"
    @change="if ($el.selectedOptions[0].value) {document.querySelector('#assessment-item-response-{{ $response->id }}-work-requirements').setAttribute('src', '{{ route('my.references.for.assessment-item-response.work.create', ['response' => $response->id, 'work' => '000000']) }}'.replace('000000', $el.selectedOptions[0].value))}"
  />

  <x-turbo::frame id="assessment-item-response-{{ $response->id }}-work-requirements">
  </x-turbo::frame>

</x-ui.form>
