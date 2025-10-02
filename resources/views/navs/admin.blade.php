<nav class="bg-navbar border-b border-norma-gray-200 relative">
  <div class="mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex h-16" x-data="{ sidebar: false }">
      <div class="-ml-2 mr-2 flex items-center sm:hidden">
        <button @click="sidebar = !sidebar" type="button"
                class="bg-white inline-flex items-center justify-center p-2 rounded-md text-norma-gray-400 hover:text-norma-gray-500 hover:bg-norma-gray-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                aria-controls="mobile-menu" aria-expanded="false">
          <span class="sr-only">Open main menu</span>
          <svg class="block h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
               stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M4 6h16M4 12h16M4 18h16" />
          </svg>
          <svg class="hidden h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
               stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>

      <div class="flex grow">
        <div class="shrink-0 flex items-center">
          <a href="{{ route('admin.dashboard') }}">
            <x-ui.norma-logo></x-ui.norma-logo>
          </a>
        </div>
        <div class="bg-navbar h-full sm:h-auto sm:bg-transparent w-80 sm:w-auto fixed sm:relative pt-2 sm:pt-0 mt-16 sm:mt-0 flex flex-col sm:flex-row sm:-my-px sm:ml-6 sm:space-x-8 sm:left-0 p-2 sm:p-0"
             x-bind:class="sidebar ? 'left-0' : '-left-full'">
          <x-ui.nav-item class="w-full sm:w-auto" route="admin.system-notifications.index">
            {{ __('nav.system_notification') }}
          </x-ui.nav-item>
          <x-ui.nav-item class="w-full sm:w-auto" route="admin.api-logs.index">
            {{ __('nav.api_logs') }}
          </x-ui.nav-item>
        </div>
      </div>

      <div class="flex items-center">
        <button type="button"
                class="bg-white p-1 rounded-full text-norma-gray-400 hover:text-norma-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
          <span class="sr-only">View notifications</span>

          <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
               stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
          </svg>
        </button>

        <x-ui.dropdown>
          <x-slot name="trigger">
            <button type="button"
                    class="max-w-xs bg-white flex items-center text-sm rounded-full focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                    id="user-menu-button" aria-expanded="false" aria-haspopup="true">
              <span class="sr-only">Open user menu</span>
              <x-ui.user-avatar :user="$user"></x-ui.user-avatar>
            </button>
          </x-slot>


          <x-ui.form method="post" action="{{ route('logout') }}">
            <button class="px-4 py-2 text-sm text-norma-gray-700 w-full text-left" role="menuitem" tabindex="-1">
              Sign out
            </button>
          </x-ui.form>

        </x-ui.dropdown>

      </div>
    </div>
  </div>
</nav>
