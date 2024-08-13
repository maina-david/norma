<x-geonames.location.my.location-data-table
    :base-query="$baseQuery"
    :route="route('my.locations.for.file.index', ['file' => $file->id])"
    :fields="['title']"
    :paginate="50"
    searchable
    :actionable="auth()->user()->can('my.storage.file.global.manage')"
    :actions="['remove_from_file']"
    :actions-route="route('my.locations.for.file.actions', ['file' => $file->id])"
>
  <x-slot name="actionButton">
    <x-general.add-items-to-item-modal
        items-name="locations"
        :tooltip="__('ontology.legal_domain.add_legal_domains')"
        :actionRoute="route('my.locations.for.file.add', ['file' => $file->id])"
        :route="route('my.settings.locations.json.index')"
        :placeholder="__('geonames.location.select_jurisdictions_to_add', ['item' => $file->title])"
    />
  </x-slot>
</x-geonames.location.my.location-data-table>
