<x-ui.input :value="old('title', $resource->title ?? '')" name="title" label="{{ __('interface.title') }}" required />

@if (userCanManageAllOrgs())
  <x-customer.organisation.organisation-selector :id="$resource->organisation_id ?? null"
                                                 :title="isset($resource) && $resource->organisation_id ? $resource->organisation->title : null" />

  {{-- <x-ui.input name="organisation_id"
              required
              label="{{ __('customer.organisation.organisation') }}"
              :value="old('title', $resource->organisation_id ?? '')"
              type="select"
              :options="isset($resource) && $resource->organisation_id ? [$resource->organisation_id => $resource->organisation->title] : []"
              class="remote-select"
              :data-load="route('my.settings.organisations.index')"
              data-load-value-field="{{ $loadValueField ?? 'id' }}"
              data-load-label-field="{{ $loadLabelField ?? 'title' }}"
              data-load-search-field="{{ $loadSearchField ?? 'title' }}"
              data-no-results="{{ __('actions.errors.no_results_found_for') }}"
              data-keep-typing="{{ __('actions.errors.keep_typing') }}" /> --}}
@endif
<x-slot name="footer">
  <div></div>
  <div>
    <x-ui.back-button
                      :fallback="isset($resource) && $resource->id ? route('my.settings.teams.show', ['team' => $resource->id]) : route('my.settings.teams.index')" />
    <x-ui.button type="submit" theme="primary">{{ __('actions.save') }}</x-ui.button>
  </div>
</x-slot>
