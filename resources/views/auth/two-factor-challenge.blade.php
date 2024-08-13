@php
$type = request('type', 'code');
$alt = $type === 'code' ? 'recovery_code' : 'code';
$label = $type === 'code' ? 'Authentication' : 'Recovery';
$label = "Two Factor {$label} Code";
@endphp
<x-layouts.guest>
  <div class="mt-8 max-w-sm mx-auto sm:w-full sm:max-w-md">
    <x-ui.card>

      <div class="sm:mx-auto sm:w-full sm:max-w-md mb-8 mt-2">
        <x-ui.libryo-logo login height="h-16" class="px-4 max-w-sm"></x-ui.libryo-logo>

        <x-slot name="title"></x-slot>
      </div>

      <x-ui.form class="space-y-2" method="post" action="{{ route('two-factor.login') }}">

        <x-ui.input value="{{ old($type) }}" :name="$type" :label="$label" required autocomplete="off">
        </x-ui.input>

        <div>
          <p class="text-xs text-libryo-gray-700">{{ __('auth.user.two_factor_authentication_instructions') }}</p>
        </div>

        <div class="flex justify-end items-center py-2">
          <a href="{{ route('two-factor.login', ['type' => $alt]) }}"
             class="underline text-sm text-libryo-gray-600 hover:text-libryo-gray-900">Use
            {{ Str::title(str_replace('_', ' ', $alt)) }}?</a>
        </div>

        <x-ui.button theme="primary" class="justify-center w-full" type="submit">Log in</x-ui.button>

      </x-ui.form>
    </x-ui.card>
  </div>
</x-layouts.guest>
