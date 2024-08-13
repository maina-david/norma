<x-customer.libryo.my.settings.compilation-layout :libryo="$libryo">
  <x-ontology.legal-domain.my.legal-domain-data-table
                                                      :base-query="$baseQuery"
                                                      :route="route(
                                                          'my.settings.libryos.compilation.legal-domains.index',
                                                          [
                                                              'libryo' => $libryo->id,
                                                          ],
                                                      )"
                                                      :fields="['title']"
                                                      :paginate="50"
                                                      searchable
                                                      actionable
                                                      :actions="['remove_from_libryo']"
                                                      :actions-route="route('my.settings.legal-domains.for.libryo.actions', [
                                                          'libryo' => $libryo->id,
                                                      ])">
    <x-slot name="actionButton">
      <x-general.add-items-to-item-modal items-name="legal-domains"
                                         :tooltip="__('ontology.legal_domain.add_legal_domains')"
                                         :actionRoute="route('my.settings.legal-domains.for.libryo.add', [
                                             'libryo' => $libryo->id,
                                         ])"
                                         :route="route('my.settings.legal-domains.index', ['location_id' => $libryo->location_id])"
                                         :placeholder="__('ontology.legal_domain.select_legal_domains_to_add', [
                                             'libryo' => $libryo->title,
                                         ])" />
    </x-slot>
  </x-ontology.legal-domain.my.legal-domain-data-table>
</x-customer.libryo.my.settings.compilation-layout>
