<div>
  <x-customer.libryo.libryo-data-table
                                       :route="route('my.notify.legal-updates.libryos.index', ['update' => $update])"
                                       :base-query="$baseQuery"
                                       searchable
                                       :headings="false"
                                       :fields="['no_link_title']"
                                       :paginate="50" />
</div>
