<div class="grid grid-cols-3 gap-1 text-left">
  <div class="">
    <x-ui.icon name="user" class="mr-1 w-5" />{{ $organisation['users_count'] }}
  </div>
  <div class="">
    <x-ui.icon name="map-marker" class="mr-1 w-5" />{{ $organisation['libryos_count'] }}
  </div>
  <div class="">
    <x-ui.icon name="users" class="mr-1 w-5" />{{ $organisation['teams_count'] }}
  </div>
</div>
