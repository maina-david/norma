<x-ui.input :value="old('name', $resource->title ?? '')" name="title" label="{{ __('collaborators.team.title') }}"
            required />


<x-geonames.currency-selector :value="old('currency', $resource->currency ?? '')" name="currency"
                              label="{{ __('collaborators.team.currency') }}" required />



@include('partials.collaborators.collaborate.team.bank-details-form')


<x-ui.input :value="old('name', $resource->transferwise_id ?? '')" name="transferwise_id"
            label="{{ __('collaborators.team.transferwise_id') }}" />


<div class="mt-4">
  <x-ui.input type="checkbox" :value="old('name', $resource->auto_pay ?? '')" name="auto_pay"
              label="{{ __('collaborators.team.auto_pay') }}" />
</div>


<x-slot name="footer">
  <div></div>
  <div>
    <x-ui.back-button :fallback="route('collaborate.teams.index')" />
    <x-ui.button type="submit" theme="primary">{{ __('actions.save') }}</x-ui.button>
  </div>
</x-slot>
