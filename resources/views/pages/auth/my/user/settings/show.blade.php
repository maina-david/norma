<x-layouts.settings-two-col>
  <x-slot name="header">
    <span class="flex items-center">
      <x-ui.icon name="user" size="10" class="mr-5 text-libryo-gray-600" type="duotone" />
      <span>
      {{ $user->full_name }}
      </span>
    </span>



    @if(session()->has('generated-reset-token') || session()->has('generated-reset-token-throttled'))
    <div class="bg-white absolute top-0 left-0 z-10 shadow w-full px-4 py-8">
      <div class="relative">
        <button class="absolute -top-4 -right-3 p-2 rounded-full bg-white flex items-center justify-center" onclick="this.parentElement.parentElement.remove()">
          <x-ui.icon name="times-circle" />
        </button>

        <div class="flex flex-col items-center">
          <div class="text-base">{{ __('auth.user.password_reset_link') }}</div>

          @if(session()->has('generated-reset-token'))
            <p class="text-sm font-light">{{ __('auth.user.use_reset_link') }}</p>

            <p class="text-xs text-left mt-4 font-mono px-4 py-2 bg-libryo-gray-200 rounded-lg leading-6" onclick="range = document.createRange();range.selectNodeContents(this);window.getSelection().removeAllRanges();window.getSelection().addRange(range)">
              {{ session('generated-reset-token') }}
            </p>
          @endif

          @if(session()->has('generated-reset-token-throttled'))
            <p class="text-sm font-light">{{ __('passwords.throttled') }}</p>
          @endif
        </div>
      </div>
    </div>
    @endif

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

    @can('my.auth.user.update')
      <x-ui.card class="text-center flex flex-col">
        <x-ui.form :action="route('my.settings.users.update', ['user' => $user->id])" method="put">
          @include('partials.auth.my.user.settings.form', ['resource' => $user])
        </x-ui.form>
      </x-ui.card>
    @endcan

    @canany(['my.users.password.reset', 'my.users.impersonate'])
      <x-ui.card class="text-center ">
        @can('my.users.password.reset')
        <x-ui.button type="link" href="{{ route('my.settings.users.impersonate', ['user' => $user]) }}"
                     theme="tertiary">
          {{ __('auth.user.impersonate') }}</x-ui.button>
        @endcan

        @can('my.users.password.reset')
        @if(!$user->auth_provider)
          <form action="{{ route('my.settings.users.password-reset', ['user' => $user]) }}" method="post">
            {{ csrf_field() }}
            <x-ui.button type="submit" theme="tertiary" class="mt-2">
              {{ __('auth.user.generate_password_reset_link') }}
            </x-ui.button>
          </form>
        @endif
        @endcan
      </x-ui.card>

    @endcanany

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
          <x-ui.tab-nav name="organisations" url="{{ route('my.settings.organisations.for.user.index', ['user' => $user->id]) }}">
            {{ __('settings.nav.organisations') }}</x-ui.tab-nav>
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

        <x-ui.tab-content name="organisations">
          <turbo-frame id="settings-organisations-for-user-{{ $user->id }}">
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
