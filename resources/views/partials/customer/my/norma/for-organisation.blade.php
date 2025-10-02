<x-customer.norma.norma-data-table :base-query="$baseQuery"
                                     :route="route('my.settings.normas.for.organisation.index', [
                                         'organisation' => $organisation->id,
                                     ])"
                                     searchable
                                     :fields="$tableFields"
                                     :paginate="50">
</x-customer.norma.norma-data-table>
