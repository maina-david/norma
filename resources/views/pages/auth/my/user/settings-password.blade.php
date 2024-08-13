<x-auth.user.my.settings-layout>
  <div class="w-2/3">
    <x-ui.card>
      <x-ui.form autocomplete="off" :action="route('my.user.settings.password.update')" method="put">
        @include('partials.auth.user.update-password-form', ['resource' => $user])
      </x-ui.form>
    </x-ui.card>
  </div>
</x-auth.user.my.settings-layout>
