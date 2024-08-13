<x-layouts.guest>
  <div>
    <x-ui.card>

      <div class="sm:mx-auto sm:w-full sm:max-w-md mb-8 mt-2">
        <x-ui.libryo-logo login height="h-32" class="px-4 max-w-sm"></x-ui.libryo-logo>

        <x-slot name="title"></x-slot>
      </div>

      @if (session()->has('saml_failed_error'))
        <div class="mb-8 font-medium text-sm text-negative text-center">
          {{ session()->pull('saml_failed_error') }}
        </div>
      @endif

      {{-- SSO --}}
        <div class="italic text-xs text-center my-4 text-libryo-gray-500">
          {!! __('interface.agree_terms_of_use') !!}
        </div>

        <x-ui.button
          type="link"
          href="{{ route('my.saml.login.start', ['slug' => request()->route('slug')]) }}"
          size="xl"
          theme="primary"
          class="justify-center w-full mt-8"
        >
          {{ __('interface.login') }}
        </x-ui.button>



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
