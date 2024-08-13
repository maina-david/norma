<x-ui.form method="post" :action="$route">

  <div class="text-xl my-10">
    {{ __('compilation.generic_compilation.select_jurisdiction') }}
  </div>

  <x-geonames.location.location-selector />

  <div class="my-10 mb-20">
    <x-ui.input
                type="checkbox"
                name="include_children"
                label="{{ __('compilation.generic_compilation.include_child_locations') }}"
                :value="''" />

    <x-ui.alert-box class="mt-3">
      {{ __('compilation.generic_compilation.include_child_locations_info') }}
    </x-ui.alert-box>

    <div class="text-xl my-10">
      {{ __('compilation.generic_compilation.select_legal_domains') }}
    </div>

    <x-ontology.legal-domain-selector name="legal_domains[]" :label="__('ontology.legal_domain.select_legal_domains')"
                                      multiple />

    <x-slot name="footer">
      <x-ui.back-button :fallback="route('my.settings.libraries.show', ['library' => $library->id])" />
      <x-ui.button type="submit" theme="primary">{{ __('actions.go') }}</x-ui.button>
    </x-slot>
</x-ui.form>
