<x-customer.norma.norma-data-table
  :base-query="$baseQuery"
  :route="route('my.settings.compilation.normas.for.legal-update.index', ['update' => $update])"
  :paginate="50"
  :fields="['title', 'organisation']"
  searchable
>
</x-customer.norma.norma-data-table>
