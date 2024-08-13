<div class="my-5">
  <x-ui.alert-box type="info" class="mb-2">
    {{ __('auth.user.organisations_user_access_info') }}
  </x-ui.alert-box>
</div>

<x-customer.organisation.organisation-data-table
    :base-query="$baseQuery"
    :route="route('my.settings.organisations.for.user.index', ['user' => $user->id])"
    searchable
    :fields="['title']"
    :paginate="50"
    :actionable="auth()->user()->can('access.org.settings.all')"
    :actions="['remove_from_user']"
    :actions-route="route('my.settings.organisations.for.user.actions', ['user' => $user->id])"
>
  <x-slot name="actionButton">
    @can('access.org.settings.all')
      <x-general.add-items-to-item-modal
          items-name="organisations"
          :actionRoute="route('my.settings.organisations.for.user.add', ['user' => $user->id])"
          :route="route('my.settings.organisations.for.user.search')"
          :placeholder="__('auth.user.select_organisations_to_add', ['user' => $user->fullName])"
          :tooltip="__('auth.user.select_organisations_to_add', ['user' => $user->fullName])"
      />
    @endcan
  </x-slot>
</x-customer.organisation.organisation-data-table>
