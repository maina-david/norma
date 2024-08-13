<x-layouts.settings :header="__('settings.nav.organisations')">

  <x-slot name="actions">
    <x-ui.button styling="outline"
                 theme="primary"
                 type="link"
                 href="{{ route('my.settings.organisations.create') }}">
      <x-ui.icon name="plus" class="mr-2" />
      {{ __('actions.add') }}
    </x-ui.button>
  </x-slot>

  <x-customer.organisation.organisation-data-table :base-query="$baseQuery"
                                                   :route="route('my.settings.organisations.index')"
                                                   searchable
                                                   filterable
                                                   :filters="['whitelabel_id', 'plan', 'type', 'customer_category']"
                                                   :paginate="50" />
</x-layouts.settings>
