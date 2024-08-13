<x-layouts.app>
  <x-slot name="header">
    {{ __('auth.user.profile_settings') }}
  </x-slot>

  <div class="grid grid-cols-3 gap-4">
    <div class="w-96">
      <x-auth.user.my.settings-nav />
    </div>
    <div class="col-span-2">
      {{ $slot }}
    </div>
  </div>

</x-layouts.app>
