<x-customer.libryo.libryo-data-table :base-query="$baseQuery"
                                     :route="route('my.settings.libryos.for.organisation.index', [
                                         'organisation' => $organisation->id,
                                     ])"
                                     searchable
                                     :fields="$tableFields"
                                     :paginate="50">
</x-customer.libryo.libryo-data-table>
