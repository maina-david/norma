<x-auth.user.my.settings-layout>
  <div class="">
    <x-ui.card>
      <x-ui.form autocomplete="off" :action="route('my.user.settings.notifications.update')" method="put">
        @include('partials.auth.user.notifications-form', ['resource' => $user])
      </x-ui.form>
    </x-ui.card>
  </div>
</x-auth.user.my.settings-layout>
