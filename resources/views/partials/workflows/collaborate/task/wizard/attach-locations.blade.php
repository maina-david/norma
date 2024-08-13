<div>
  <x-geonames.location.location-selector
    name="locations"
    :required="!($stepMeta['optional'] ?? false)"
  />
</div>
