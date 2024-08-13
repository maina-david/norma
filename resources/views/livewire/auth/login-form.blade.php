@php
$init = $hasErrors ? "grecaptcha.render(document.querySelector('.g-recaptcha'))" : '';
@endphp

<div>
  @if($emailValidated || $hasErrors)
    <x-ui.form
      x-show="showingPasswordLogin"
      class="space-y-3"
      method="post"
      action="{{ route('login', ['f' => (int) $hasErrors]) }}"
      x-init="{{ $init }}"
    >
      <x-ui.input
        value="{{ old('email', $email) }}"
        name="email"
        type="email"
        label="Email Address"
        required
      />

      <x-ui.input
        autocomplete="off"
        minlength="6"
        name="password"
        type="password"
        label="Password"
        required
        autofocus
      />

      <div class="flex justify-between items-center py-2">
        <x-ui.input value="{{ old('remember') }}" name="remember" type="checkbox" label="Remember me">
        </x-ui.input>

        <a
            href="{{ route('password.request') }}"
            class="underline text-sm text-libryo-gray-600 hover:text-libryo-gray-900"
        >
          Forgot your password?
        </a>
      </div>

      @if($hasErrors && config('services.google_recaptcha.enabled'))
        <div class="flex flex-col items-center mt-4 mb-8">
          <div class="g-recaptcha" data-sitekey="{{ config('services.google_recaptcha.key') }}"></div>

          @error('g-recaptcha-response')
          <div class="text-sm text-red-400 mt-2">{{ $message }}</div>
          @enderror
        </div>
      @endif


      <div class="italic text-xs text-center mb-4 text-libryo-gray-500">
        {!! __('interface.agree_terms_of_use') !!}
      </div>

      <x-ui.button theme="primary" class="justify-center w-full" type="submit">
        {{ __('interface.login') }}
      </x-ui.button>
    </x-ui.form>

  @else

    <x-ui.form x-show="showingPasswordLogin" wire:submit="save">
      <x-ui.input
          name="email"
          label="Email Address"
          required
          type="email"
          wire:model="email"
          value="{{ old('email', request()->input('email')) }}"
      />

      <div class="italic text-xs text-center my-4 text-libryo-gray-500">
        {!! __('interface.agree_terms_of_use') !!}
      </div>

      <x-ui.button theme="primary" class="justify-center w-full" type="submit">
        {{ __('interface.next') }}
      </x-ui.button>

    </x-ui.form>

  @endif
</div>
