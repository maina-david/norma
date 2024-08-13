<x-layouts.settings :header="__('settings.nav.welcome_back', ['name' => $user->fname]) . ' 👋'">


  <dl class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
    <div class="relative bg-white pt-5 px-4 pb-12 sm:pt-6 sm:px-6 shadow rounded-lg overflow-hidden">
      <dt>
        <div class="absolute rounded-md p-3">
          <x-ui.icon name="heartbeat" class="text-primary" size="8" />
        </div>
        <p class="ml-16 text-sm font-medium text-libryo-gray-500 truncate">{{ __('auth.user.active_users') }}</p>
      </dt>
      <dd class="ml-16 pb-6 flex items-baseline sm:pb-7">
        <p class="text-2xl font-semibold text-libryo-gray-900">
          {{ $activeUsersCount }}
        </p>
        <div class="absolute bottom-0 inset-x-0 bg-libryo-gray-50 px-4 py-4 sm:px-6">
          <div class="text-sm">
            <a href="{{ route('my.settings.users.index') }}"
               class="font-medium text-primary hover:text-primary-darker">{{ __('interface.view_all') }}</a>
          </div>
        </div>
      </dd>
    </div>

    <div class="relative bg-white pt-5 px-4 pb-12 sm:pt-6 sm:px-6 shadow rounded-lg overflow-hidden">
      <dt>
        <div class="absolute rounded-md p-3">
          <x-ui.icon name="users" class="text-secondary" size="8" />
        </div>
        <p class="ml-16 text-sm font-medium text-libryo-gray-500 truncate">{{ __('customer.team.teams') }}</p>
      </dt>
      <dd class="ml-16 pb-6 flex items-baseline sm:pb-7">
        <p class="text-2xl font-semibold text-libryo-gray-900">
          {{ $teamsCount }}
        </p>
        <div class="absolute bottom-0 inset-x-0 bg-libryo-gray-50 px-4 py-4 sm:px-6">
          <div class="text-sm">
            <a href="{{ route('my.settings.teams.index') }}"
               class="font-medium text-primary hover:text-primary-darker">{{ __('interface.view_all') }}</a>
          </div>
        </div>
      </dd>
    </div>

    <div class="relative bg-white pt-5 px-4 pb-12 sm:pt-6 sm:px-6 shadow rounded-lg overflow-hidden">
      <dt>
        <div class="absolute rounded-md p-3">
          <x-ui.icon name="map-marker" class="text-tertiary" size="8" />
        </div>
        <p class="ml-16 text-sm font-medium text-libryo-gray-500 truncate">{{ __('customer.libryo.libryo_streams') }}</p>
      </dt>
      <dd class="ml-16 pb-6 flex items-baseline sm:pb-7">
        <p class="text-2xl font-semibold text-libryo-gray-900">
          {{ $libryosCount }}
        </p>
        <div class="absolute bottom-0 inset-x-0 bg-libryo-gray-50 px-4 py-4 sm:px-6">
          <div class="text-sm">
            <a href="{{ route('my.settings.libryos.index') }}"
               class="font-medium text-primary hover:text-primary-darker">{{ __('interface.view_all') }}</a>
          </div>
        </div>
      </dd>
    </div>
  </dl>

  {{-- <div class=" mt-10 relative bg-white pt-5 px-4 pb-12 sm:pt-6 sm:px-6 shadow rounded-lg overflow-hidden">
    <dt class="mb-5">
      <div class="absolute">
        <x-ui.icon name="bell" class="text-warning" size="8" />
      </div>
      <p class="ml-16 text-sm font-medium text-libryo-gray-500 truncate">
        {{ __('notify.legal_update.updates_sent') }}</p>
    </dt>
    <dd class="ml-16 pb-6 flex items-baseline sm:pb-7">
      <ol role="list" class="border border-libryo-gray-300 rounded-md divide-y divide-libryo-gray-300 lg:flex lg:divide-y-0 flex-grow">
        @foreach ($updates as $update)
          <li class="relative md:flex-1 md:flex">
            <div class="group w-full">
              <span class="px-6 py-4 flex items-center justify-center flex-grow text-sm font-medium">
                <span
                      class="shrink-0 w-10 h-10 flex items-center justify-center text-white bg-primary rounded-full group-hover:bg-primary">
                  {{ $update['count'] }}
                </span>
                <span class="ml-4 text-sm font-medium text-libryo-gray-900 w-24">
                  {{ $update['month'] }}
                </span>
              </span>
            </div>

            @if (!$loop->last)
              <!-- Arrow separator for lg screens and up -->
              <div class="hidden lg:block absolute top-0 right-0 h-full w-5" aria-hidden="true">
                <svg class="h-full w-full text-libryo-gray-300" viewBox="0 0 22 80" fill="none" preserveAspectRatio="none">
                  <path d="M0 -2L20 40L0 82" vector-effect="non-scaling-stroke" stroke="currentcolor"
                        stroke-linejoin="round" />
                </svg>
              </div>
            @endif
          </li>

        @endforeach
    </dd>
  </div> --}}


</x-layouts.settings>
