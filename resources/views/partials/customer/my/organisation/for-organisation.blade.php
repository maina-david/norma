<x-customer.organisation.organisation-data-table
  searchable
  filterable
  :base-query="$baseQuery"
  :route="route('my.settings.child-organisations.for.organisation.index', ['organisation' => $organisation->id])"
  :filters="['whitelabel_id', 'plan', 'type', 'customer_category']"
  :actionable="auth()->user()->can('access.org.settings.all')"
  :actions="['remove_from_organisation']"
  :actions-route="route('my.settings.child-organisations.for.organisation.actions', ['organisation' => $organisation->id])"
  :paginate="50"
>
  <x-slot name="actionButton">
    @can('access.org.settings.all')
      <x-general.add-items-to-item-modal
          items-name="organisations"
          :actionRoute="route('my.settings.child-organisations.for.organisation.store', ['organisation' => $organisation->id])"
          :route="route('my.settings.organisations.children.search')"
          :placeholder="__('customer.organisation.select_child_organisations_to_add', ['organisation' => $organisation->title])"
          :tooltip="__('customer.organisation.select_child_organisations_to_add', ['organisation' => $organisation->title])"
      />
    @endcan
  </x-slot>
</x-customer.organisation.organisation-data-table>
