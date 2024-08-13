<x-auth.user.user-data-table :base-query="$baseQuery"
                             :route="route('my.notify.legal-updates.users.index', ['update' => $update])"
                             searchable
                             :headings="false"
                             :fields="['legal_update_status']" />
