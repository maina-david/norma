<x-layouts.settings-two-col>
  <x-slot name="header">
    <span class="flex items-center">
      <x-ui.icon name="user" size="10" class="mr-5 text-libryo-gray-600" type="duotone" />
      <span>
      {{ $user->full_name }}
      </span>
    </span>
  </x-slot>
  <x-slot name="leftCol">
    <x-ui.card class="text-center flex flex-col">
      <div class="mx-auto inline-block my-5">
        <x-ui.user-avatar :user="$user" />
      </div>
      <div class="my-2 text-lg">{{ $user->full_name }}</div>
      <div class="text-sm ">{{ $user->email }}</div>
      <div class="text-sm">{{ $user->timezone }}</div>
    </x-ui.card>

    @can('my.users.impersonate')
      <x-ui.card class="text-center ">
        <x-ui.button type="link" href="{{ route('my.settings.users.impersonate', ['user' => $user]) }}"
                     theme="tertiary">
          {{ __('auth.user.impersonate') }}</x-ui.button>
      </x-ui.card>
    @endcan

  </x-slot>
  <x-slot name="rightCol">
    <x-ui.card>
      <x-ui.tabs>

        <x-slot name="nav">
          <x-ui.tab-nav name="teams" url="{{ route('my.settings.teams.for.user.index', ['user' => $user->id]) }}">
            {{ __('settings.nav.teams') }}
          </x-ui.tab-nav>
          <x-ui.tab-nav name="libryos" url="{{ route('my.settings.libryos.for.user.index', ['user' => $user->id]) }}">
            {{ __('settings.nav.libryo_streams') }}</x-ui.tab-nav>
          @if (userCanManageAllOrgs())
            <x-ui.tab-nav name="status"
                          url="{{ route('my.settings.users.lifecycle.activities', ['user' => $user->id]) }}">
              {{ __('settings.nav.status') }}</x-ui.tab-nav>
          @endif
        </x-slot>

        <x-ui.tab-content name="teams">
          <turbo-frame id="settings-teams-for-user-{{ $user->id }}">
            <x-ui.skeleton />
          </turbo-frame>
        </x-ui.tab-content>

        <x-ui.tab-content name="libryos">
          <turbo-frame id="settings-libryos-for-user-{{ $user->id }}">
            <x-ui.skeleton />
          </turbo-frame>
        </x-ui.tab-content>

        @if (userCanManageAllOrgs())
          <x-ui.tab-content name="status">
            <turbo-frame id="settings-activities-for-user-{{ $user->id }}">
              <x-ui.skeleton />
            </turbo-frame>
          </x-ui.tab-content>
        @endif

      </x-ui.tabs>
    </x-ui.card>
  </x-slot>
</x-layouts.settings-two-col>
