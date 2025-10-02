
<div>
  <div class="font-light text-lg text-norma-gray-900">{{ __('auth.user.two_factor_disabled') }}</div>
  <p class="text-sm mt-2">{{ __('auth.user.two_factor_disabled_explanation') }}</p>
</div>

<x-ui.form :action="route('two-factor.enable')" method="post">
  <x-slot name="footer">
    <div></div>
    <x-ui.button size="lg" type="submit" theme="primary">{{ __('actions.enable') }}</x-ui.button>
  </x-slot>
</x-ui.form>
