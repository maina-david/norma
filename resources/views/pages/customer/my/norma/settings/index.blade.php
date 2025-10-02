<x-layouts.settings :header="__('settings.nav.norma_streams')">

  <x-slot name="actions">

    @if (userCanManageAllOrgs())
      <x-ui.button styling="outline"
                   theme="primary"
                   type="link"
                   href="{{ route('my.settings.normas.create') }}">
        {{ __('actions.create') }}</x-ui.button>
    @endif
  </x-slot>

  <x-customer.norma.norma-data-table :base-query="$baseQuery"
                                       :route="route('my.settings.normas.index')"
                                       searchable
                                       actionable
                                       filterable
                                       :fields="$organisation
                                           ? ['title', 'active']
                                           : ['title', 'organisation', 'created_by', 'active']"
                                       :filters="['deactivated', 'citations']"
                                       :actions="['deactivate', 'activate']"
                                       :actions-route="route(
                                           'my.settings.normas.actions.' . ($organisation ? 'organisation' : 'all'),
                                           ['organisation' => $organisation->id ?? 'all'],
                                       )"
                                       :paginate="50" />
</x-layouts.settings>
