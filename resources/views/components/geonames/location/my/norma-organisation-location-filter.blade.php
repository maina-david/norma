<div>
  <div class="filter-container py-4" x-data="{ onCheck: function (el, val) { el.closest('.filter-container').querySelectorAll('.filter-children input').forEach(function (ch) { ch.checked = el.checked; }); } }">
    <div class="mb-3">
      <x-ui.input
        type="checkbox"
        label=""
        name=""
        @change="onCheck($event.target)"
        id="all-locations"
      >
        <x-slot:label>
          <span class="font-bold">{{ __('geonames.location.jurisdictions') }}</span>
        </x-slot:label>
      </x-ui.input>
    </div>

    <div class="space-y-2 filter-children">
      @foreach($locations as $location)
        <div class="mb-3">
          <x-ui.input
            type="checkbox"
            :label="$location->title"
            id="location{{ $location->id }}"
            name="locations[]"
            :checkbox-value="$location->id"
            :value="in_array($location->id, $applied)"
          />
        </div>
      @endforeach
    </div>
  </div>

</div>
