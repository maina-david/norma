<x-customer.norma.my.settings.layout :norma="$norma">
  <div class="my-5">
    <x-ui.alert-box type="info" class="mb-2">
      {{ __('customer.norma.teams_norma_part_info', ['organisation' => $organisation ? $organisation->title : __('customer.organisation.all_organisations')]) }}
    </x-ui.alert-box>
  </div>

  <x-customer.team.team-data-table :base-query="$baseQuery"
                                   :route="route('my.settings.teams.for.norma.index', ['norma' => $norma->id])"
                                   :routeParams="['norma' => $norma->id]"
                                   searchable
                                   actionable
                                   :actions="['remove_from_norma']"
                                   :actions-route="route(
                                       'my.settings.teams.for.norma.actions.' .
                                           ($organisation ? 'organisation' : 'all'),
                                       ['organisation' => $organisation->id ?? 'all', 'norma' => $norma->id],
                                   )"
                                   :paginate="50">
    <x-slot name="actionButton">
      <x-general.add-items-to-item-modal items-name="teams"
                                         :tooltip="__('customer.team.add_teams')"
                                         :actionRoute="route('my.settings.teams.for.norma.add', [
                                             'norma' => $norma->id,
                                         ])"
                                         :route="route('my.settings.teams.index')"
                                         :placeholder="__('customer.norma.select_teams_to_add', [
                                             'norma' => $norma->title,
                                         ])" />
    </x-slot>
  </x-customer.team.team-data-table>
</x-customer.norma.my.settings.layout>
