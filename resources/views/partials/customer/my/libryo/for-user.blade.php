<div class="my-5">
  <x-ui.alert-box type="info" class="mb-2">
    {{ __('auth.user.libryos_user_access_info', ['organisation' => $organisation ? $organisation->title : __('customer.organisation.all_organisations')]) }}
  </x-ui.alert-box>
</div>

<x-customer.libryo.libryo-data-table :base-query="$baseQuery"
                                     :route="route('my.settings.libryos.for.user.index', ['user' => $user->id])"
                                     searchable
                                     :fields="['title', 'active']"
                                     :paginate="50">
  <x-slot name="actionButton">
    {{-- <x-customer.team.add-teams
                               :actionRoute="route('my.settings.teams.for.user.add', ['user' => $user->id])"
                               :route="route('my.settings.teams.index')"
                               :placeholder="__('auth.user.select_teams_to_add', ['user' => $user->fullName])" /> --}}
  </x-slot>
</x-customer.libryo.libryo-data-table>
