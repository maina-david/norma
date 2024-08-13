<x-ontology.legal-domain.collaborate.layout :domain="$domain">
  <div>
    <x-geonames.location.location-selector multiple show-path name="ids[]" required :label="__('geonames.location.select_jurisdictions_to_add', ['item' => $domain->title])" />

    <div class="flex justify-end">
      <x-ui.button type="submit" theme="primary" class="mt-10">{{ __('actions.add') }}</x-ui.button>
    </div>

  </div>
</x-ontology.legal-domain.collaborate.layout>
