<x-layouts.guest>
  <div>
    <x-ui.card>

      <div class="sm:mx-auto sm:w-full sm:max-w-md mb-8 mt-2">
        <x-ui.norma-logo login height="h-32" class="px-4 max-w-sm"></x-ui.norma-logo>

        <x-slot name="title"></x-slot>
      </div>

      <div class="mb-8 font-medium text-sm text-negative text-center">
        {{ __('auth.saml.conflict') }}
      </div>

      {{-- SSO --}}
      <div class="italic text-xs text-center my-4 text-norma-gray-500">
        {!! __('interface.agree_terms_of_use') !!}
      </div>

      <div class="max-w-sm w-full mx-auto">
        <x-ui.form method="POST" action="{{ route('my.saml.conflict.store', ['slug' => $slug]) }}">
          <x-ui.button
              type="submit"
              size="xl"
              theme="primary"
              class="justify-center w-full mt-8"
          >
            {{ __('auth.saml.enable_sso') }}
          </x-ui.button>
        </x-ui.form>

        <x-ui.button
            type="link"
            href="{{ route('login') }}"
            size="xl"
            theme="dark"
            class="justify-center w-full mt-8"
        >
          {{ __('auth.saml.use_password') }}
        </x-ui.button>
      </div>



      <div class="text-xs text-center mt-10 text-norma-gray-400">
        <a class="hover:text-norma-gray-700" href="https://norma.com/privacy-policy/" target="_blank">
          {{ __('interface.privacy_policy') }}
        </a>
        |
        <a class="hover:text-norma-gray-700" href="https://norma.com/norma-user-terms/" target="_blank">
          {{ __('interface.user_terms') }}
        </a>
      </div>

    </x-ui.card>
  </div>
</x-layouts.guest>
