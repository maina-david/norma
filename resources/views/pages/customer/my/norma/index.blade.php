<x-layouts.app>
  <x-slot name="header">
    <div class="flex items-center">
      <x-ui.icon name="map-marker" class="mr-3 ml-5" size="8" />
      {{ __('customer.norma.norma_streams') }}
    </div>
  </x-slot>


  @if ($norma)
    <div
         x-init="map = new window.NormaMap(document.getElementById('norma-map'), {{ $norma ? json_encode($norma->toArray()) : 'null' }}, {{ $mapZoom }}, {{ $mapCenterLat }}, {{ $mapCenterLng }})">
      <div class="h-96 w-full" id="norma-map"></div>
    </div>
  @endif

  <x-customer.norma.norma-data-table :base-query="$baseQuery"
                                       :route="route('my.customer.normas.index')"
                                       searchable
                                       :search-placeholder="__('customer.norma.search_norma_streams') . '...'"
                                       :fields="['no_link_title', 'activate']"
                                       :paginate="50" />

</x-layouts.app>
