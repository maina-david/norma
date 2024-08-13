<x-layouts.app>
  <x-slot name="header">
    <div class="flex items-center">
      <x-ui.icon name="map-marker" class="mr-3 ml-5" size="8" />
      {{ __('customer.libryo.libryo_streams') }}
    </div>
  </x-slot>


  @if ($libryo)
    <div
         x-init="map = new window.LibryoMap(document.getElementById('libryo-map'), {{ $libryo ? json_encode($libryo->toArray()) : 'null' }}, {{ $mapZoom }}, {{ $mapCenterLat }}, {{ $mapCenterLng }})">
      <div class="h-96 w-full" id="libryo-map"></div>
    </div>
  @endif

  <x-customer.libryo.libryo-data-table :base-query="$baseQuery"
                                       :route="route('my.customer.libryos.index')"
                                       searchable
                                       :search-placeholder="__('customer.libryo.search_libryo_streams') . '...'"
                                       :fields="['no_link_title', 'activate']"
                                       :paginate="50" />

</x-layouts.app>
