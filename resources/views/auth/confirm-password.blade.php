<x-layouts.guest no-logo>
  <div class="mt-8 max-w-sm mx-auto sm:w-full sm:max-w-md">
    <x-ui.card>

      <div class="sm:mx-auto sm:w-full sm:max-w-md mb-8 mt-2">
        <x-ui.libryo-logo login height="h-16 max-w-sm" class="px-4"></x-ui.libryo-logo>

        <x-slot name="title"></x-slot>
      </div>

      <x-ui.form class="space-y-2" method="post" action="{{ route('password.confirm') }}">

        <div class="font-light text-lg text-libryo-gray-900">{{ __('auth.user.confirm_password') }}</div>
        <p class="text-sm mt-2">{{ __('auth.user.confirm_password_explanation') }}</p>

        <x-ui.input minlength="8" autocomplete="off" name="password" type="password" label="Password" required class="mb-2">
        </x-ui.input>

        <x-ui.button theme="primary" class="justify-center w-full" type="submit">Confirm Password</x-ui.button>

      </x-ui.form>
    </x-ui.card>
  </div>
</x-layouts.guest>
