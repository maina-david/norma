<div class="my-5">
  <x-ui.alert-box type="info" class="mb-2">
    {{ __('customer.team.normas_in_team_info', ['organisation' => $organisation ? $organisation->title : __('customer.organisation.all_organisations')]) }}
  </x-ui.alert-box>
</div>

<x-customer.norma.norma-data-table :base-query="$baseQuery"
                                     :route="route('my.settings.normas.for.team.index', ['team' => $team->id])"
                                     searchable
                                     actionable
                                     :fields="['title', 'active']"
                                     :actions="['remove_from_team']"
                                     :actions-route="route('my.settings.normas.for.team.actions.' . ($organisation ? 'organisation' : 'all'), ['organisation' => $organisation->id ?? 'all', 'team' => $team->id])"
                                     :paginate="50">
  <x-slot name="actionButton">
    <x-general.add-items-to-item-modal items-name="normas"
                                       :tooltip="__('customer.norma.add_normas')"
                                       :actionRoute="route('my.settings.normas.for.team.add', ['team' => $team->id])"
                                       :route="route('my.settings.normas.index')"
                                       :placeholder="__('customer.team.select_normas_to_add', ['team' => $team->title])" />
  </x-slot>
</x-customer.norma.norma-data-table>
