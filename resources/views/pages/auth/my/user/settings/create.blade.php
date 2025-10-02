<x-layouts.settings>
  <x-slot name="header">
    <span class="flex items-center">
      <x-ui.icon name="user" size="10" class="mr-5 text-norma-gray-600" type="duotone" />
      <span>
      {{ __('auth.user.create_title') }}
      </span>
    </span>
  </x-slot>

  <x-ui.card class="text-center flex flex-col max-w-3xl w-full mx-auto">
    <x-ui.form :action="route('my.settings.users.store')" method="post">
      @include('partials.auth.my.user.settings.form')
    </x-ui.form>
  </x-ui.card>
</x-layouts.settings>
