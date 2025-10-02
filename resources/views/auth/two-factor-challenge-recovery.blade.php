<x-layouts.guest>
  <div class="mt-8 max-w-sm mx-auto sm:w-full sm:max-w-md">
    <x-ui.card>

      <div class="sm:mx-auto sm:w-full sm:max-w-md mb-8 mt-2">
        <x-ui.norma-logo login height="h-16" class="px-4 max-w-sm"></x-ui.norma-logo>

        <x-slot name="title"></x-slot>
      </div>

      <x-ui.form class="space-y-2" method="post" action="{{ route('two-factor.login') }}">

        <x-ui.input class="mb-2" value="{{ old('recovery_code') }}" name="recovery_code"
                    label="Two Factor Authentication Recovery Code" required autocomplete="off"></x-ui.input>

        <x-ui.button theme="primary" class="justify-center w-full" type="submit">Log in</x-ui.button>
      </x-ui.form>
    </x-ui.card>
  </div>
</x-layouts.guest>
