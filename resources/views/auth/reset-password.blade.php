<x-layouts.guest>
  <div class="mt-8 max-w-sm mx-auto sm:w-full sm:max-w-md">
    <x-ui.card>

      <div class="sm:mx-auto sm:w-full sm:max-w-md mb-8 mt-2">
        <x-ui.libryo-logo login height="h-16" class="px-4 max-w-sm"></x-ui.libryo-logo>

        <x-slot name="title"></x-slot>
      </div>

      <x-ui.form class="space-y-2" method="post" action="{{ route('password.update') }}">

        <input type="hidden" name="token" value="{{ request()->route('token') }}">

        <x-ui.input value="{{ old('email', request()->get('email')) }}" name="email" label="Email Address" required></x-ui.input>

        <x-ui.input minlength="8" autocomplete="off" name="password" type="password" label="Password" required></x-ui.input>

        <x-ui.input minlength="8" name="password_confirmation" type="password" label="Confirm Password" required
                    class="mb-2"></x-ui.input>

        <x-ui.button theme="primary" class="justify-center w-full" type="submit">Reset Password</x-ui.button>

      </x-ui.form>
    </x-ui.card>
  </div>
</x-layouts.guest>
