<x-ui.alert-box>{{ __('auth.user.email_update_info', ['email' => $user->email]) }}</x-ui.alert-box>

<x-ui.input :value="old('password')" autocomplete="off" data-lpignore="true" name="password"
            label="{{ __('auth.user.settings.password') }}" required />
<x-ui.input :value="old('email')" autocomplete="off" data-lpignore="true" name="email"
            label="{{ __('auth.user.new_email') }}" required />


<x-slot name="footer">
  <div class="flex flex-row justify-items-end">
    <x-ui.button type="submit" theme="primary">{{ __('auth.user.reset_email') }}</x-ui.button>
  </div>
</x-slot>
