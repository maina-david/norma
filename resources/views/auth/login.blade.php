<x-layouts.guest>
  <script src="https://www.google.com/recaptcha/api.js"></script>
  <div x-data="{ showingPasswordLogin: {{ $ssoEnabled ? 'false' : 'true' }} }" class="-mt-40 max-w-sm mx-auto sm:w-full sm:max-w-md">
    <x-ui.card>

      <div class="sm:mx-auto sm:w-full sm:max-w-md mb-8 mt-2">
        <x-ui.libryo-logo login height="h-32" class="px-4 max-w-sm "></x-ui.libryo-logo>

        <x-slot name="title"></x-slot>
      </div>

      @if (session('status'))
        <div class="mb-4 font-medium text-sm text-positive text-center">
          {{ session('status') }}
        </div>
      @endif


      {{-- SSO --}}
      @if ($ssoEnabled)
        <div class="italic text-xs text-center my-4 text-libryo-gray-500">
          {!! __('interface.agree_terms_of_use') !!}
        </div>

        <x-ui.button type="link" href="{{ route('my.oauth.provider.redirect') }}" size="xl" theme="primary"
                     class="justify-center w-full">
          {{ __('interface.login_with', ['name' => $whitelabel->appName()]) }}
        </x-ui.button>

        <div class="my-10 text-3xl text-center uppercase">
          {{ __('interface.or') }}
        </div>
        <div x-show="!showingPasswordLogin" class="text-center mb-5">
          <a @click="showingPasswordLogin = true" class="text-primary cursor-pointer">Sign in with password</a>
        </div>
      @endif
      {{-- Normal login --}}

      <livewire:auth.login-form :bag="$errors->getMessageBag()" />

      {{-- @endif --}}


      <div class="text-xs text-center mt-10 text-libryo-gray-400">
        <a class="hover:text-libryo-gray-700" href="https://libryo.com/privacy-policy/" target="_blank">
          {{ __('interface.privacy_policy') }}
        </a>
        |
        <a class="hover:text-libryo-gray-700" href="https://libryo.com/libryo-user-terms/" target="_blank">
          {{ __('interface.user_terms') }}
        </a>
      </div>

    </x-ui.card>
  </div>
</x-layouts.guest>
