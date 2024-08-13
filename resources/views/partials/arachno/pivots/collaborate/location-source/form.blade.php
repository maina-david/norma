<div>
  <x-geonames.location.location-selector multiple show-path name="ids[]" required :label="__('nav.jurisdictions')" />

  <div class="flex justify-end">
    <x-ui.button type="submit" theme="primary" class="mt-10">{{ __('actions.add') }}</x-ui.button>
  </div>

</div>
