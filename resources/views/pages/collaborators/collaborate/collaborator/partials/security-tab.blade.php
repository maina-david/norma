<div>
  <div>
    <div class="font-semibold">{{ __('collaborators.change_email') }}</div>
    <p class="text-sm">{{ __('auth.user.confirm_password_explanation') }}</p>
  </div>

  <x-ui.form action="{{ route('collaborate.profile.email.update') }}" method="PUT">
    <div class="grid grid-cols-2 gap-4">
      <div>
        <x-ui.input required :label="__('auth.user.new_email')" :value="old('email')" name="email" />
      </div>

      <div>
        <x-ui.input required :label="__('auth.user.confirm_password')" name="password" type="password" />
      </div>
    </div>

    <div class="flex justify-end mt-4">
      <x-ui.button type="submit">
        {{ __('actions.update') }}
      </x-ui.button>
    </div>
  </x-ui.form>


  <div>
    <div class="font-semibold">{{ __('collaborators.change_password') }}</div>
  </div>


  <x-ui.form action="{{ route('collaborate.profile.password.update') }}" method="PUT">
    <div class="grid grid-cols-2 gap-4">
      <div>
        <x-ui.input required :label="__('auth.user.current_password')" name="current_password" type="password" />
      </div>
      <div>
        <x-ui.input required :label="__('auth.user.new_password')" name="password" type="password" />
      </div>
      <div>
        <x-ui.input required :label="__('auth.user.confirm_password')" name="password_confirmation" type="password" />
      </div>
    </div>

    <div class="flex justify-end mt-4">
      <x-ui.button type="submit">
        {{ __('actions.update') }}
      </x-ui.button>
    </div>
  </x-ui.form>

  @if($user->id === auth()->id())
  <div class="my-12">
      <div class="font-semibold mb-4">{{ __('auth.user.browser_sessions') }}</div>
      <div>
        @include('partials.auth.user.logout-other-browser-sessions-form')
      </div>
  </div>
  @endif
</div>
