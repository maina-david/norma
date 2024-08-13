<x-customer.libryo.libryo-data-table
  :base-query="$baseQuery"
  :route="route('my.settings.compilation.libryos.for.legal-update.index', ['update' => $update])"
  :paginate="50"
  :fields="['title', 'organisation']"
  searchable
>
</x-customer.libryo.libryo-data-table>
