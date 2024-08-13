@php
$target = \App\Managers\AppManager::getApp();
$target = route("{$target}.auth.logout.devices");
@endphp
<div @closed="location.reload()">
  <x-ui.modal>
    <x-slot name="trigger">
      <div class="flex items-center flex-col md:flex-row">
        <div class="flex-grow pr-8">
          {{ __('auth.user.browser_session_message') }}
        </div>
        <div class="flex-shrink-0">
          <x-ui.button size="lg" theme="negative" @click="open = true">
            {{ __('auth.user.logout_browser_sessions_button_text') }}
          </x-ui.button>
        </div>

      </div>
    </x-slot>

    <div class="max-w-lg space-y-6 px-6 mb-4">
      <x-ui.form method="post" :action="$target">
        <x-ui.input
            minlength="8"
            data-lpignore="true"
            autocomplete="off"
            name="password"
            type="password"
            placeholder="Password"
            :label="__('auth.user.please_enter_your_password_to_logout') "
            required
        />

        <div class="mt-3">
          <x-ui.button size="lg" type="submit" theme="negative">{{ __('auth.user.logout_browser_sessions_button_text') }}</x-ui.button>
        </div>
      </x-ui.form>
    </div>
  </x-ui.modal>
</div>
