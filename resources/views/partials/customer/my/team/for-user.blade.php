<div class="my-5">
  <x-ui.alert-box type="info" class="mb-2">
    {{ __('auth.user.teams_user_part_info', ['organisation' => $organisation ? $organisation->title : __('customer.organisation.all_organisations')]) }}
  </x-ui.alert-box>
</div>

<x-customer.team.team-data-table :base-query="$baseQuery"
                                 :route="route('my.settings.teams.for.user.index', ['user' => $user->id])"
                                 searchable
                                 actionable
                                 :actions="['remove']"
                                 :paginate="50"
                                 :actions-route="route('my.settings.teams.for.user.actions.' . ($organisation ? 'organisation' : 'all'), ['organisation' => $organisation->id ?? 'all', 'user' => $user->id])">
  <x-slot name="actionButton">

    <x-general.add-items-to-item-modal items-name="teams"
                                       :tooltip="__('customer.team.add_to_teams')"
                                       :actionRoute="route('my.settings.teams.for.user.add', ['user' => $user->id])"
                                       :route="route('my.settings.teams.index')"
                                       :placeholder="__('auth.user.select_teams_to_add', ['user' => $user->fullName])" />
  </x-slot>
</x-customer.team.team-data-table>
