@php
use App\Services\Auth\ImpersonationManager;
@endphp


<div x-data="{ sidebar: false }" class="fixed w-full top-0 left-0 z-30 print:hidden">
  <nav class="bg-navbar border-b border-norma-gray-200 ">
    <div class="px-2 md:px-4">
      <div class="flex h-16">
        <div class="-ml-2 mr-2 flex items-center sm:hidden">
          <button @click="sidebar = !sidebar" type="button"
                  class="bg-white inline-flex items-center justify-center p-2 rounded-md text-norma-gray-400 hover:text-norma-gray-500 hover:bg-norma-gray-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                  aria-controls="mobile-menu" aria-expanded="false">
            <span class="sr-only">Open main menu</span>
            <svg class="block h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                 stroke="currentColor" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
            <svg class="hidden h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                 stroke="currentColor" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <div class="flex grow">
          <div class="shrink-0 hidden md:flex items-center">
            <x-ui.norma-logo height="h-10" style="max-width: 200px;"></x-ui.norma-logo>
          </div>
          <div class="mt-1 ml-3 md:ml-5">
            <x-customer.norma-switcher />
          </div>
          {{-- <div
             class="bg-navbar h-full sm:h-auto sm:bg-transparent w-80 sm:w-auto fixed sm:relative pt-2 sm:pt-0 mt-16 sm:mt-0 flex flex-col sm:flex-row sm:-my-px sm:ml-6 sm:space-x-8 sm:left-0 p-2 sm:p-0"
             x-bind:class="sidebar ? 'left-0' : '-left-full'">
          <x-ui.nav-item class="w-full sm:w-auto" route="my.home">Dashboard</x-ui.nav-item>
        </div> --}}
        </div>

        <div class="flex items-center">
          <x-ui.my.global-search />


          <x-ui.dropdown position="left" class="w-44">
            <x-slot name="trigger">
              <button type="button"
                      class="max-w-xs bg-white flex items-center text-sm rounded-full focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
                      id="user-menu-button" aria-expanded="false" aria-haspopup="true">
                <span class="sr-only">Open user menu</span>
                <x-ui.user-avatar :user="$user"></x-ui.user-avatar>
              </button>
            </x-slot>


            @if (app(ImpersonationManager::class)->isImpersonating())
              <a href="{{ route('my.users.impersonate.leave') }}"
                 class="text-norma-gray-700 block px-4 py-2 text-sm" role="menuitem" tabindex="-1"
                 id="menu-item-0">{{ __('auth.user.leave_impersonation') }}</a>
            @endif


            <a class="block hover:bg-norma-gray-50 px-4 py-2 text-sm text-norma-gray-700 w-full text-left"
               href="{{ route('my.user.settings.profile.show') }}">
              {{ __('auth.user.profile_settings') }}
            </a>


            <x-ui.form method="post" action="{{ route('logout') }}">
              <button class="hover:bg-norma-gray-50 px-4 py-2 text-sm text-norma-gray-700 w-full text-left" role="menuitem"
                      tabindex="-1">
                {{ __('actions.sign_out') }}
              </button>
            </x-ui.form>


          </x-ui.dropdown>

        </div>
      </div>
    </div>
  </nav>

  {{-- Sidebar for anything bigger than mobile --}}
  <aside class="fixed bottom-0 -left-full sm:-left-0 bg-sidebar-background text-sidebar-text overflow-hidden z-30 w-14 hover:w-60 transition-all duration-200 ease-in-out"
         style="top: 65px; z-index: 1000;">
    <x-ui.my.sidebar />
  </aside>
  {{-- Sidebar for mobile --}}
  <aside x-bind:class="sidebar ? 'left-0' : '-left-full'"
         class="fixed bottom-0 sm:hidden bg-sidebar-background text-sidebar-text overflow-hidden z-30 w-56"
         style="top: 65px;">
    <x-ui.my.sidebar />
  </aside>

  @include('partials.ui.my.help')
</div>


<x-ui.alert />
