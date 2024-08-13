<div class="my-5">
  <x-ui.alert-box type="info" class="mb-2">
    {{ __('customer.team.users_in_team_info', ['team' => $team->title]) }}
  </x-ui.alert-box>
</div>

<x-auth.user.user-data-table :base-query="$baseQuery"
                             :route="route('my.settings.users.for.team.index', ['team' => $team->id])"
                             searchable
                             :paginate="50"
                             :fields="['avatar', 'name', 'email', 'active']"
                             actionable
                             :actions="['remove_from_team']"
                             :actions-route="route('my.settings.users.for.team.actions.' . ($organisation ? 'organisation' : 'all'), ['organisation' => $organisation->id ?? 'all', 'team' => $team->id])">
  <x-slot name="actionButton">
    <x-general.add-items-to-item-modal items-name="users"
                                       :tooltip="__('auth.user.add_users')"
                                       :actionRoute="route('my.settings.users.for.team.add', ['team' => $team->id])"
                                       :route="route('my.settings.users.index')"
                                       :placeholder="__('customer.team.select_users_to_add', ['team' => $team->title])" />
  </x-slot>
</x-auth.user.user-data-table>
