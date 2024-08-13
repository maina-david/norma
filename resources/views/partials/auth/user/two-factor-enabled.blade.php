<div>
  <div class="font-semibold text-lg text-libryo-gray-900">{{ __('auth.user.two_factor_enabled') }}</div>
  <p class="text-sm mt-2">{{ __('auth.user.two_factor_enabled_explanation') }}</p>
</div>


@if (session('status') == 'two-factor-authentication-enabled')
  <div class="space-y-8 mt-8">
    <div class="mb-4 font-medium text-sm text-green-600">
      {{ __('auth.user.scan_the_qr_code') }}
    </div>

    <div class="flex justify-center">
      {!! $user->twoFactorQrCodeSvg() !!}
    </div>

  </div>
@endif

@if(in_array(session('status'), ['recovery-codes-generated', 'two-factor-authentication-enabled']))
  <div class="my-4">
    <div class="font-semibold text-lg text-libryo-gray-900">{{ __('auth.user.recovery_codes') }}</div>
    <p class="text-sm">{{ __('auth.user.store_recovery_codes') }}</p>
  </div>

  <div class="p-4 border rounded bg-libryo-gray-100 font-mono">
    @foreach((array) $user->recoveryCodes() as $code)
      <div>{{ $code }}</div>
    @endforeach
  </div>
@endif



<div class="flex justify-end space-x-2">

  <x-ui.form :action="route('two-factor.recovery-codes')" method="post">
    <x-slot name="footer">
      <div></div>
      <x-ui.button size="lg" type="submit">{{ __('auth.user.regenerate_recovery_codes') }}</x-ui.button>
    </x-slot>
  </x-ui.form>

  <x-ui.form :action="route('two-factor.disable')" method="delete">
    <x-slot name="footer">
      <div></div>
      <x-ui.button size="lg" type="submit" theme="negative">{{ __('actions.disable') }}</x-ui.button>
    </x-slot>
  </x-ui.form>
</div>
