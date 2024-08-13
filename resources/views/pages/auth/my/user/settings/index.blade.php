<x-layouts.settings :header="__('settings.nav.users')">

  @if ($organisation)
    <x-slot name="actions">
      <x-ui.button styling="outline"
                   theme="primary"
                   type="link"
                   href="{{ route('my.settings.users.for.organisation.import.create') }}">
        {{ __('actions.import') }}</x-ui.button>

      <x-ui.button styling="outline"
                   theme="primary"
                   type="link"
                   target="_blank"
                   href="{{ route('my.settings.users.for.organisation.export', ['organisation' => $organisation->id]) }}">
        {{ __('actions.export') }}</x-ui.button>

      <x-ui.button styling="outline"
                   theme="primary"
                   type="link"
                   href="{{ route('my.settings.users.create') }}">
        {{ __('auth.user.create_title') }}</x-ui.button>
    </x-slot>

    <x-auth.user.user-data-table :base-query="$baseQuery"
                                 :route="route('my.settings.users.index')"
                                 :actions-route="route('my.settings.users.actions.organisation', [
                                     'organisation' => $organisation->id ?? 'all',
                                 ])"
                                 :fields="['avatar', 'name', 'email', 'active', 'admin']"
                                 searchable
                                 actionable
                                 :actions="['activate', 'deactivate', 'make_org_admin', 'remove_org_admin', 'resend_welcome_email']"
                                 filterable
                                 :paginate="50" />
  @else
    <x-auth.user.user-with-orgs-data-table :base-query="$baseQuery"
                                           :route="route('my.settings.users.index')"
                                           :actions-route="route('my.settings.users.actions.all', [
                                               'organisation' => $organisation->id ?? 'all',
                                           ])"
                                           :fields="['avatar', 'name', 'email', 'active', 'organisations']"
                                           :filters="['active']"
                                           searchable
                                           actionable
                                           :actions="['activate', 'deactivate', 'resend_welcome_email', 'delete_account']"
                                           filterable
                                           :paginate="50" />
  @endif

</x-layouts.settings>
