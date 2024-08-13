<x-ui.input type="select" :label="__('lookups.canned_response.for_field')"
            :value="old('for_field', $resource->for_field ?? '')" name="for_field"
            :options="\App\Enums\Lookups\CannedResponseField::lang()"
            required />

<x-ui.input type="textarea" :label="__('lookups.canned_response.response')"
            rows="5"
            :value="old('response', $resource->response ?? '')"
            name="response"
            required />


<x-slot name="footer">
  <div></div>
  <div>
    <x-ui.back-button :fallback="route('collaborate.tags.index')" />
    <x-ui.button type="submit" theme="primary">{{ __('actions.save') }}</x-ui.button>
  </div>
</x-slot>
