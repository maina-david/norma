<x-layouts.settings :header="$team->title">
  <x-slot name="header">
    <span class="flex items-center">
      <x-ui.icon name="users" size="10" class="mr-5 text-norma-gray-600" type="duotone" />
      <span>{{ $team->title }}</span>
    </span>
  </x-slot>
  <x-slot name="actions">
    <div class="inline-flex">
      <x-ui.delete-button :route="route('my.settings.teams.destroy', ['team' => $team->id])"
                          button-theme="negative">{{ __('actions.delete') }}</x-ui.delete-button>
    </div>

    <x-ui.button styling="outline"
                 theme="primary"
                 type="link"
                 href="{{ route('my.settings.teams.edit', ['team' => $team->id]) }}">
      {{ __('actions.edit') }}</x-ui.button>
  </x-slot>

  <x-ui.card>
    <x-ui.tabs x-cloak>

      <x-slot name="nav">
        <x-ui.tab-nav name="users" url="{{ route('my.settings.users.for.team.index', ['team' => $team->id]) }}">
          {{ __('settings.nav.users') }}</x-ui.tab-nav>
        <x-ui.tab-nav name="normas" url="{{ route('my.settings.normas.for.team.index', ['team' => $team->id]) }}">
          {{ __('settings.nav.norma_streams') }}</x-ui.tab-nav>
      </x-slot>

      <x-ui.tab-content name="users">

        <turbo-frame id="settings-users-for-team-{{ $team->id }}">
          <x-ui.skeleton />
        </turbo-frame>
      </x-ui.tab-content>
      <x-ui.tab-content name="normas">
        <turbo-frame id="settings-normas-for-team-{{ $team->id }}">
          <x-ui.skeleton />
        </turbo-frame>
      </x-ui.tab-content>

    </x-ui.tabs>
  </x-ui.card>
</x-layouts.settings>
