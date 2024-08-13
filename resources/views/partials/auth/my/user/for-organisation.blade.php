<div class="my-5">
  <x-ui.alert-box type="info" class="mb-2">
    {{ __('customer.organisation.users_in_org_info', ['organisation' => $org->title]) }}
  </x-ui.alert-box>
</div>

<x-auth.user.user-data-table :base-query="$baseQuery"
                             :route="route('my.settings.users.for.organisation.index', ['organisation' => $org->id])"
                             searchable
                             filterable
                             actionable
                             :actions="['remove_from_organisation', 'make_org_admin', 'remove_org_admin', 'resend_welcome_email', 'delete_account']"
                             :actions-route="route('my.settings.users.for.organisation.actions.all', ['organisation' => $org->id])"
                             :filters="['active', 'lifecycle_stage']"
                             :paginate="50"
                             :fields="['avatar', 'name', 'email', 'active']">
  <x-slot name="actionButton">
    <x-general.add-items-to-item-modal items-name="users"
                                       :tooltip="__('auth.user.add_users')"
                                       :actionRoute="route('my.settings.users.for.organisation.add', ['organisation' => $org->id])"
                                       :route="route('my.settings.users.index')"
                                       :placeholder="__('customer.organisation.select_users_to_add', ['organisation' => $org->title])" />
  </x-slot>
</x-auth.user.user-data-table>
