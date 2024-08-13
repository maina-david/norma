<x-layouts.guest>
  <div class="mt-8 max-w-sm mx-auto sm:w-full sm:max-w-md">
    <x-ui.card>

      <div class="sm:mx-auto sm:w-full sm:max-w-md mb-8 mt-2">
        <x-ui.libryo-logo login height="h-16" class="px-4 max-w-sm"></x-ui.libryo-logo>

        <x-slot name="title"></x-slot>
      </div>

      @if (session('status') || $errors->has('email'))
        <div class="mb-4 font-medium text-sm text-green-600 text-center">
          {{ session('status', $errors->get('email')[0] ?? '') }}
        </div>
      @endif

      <x-ui.form class="space-y-2" method="post" action="{{ route('password.email') }}">

        <x-ui.input value="" name="email" label="Email Address" class="mb-2" required no-error />

        <x-ui.button theme="primary" class="justify-center w-full" type="submit">Send Reset Link</x-ui.button>

        <div class="flex justify-end items-center">
          <a href="{{ route('login') }}" class="underline text-sm text-libryo-gray-600 hover:text-libryo-gray-900">Log In?</a>
        </div>

      </x-ui.form>
    </x-ui.card>
  </div>
</x-layouts.guest>
