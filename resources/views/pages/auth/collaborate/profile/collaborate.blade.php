<x-layouts.collaborate>

  <x-slot name="header">
    <h1>{{ __('nav.profile_and_settings') }}</h1>
  </x-slot>


  <x-ui.card>

    <div class="px-4 sm:px-6">

      <x-ui.tabs>

        <x-slot name="nav">
          <x-ui.tab-nav name="general">{{ __('nav.general') }}</x-ui.tab-nav>
          <x-ui.tab-nav name="security">{{ __('nav.security_password') }}</x-ui.tab-nav>
          <x-ui.tab-nav name="notifications">{{ __('nav.notifications') }}</x-ui.tab-nav>
          <x-ui.tab-nav name="billing">{{ __('nav.billing') }}</x-ui.tab-nav>
        </x-slot>

        <x-ui.tab-content name="general">

          @include('pages.auth.collaborate.profile.partials.general')

        </x-ui.tab-content>

      </x-ui.tabs>

    </div>

  </x-ui.card>

</x-layouts.collaborate>
