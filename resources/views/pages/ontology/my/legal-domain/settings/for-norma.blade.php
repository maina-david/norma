<x-customer.norma.my.settings.compilation-layout :norma="$norma">
  <x-ontology.legal-domain.my.legal-domain-data-table
                                                      :base-query="$baseQuery"
                                                      :route="route(
                                                          'my.settings.normas.compilation.legal-domains.index',
                                                          [
                                                              'norma' => $norma->id,
                                                          ],
                                                      )"
                                                      :fields="['title']"
                                                      :paginate="50"
                                                      searchable
                                                      actionable
                                                      :actions="['remove_from_norma']"
                                                      :actions-route="route('my.settings.legal-domains.for.norma.actions', [
                                                          'norma' => $norma->id,
                                                      ])">
    <x-slot name="actionButton">
      <x-general.add-items-to-item-modal items-name="legal-domains"
                                         :tooltip="__('ontology.legal_domain.add_legal_domains')"
                                         :actionRoute="route('my.settings.legal-domains.for.norma.add', [
                                             'norma' => $norma->id,
                                         ])"
                                         :route="route('my.settings.legal-domains.index', ['location_id' => $norma->location_id])"
                                         :placeholder="__('ontology.legal_domain.select_legal_domains_to_add', [
                                             'norma' => $norma->title,
                                         ])" />
    </x-slot>
  </x-ontology.legal-domain.my.legal-domain-data-table>
</x-customer.norma.my.settings.compilation-layout>
