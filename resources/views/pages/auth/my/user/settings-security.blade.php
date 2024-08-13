<x-auth.user.my.settings-layout>
  <div class="w-2/3">
    <div class="mb-4">
      <div class="font-light text-xl text-libryo-gray-900">{{ __('auth.user.two_factor_authentication') }}</div>
      <div class="text-sm">{{ __('auth.user.two_factor_authentication_explanation') }}</div>
    </div>

    <x-ui.card>
      @include('partials.auth.user.two-factor-form', ['resource' => $user])
    </x-ui.card>

    <section>
      <div class="mt-8">
        <div class="my-4">
          <div class="font-light text-xl text-libryo-gray-900">{{ __('auth.user.browser_sessions') }}</div>
          <div class="text-sm">{{ __('auth.user.browser_session_title') }}</div>
        </div>

        <x-ui.card>
          @include('partials.auth.user.logout-other-browser-sessions-form')
        </x-ui.card>
      </div>
    </section>
  </div>
</x-auth.user.my.settings-layout>
