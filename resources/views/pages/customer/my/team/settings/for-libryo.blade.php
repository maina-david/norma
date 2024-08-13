<x-customer.libryo.my.settings.layout :libryo="$libryo">
  <div class="my-5">
    <x-ui.alert-box type="info" class="mb-2">
      {{ __('customer.libryo.teams_libryo_part_info', ['organisation' => $organisation ? $organisation->title : __('customer.organisation.all_organisations')]) }}
    </x-ui.alert-box>
  </div>

  <x-customer.team.team-data-table :base-query="$baseQuery"
                                   :route="route('my.settings.teams.for.libryo.index', ['libryo' => $libryo->id])"
                                   :routeParams="['libryo' => $libryo->id]"
                                   searchable
                                   actionable
                                   :actions="['remove_from_libryo']"
                                   :actions-route="route(
                                       'my.settings.teams.for.libryo.actions.' .
                                           ($organisation ? 'organisation' : 'all'),
                                       ['organisation' => $organisation->id ?? 'all', 'libryo' => $libryo->id],
                                   )"
                                   :paginate="50">
    <x-slot name="actionButton">
      <x-general.add-items-to-item-modal items-name="teams"
                                         :tooltip="__('customer.team.add_teams')"
                                         :actionRoute="route('my.settings.teams.for.libryo.add', [
                                             'libryo' => $libryo->id,
                                         ])"
                                         :route="route('my.settings.teams.index')"
                                         :placeholder="__('customer.libryo.select_teams_to_add', [
                                             'libryo' => $libryo->title,
                                         ])" />
    </x-slot>
  </x-customer.team.team-data-table>
</x-customer.libryo.my.settings.layout>
