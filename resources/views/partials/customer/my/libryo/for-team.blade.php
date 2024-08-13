<div class="my-5">
  <x-ui.alert-box type="info" class="mb-2">
    {{ __('customer.team.libryos_in_team_info', ['organisation' => $organisation ? $organisation->title : __('customer.organisation.all_organisations')]) }}
  </x-ui.alert-box>
</div>

<x-customer.libryo.libryo-data-table :base-query="$baseQuery"
                                     :route="route('my.settings.libryos.for.team.index', ['team' => $team->id])"
                                     searchable
                                     actionable
                                     :fields="['title', 'active']"
                                     :actions="['remove_from_team']"
                                     :actions-route="route('my.settings.libryos.for.team.actions.' . ($organisation ? 'organisation' : 'all'), ['organisation' => $organisation->id ?? 'all', 'team' => $team->id])"
                                     :paginate="50">
  <x-slot name="actionButton">
    <x-general.add-items-to-item-modal items-name="libryos"
                                       :tooltip="__('customer.libryo.add_libryos')"
                                       :actionRoute="route('my.settings.libryos.for.team.add', ['team' => $team->id])"
                                       :route="route('my.settings.libryos.index')"
                                       :placeholder="__('customer.team.select_libryos_to_add', ['team' => $team->title])" />
  </x-slot>
</x-customer.libryo.libryo-data-table>
