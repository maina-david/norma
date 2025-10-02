<x-ontology.legal-domain.my.legal-domain-data-table
    :base-query="$baseQuery"
    :route="route('my.legal-domains.for.file.index', ['file' => $file->id])"
    :fields="['title']"
    :paginate="50"
    searchable
    :actionable="auth()->user()->can('my.storage.file.global.manage')"
    :actions="['remove_from_file']"
    :actions-route="route('my.legal-domains.for.file.actions', ['file' => $file->id])"
>
  <x-slot name="actionButton">
    <x-general.add-items-to-item-modal
      items-name="legal-domains"
      :tooltip="__('ontology.legal_domain.add_legal_domains')"
      :actionRoute="route('my.legal-domains.for.file.add', ['file' => $file->id])"
      :route="route('my.settings.legal-domains.index')"
      :placeholder="__('ontology.legal_domain.select_legal_domains_to_add', ['norma' => $file->title])"
    />
  </x-slot>
</x-ontology.legal-domain.my.legal-domain-data-table>
