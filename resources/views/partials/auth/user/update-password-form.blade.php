<x-ui.input minlength="8" data-lpignore="true" autocomplete="off" name="current_password" type="password"
            :label="__('auth.user.current_password')" required>
</x-ui.input>

<x-ui.input minlength="8" data-lpignore="true" autocomplete="off" name="password" type="password"
            :label="__('auth.user.new_password')"
            required></x-ui.input>

<x-ui.input minlength="8" data-lpignore="true" autocomplete="off" name="password_confirmation" type="password"
            :label="__('auth.user.confirm_password')"
            required
            class="mb-2"></x-ui.input>


<x-slot name="footer">
  <div></div>
  <x-ui.button type="submit" theme="primary">{{ __('actions.save') }}</x-ui.button>
</x-slot>
