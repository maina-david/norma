<x-layouts.settings :header="__('settings.nav.teams')">

  <x-slot name="actions">
    <x-ui.button styling="outline"
                 theme="primary"
                 type="link"
                 href="{{ route('my.settings.teams.create') }}">
      {{ __('actions.create') }}</x-ui.button>
  </x-slot>

  <x-customer.team.team-data-table :base-query="$baseQuery"
                                   :route="route('my.settings.teams.index')"
                                   searchable
                                   :paginate="$organisation ? 25 : 50" />
</x-layouts.settings>
