<x-auth.user.my.settings-layout>
  <div class="w-2/3">
    <x-ui.card>
      <x-ui.form :action="route('my.user.settings.profile.update')" enctype="multipart/form-data" method="put">
        @include('partials.auth.user.profile-form', ['resource' => $user])
      </x-ui.form>
    </x-ui.card>
  </div>
</x-auth.user.my.settings-layout>
