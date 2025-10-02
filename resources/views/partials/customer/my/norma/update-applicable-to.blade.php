<div>
  <x-customer.norma.norma-data-table
                                       :route="route('my.notify.legal-updates.normas.index', ['update' => $update])"
                                       :base-query="$baseQuery"
                                       searchable
                                       :headings="false"
                                       :fields="['no_link_title']"
                                       :paginate="50" />
</div>
